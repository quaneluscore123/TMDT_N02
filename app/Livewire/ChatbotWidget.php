<?php

namespace App\Livewire;

use Livewire\Component;

class ChatbotWidget extends Component
{
    public $isOpen = false;

    public $message = '';

    public $messages = [];

    public $isLoading = false;

    protected $listeners = [
        'receiveBotResponse',
    ];

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function sendQuick(string $text): void
    {
        $this->message = $text;
        $this->sendMessage();
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) {
            return;
        }

        $userMessage = $this->message;
        $this->message = '';

        $this->messages[] = [
            'type' => 'user',
            'content' => $userMessage,
        ];

        $this->dispatch('startStreaming', message: $userMessage, history: $this->messages);
        $this->dispatch('scrollToBottom');
    }

    public function receiveBotResponse($response)
    {
        $content = trim((string) $response);
        if ($content !== '') {
            $this->messages[] = [
                'type' => 'bot',
                'content' => $content,
            ];
        }
        $this->isLoading = false;
        $this->dispatch('scrollToBottom');
    }

    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}
