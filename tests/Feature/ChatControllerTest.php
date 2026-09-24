<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stream_requires_message()
    {
        $response = $this->postJson('/chat/stream', []);
        $response->assertStatus(400)->assertJson(['error' => 'Message is required']);
    }

    private function getStreamContent($response)
    {
        $prevHandler = set_error_handler(function () { return true; }, E_NOTICE | E_WARNING);
        
        try {
            $response->sendContent();
        } finally {
            if ($prevHandler) {
                set_error_handler($prevHandler);
            } else {
                restore_error_handler();
            }
            
            while (ob_get_level() < 1) {
                ob_start();
            }
        }
    }

    public function test_stream_guest_uses_fallback()
    {
        $response = $this->postJson('/chat/stream', ['message' => 'hello']);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
        
        $this->getStreamContent($response);
    }

    public function test_stream_user_with_empty_api_key_uses_fallback()
    {
        Config::set('services.gemini.api_key', '');
        
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/chat/stream', ['message' => 'hello']);
        $response->assertStatus(200);
        
        $this->getStreamContent($response);
    }

    public function test_stream_user_gemini_api_error()
    {
        Config::set('services.gemini.api_key', 'valid_api_key');
        
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->postJson('/chat/stream', ['message' => 'hello']);
        $response->assertStatus(200);
        
        $this->getStreamContent($response);
    }
}
