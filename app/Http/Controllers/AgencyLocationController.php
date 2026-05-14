<?php

namespace App\Http\Controllers;

use App\Events\AgencyLocationUpdated;
use App\Models\AgencyLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class AgencyLocationController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $user = auth()->user();
        $agency = $user->agency;

        if (!$agency || $agency->status !== 'approved') {
            return response()->json(['error' => 'Not an approved agency.'], 403);
        }

        // Update or create location record in DB
        $loc = AgencyLocation::updateOrCreate(
            ['agency_id' => $agency->id],
            ['lat' => $request->lat, 'lng' => $request->lng]
        );

        // Load agency resources for the payload
        $resources = $agency->resources()->get(['name', 'quantity', 'status'])->toArray();

        // Store last-known location in Redis
        $redisData = json_encode([
            'id'        => $agency->id,
            'lat'       => $request->lat,
            'lng'       => $request->lng,
            'name'      => $agency->name,
            'type'      => $agency->type,
            'resources' => $resources,
        ]);
        
        // Cache in Redis (expire after 10 mins = 600 seconds)
        Redis::setex("agency_location:{$agency->id}", 600, $redisData);

        // Dispatch broadcast event using toOthers()
        broadcast(new AgencyLocationUpdated($loc))->toOthers();

        return response()->json(['success' => true]);
    }
}
