<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $agency_id;
    public $resources;

    /**
     * Create a new event instance.
     */
    public function __construct($agency_id, $resources)
    {
        $this->agency_id = $agency_id;
        $this->resources = $resources;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('agency-resources'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ResourceStatusChanged';
    }
}
