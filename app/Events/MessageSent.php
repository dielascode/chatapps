<?php

namespace App\Events;

use App\Models\Chatting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatting;

    public function __construct(Chatting $chatting)
    {
        $this->chatting = $chatting;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->chatting->id,
            'sender' => $this->chatting->sender,
            'message' => $this->chatting->message,
            'created_at' => $this->chatting->created_at->format('H:i'),
        ];
    }
}
