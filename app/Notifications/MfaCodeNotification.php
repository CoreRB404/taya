<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MfaCodeNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your TAYA verification code')
            ->greeting("Hello {$notifiable->name},")
            ->line('Use this one-time code to finish signing in:')
            ->line($this->code)
            ->line('The code expires in '.config('security.mfa.code_ttl_minutes').' minutes. If you did not sign in, reset your password and contact an administrator.');
    }
}
