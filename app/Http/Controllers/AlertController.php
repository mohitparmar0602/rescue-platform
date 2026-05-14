<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmergencyAlert;
use App\Models\Agency;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * List all alerts (super_admin sees all; agency_admin sees their own).
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            $alerts = Alert::with('issuer', 'agencies')
                ->latest()
                ->paginate(20);
        } else {
            $agency = $user->agency;
            $alerts = $agency
                ? $agency->alerts()->with('issuer')->latest()->paginate(20)
                : collect();
        }

        return view('alerts.index', compact('alerts'));
    }

    /**
     * Show the "issue new alert" form (super_admin only).
     */
    public function create()
    {
        $agencies = Agency::where('status', 'approved')->orderBy('name')->get();
        return view('alerts.create', compact('agencies'));
    }

    /**
     * Validate, persist, attach agencies and dispatch the queued job.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'severity'    => 'required|in:low,medium,high,critical',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
            'agency_ids'  => 'required|array|min:1',
            'agency_ids.*'=> 'exists:agencies,id',
        ]);

        $alert = Alert::create([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'severity'    => $validated['severity'],
            'lat'         => $validated['lat'] ?? null,
            'lng'         => $validated['lng'] ?? null,
            'issued_by'   => auth()->id(),
        ]);

        // Attach selected agencies to the pivot table
        $alert->agencies()->attach($validated['agency_ids']);

        // Load the full agency models for the job
        $agencies = Agency::whereIn('id', $validated['agency_ids'])->get();

        // Dispatch the queued job — broadcasts WS + queues email + SMS
        SendEmergencyAlert::dispatch($alert, $agencies);

        return redirect()->route('alerts.index')
            ->with('success', "Alert \"{$alert->title}\" dispatched to {$agencies->count()} agency(ies).");
    }

    /**
     * Deactivate an alert (stops it showing as active on maps).
     */
    public function deactivate(Alert $alert)
    {
        $alert->update(['is_active' => false]);

        return back()->with('success', 'Alert deactivated.');
    }
}
