<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int    $senderAgencyId;
    public int    $receiverAgencyId;
    public int    $messageId;
    public string $senderName;
    public string $senderAgencyName;
    public string $preview;      // first 60 chars of plaintext for notification
    public string $sentAt;

    public function __construct(Message $message)
    {
        $this->messageId        = $message->id;
        $this->senderAgencyId   = $message->sender->agency_id;
        $this->receiverAgencyId = $message->receiver_agency_id;
        $this->senderName       = $message->sender->name;
        $this->senderAgencyName = $message->sender->agency->name ?? 'Unknown';
        $this->preview          = mb_substr($message->body, 0, 60); // body already decrypted by accessor
        $this->sentAt           = $message->created_at->toISOString();
    }

    /**
     * Broadcast on the receiving agency's private-like channel.
     * Using a public channel (agency.{id}.chat) — message content is
     * NOT in the payload (only a preview + IDs), so the full encrypted
     * body is only ever fetched via authenticated HTTP.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("agency.{$this->receiverAgencyId}.chat"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewMessageReceived';
    }
}
