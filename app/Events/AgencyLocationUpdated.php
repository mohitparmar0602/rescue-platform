<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\AgencyLocation;

class AgencyLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $agency_id;
    public $lat;
    public $lng;
    public $name;
    public $type;
    public $resources;

    /**
     * Create a new event instance.
     */
    public function __construct(AgencyLocation $location)
    {
        // Load the relationship to grab agency details
        $location->loadMissing('agency.documents'); // or resources

        $agency = $location->agency;

        $this->agency_id = $agency->id;
        $this->lat = $location->lat;
        $this->lng = $location->lng;
        $this->name = $agency->name;
        $this->type = $agency->type;
        $this->resources = []; // This can be populated when resources table is used
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('agency-locations'),
        ];
    }
}
