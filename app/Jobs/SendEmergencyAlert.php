<?php

namespace App\Jobs;

use App\Events\AlertIssued;
use App\Mail\AlertNotificationMail;
use App\Models\Agency;
use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;

class SendEmergencyAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Max retries before the job is considered failed */
    public int $tries = 3;

    public Alert $alert;

    /** @var \Illuminate\Database\Eloquent\Collection<Agency> */
    public $agencies;

    public function __construct(Alert $alert, $agencies)
    {
        $this->alert    = $alert;
        $this->agencies = $agencies;
    }

    public function handle(): void
    {
        foreach ($this->agencies as $agency) {
            // ── 1. Real-time WebSocket push ────────────────────────────────
            broadcast(new AlertIssued($this->alert, $agency->id));

            // ── 2. Email to every admin of the agency ─────────────────────
            $admins = $agency->users()->role('agency_admin')->get();

            foreach ($admins as $admin) {
                // Email (queued)
                Mail::to($admin->email)->queue(new AlertNotificationMail($this->alert, $admin));

                // ── 3. SMS via Twilio (skip if no phone or no credentials) ─
                if (!empty($admin->phone) && config('services.twilio.sid')) {
                    try {
                        $twilio = new TwilioClient(
                            config('services.twilio.sid'),
                            config('services.twilio.token')
                        );

                        $twilio->messages->create($admin->phone, [
                            'from' => config('services.twilio.from'),
                            'body' => "🚨 ALERT [{$this->alert->severity}]: {$this->alert->title}\n{$this->alert->description}",
                        ]);
                    } catch (\Exception $e) {
                        // Log but don't fail the whole job for one SMS
                        Log::error("Twilio SMS failed for admin {$admin->id}: " . $e->getMessage());
                    }
                }
            }
        }
    }
}
