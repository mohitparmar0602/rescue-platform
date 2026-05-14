<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\AgencyDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class AgencyRegistrationController extends Controller
{
    public function create()
    {
        return view('agencies.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'agency_name' => ['required', 'string', 'max:255'],
            'agency_type' => ['required', 'string', 'max:255'],
            'registration_no' => ['required', 'string', 'max:255', 'unique:'.Agency::class],
            'address' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request) {
            $agency = Agency::create([
                'name' => $request->agency_name,
                'type' => $request->agency_type,
                'registration_no' => $request->registration_no,
                'address' => $request->address,
                'status' => 'pending',
            ]);


            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'agency_id' => $agency->id,
            ]);

            // Assign role using Spatie
            $user->assignRole('agency_admin');

            Auth::login($user);
        });

        return redirect(route('dashboard', absolute: false))->with('status', 'Agency registered successfully and is pending approval.');
    }
}
