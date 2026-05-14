<?php

namespace App\Jobs;

use App\Mail\AgencyApprovedMail;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAgencyWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $agency;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Agency $agency)
    {
        $this->user = $user;
        $this->agency = $agency;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(new AgencyApprovedMail($this->user, $this->agency));
    }
}
