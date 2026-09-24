<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function stream(Request $request, ChatbotService $chatbot)
    {
        $message = $request->input('message', '');
        $history = $request->input('history', []);

        if (empty(trim($message))) {
            return response()->json(['error' => 'Message is required'], 400);
        }

        // Guest: chỉ FAQ/fallback — không gọi Gemini, không lộ dữ liệu đơn hàng
        if (! Auth::check()) {
            return $this->sendSseResponse(function () use ($message, $chatbot) {
                $text = $chatbot->fallbackResponse($message, null);
                $this->sendSseEvent(['text' => $text, 'done' => true]);
            });
        }

        $userId = Auth::id();
        $history = array_slice(is_array($history) ? $history : [], -20);
        $contextParts = $chatbot->buildContext($message, $userId);

        $apiKey = $chatbot->getApiKey();

        if (empty($apiKey) || $apiKey === 'test') {
            return $this->sendSseResponse(function () use ($message, $chatbot, $userId) {
                $text = $chatbot->sendMessage($message, [], $userId);
                $this->sendSseEvent(['text' => $text, 'done' => true]);
            });
        }

        try {
            $model = $chatbot->getModel();
            $systemPrompt = $chatbot->getSystemPrompt();

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:streamGenerateContent?alt=sse";

            $contents = [];
            foreach ($history as $msg) {
                $contents[] = [
                    'role' => $msg['type'] === 'user' ? 'user' : 'model',
                    'parts' => [['text' => $msg['content']]],
                ];
            }

            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt.$contextParts]],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 256,
                    'thinkingConfig' => ['thinkingBudget' => 0],
                ],
            ];

            $client = new Client;
            $response = $client->post($url, [
                'json' => $payload,
                'headers' => ['x-goog-api-key' => $apiKey],
                'timeout' => 20,
                'stream' => true,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Gemini API returned status: '.$response->getStatusCode());
            }

            return $this->sendSseResponse(function () use ($response) {
                $body = $response->getBody();
                $buffer = '';

                while (! $body->eof()) {
                    $chunk = $body->read(4096);
                    if ($chunk === '' || $chunk === false) {
                        break;
                    }
                    $buffer .= $chunk;

                    while (($pos = strpos($buffer, "\n")) !== false) {
                        $line = substr($buffer, 0, $pos);
                        $buffer = substr($buffer, $pos + 1);

                        $line = trim($line);

                        if (empty($line)) {
                            continue;
                        }

                        if (str_starts_with($line, 'data: ')) {
                            $jsonStr = substr($line, 6);

                            if ($jsonStr === '[DONE]') {
                                $this->sendSseEvent(['done' => true]);

                                return;
                            }

                            $data = json_decode($jsonStr, true);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                continue;
                            }

                            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                            if ($text !== null) {
                                $this->sendSseEvent(['text' => $text]);
                            }
                        }
                    }
                }

                $this->sendSseEvent(['done' => true]);
                $response->getBody()->close();
            });

        } catch (\Exception $e) {
            Log::error('Chat streaming error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->sendSseResponse(function () use ($message, $chatbot, $userId) {
                $text = $chatbot->sendMessage($message, [], $userId);
                $this->sendSseEvent(['text' => $text, 'done' => true]);
            });
        }
    }

    private function sendSseResponse(callable $callback): StreamedResponse
    {
        return response()->stream(function () use ($callback) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
            ob_implicit_flush(true);

            $callback();

            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Pragma' => 'no-cache',
        ]);
    }

    private function sendSseEvent(array $data): void
    {
        echo 'data: '.json_encode($data)."\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
