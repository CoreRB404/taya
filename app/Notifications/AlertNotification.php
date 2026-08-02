<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Alert $alert) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $level = strtoupper(str_replace('_', ' ', $this->alert->alert_level));

        return (new MailMessage)
            ->subject("TAYA alert #{$this->alert->id}: {$level}")
            ->greeting("Alert notification: {$level}")
            ->line("Alert #{$this->alert->id} requires review in TAYA.")
            ->line('Sensitive case details are intentionally omitted from email.')
            ->action('View alert details', url("/alerts/{$this->alert->id}"))
            ->line('Sign in to review and take action.');
    }
}
