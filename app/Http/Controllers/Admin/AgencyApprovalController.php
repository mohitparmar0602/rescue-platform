<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use App\Jobs\SendAgencyWelcomeEmail;

class AgencyApprovalController extends Controller
{
    public function index()
    {
        $pendingAgencies = Agency::with('documents')->where('status', 'pending')->latest()->get();
        return view('admin.agencies.index', compact('pendingAgencies'));
    }

    public function approve(Request $request, Agency $agency)
    {
        if ($agency->status !== 'pending') {
            return back()->with('error', 'Agency is not pending approval.');
        }

        $agency->update(['status' => 'approved']);

        // Find the user associated with this agency (the agency_admin who registered it)
        // Since the User is linked via agency_id and created at registration:
        $user = $agency->users()->first();

        if ($user) {
            SendAgencyWelcomeEmail::dispatch($user, $agency);
        }

        return back()->with('status', 'Agency approved successfully. Welcome email dispatched.');
    }
}
