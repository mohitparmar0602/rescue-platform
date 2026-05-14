<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertIssued implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Alert $alert;
    public int   $agencyId;

    public function __construct(Alert $alert, int $agencyId)
    {
        $this->alert    = $alert;
        $this->agencyId = $agencyId;
    }

    /**
     * Broadcast on a per-agency private channel so only that agency receives it.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("agency.{$this->agencyId}.alerts"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AlertIssued';
    }

    /**
     * Slim payload — no need to send the full Eloquent model.
     */
    public function broadcastWith(): array
    {
        return [
            'id'          => $this->alert->id,
            'title'       => $this->alert->title,
            'description' => $this->alert->description,
            'severity'    => $this->alert->severity,
            'lat'         => $this->alert->lat,
            'lng'         => $this->alert->lng,
            'issued_at'   => $this->alert->created_at?->toISOString(),
        ];
    }
}
