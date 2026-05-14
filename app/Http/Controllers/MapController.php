<?php

namespace App\Http\Controllers;

use App\Models\AgencyLocation;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class MapController extends Controller
{
    public function index()
    {
        $agencies = [];
        $seenIds  = [];

        // ── 1. Pull live positions from Redis ──────────────────────────────
        try {
            $prefix = config('database.redis.options.prefix', '');
            $keys   = Redis::keys('*agency_location:*');

            foreach ($keys as $fullKey) {
                $keyWithoutPrefix = $prefix ? str_replace($prefix, '', $fullKey) : $fullKey;

                preg_match('/agency_location:\d+/', $keyWithoutPrefix, $matches);
                if (isset($matches[0])) {
                    $data = Redis::get($matches[0]);
                    if ($data) {
                        $decoded = json_decode($data, true);
                        if ($decoded) {
                            $agencies[]              = $decoded;
                            $seenIds[$decoded['id']] = true;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Redis unavailable, fallback to DB
            \Illuminate\Support\Facades\Log::warning('Redis unavailable in MapController: ' . $e->getMessage());
        }

        // ── 2. DB fallback for approved agencies with a stored location ─────
        //     (covers anyone not yet seen in Redis, e.g. server restarted)
        $dbLocations = AgencyLocation::with(['agency.resources'])
            ->whereHas('agency', fn ($q) => $q->where('status', 'approved'))
            ->get();

        foreach ($dbLocations as $loc) {
            if (isset($seenIds[$loc->agency_id]) || !$loc->agency) {
                continue; // Redis already has a fresher entry
            }

            $agencies[] = [
                'id'        => $loc->agency->id,
                'lat'       => $loc->lat,
                'lng'       => $loc->lng,
                'name'      => $loc->agency->name,
                'type'      => $loc->agency->type,
                'resources' => $loc->agency->resources->map(fn ($r) => [
                    'name'     => $r->name,
                    'quantity' => $r->quantity,
                    'status'   => $r->status,
                ])->values()->toArray(),
            ];
        }

        // ── 3. Active alerts that have a location (for map pins) ──────────
        $activeAlerts = Alert::where('is_active', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get(['id', 'title', 'description', 'severity', 'lat', 'lng']);

        return view('map.index', compact('agencies', 'activeAlerts'));
    }
}

