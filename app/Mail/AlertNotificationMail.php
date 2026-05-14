<?php

namespace App\Mail;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Alert $alert;
    public User  $recipient;

    public function __construct(Alert $alert, User $recipient)
    {
        $this->alert     = $alert;
        $this->recipient = $recipient;
    }

    public function envelope(): Envelope
    {
        $prefix = match ($this->alert->severity) {
            'critical' => '🚨 CRITICAL',
            'high'     => '🔴 HIGH',
            'medium'   => '🟡 MEDIUM',
            default    => '🟢 LOW',
        };

        return new Envelope(
            subject: "{$prefix} ALERT: {$this->alert->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerts.notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
