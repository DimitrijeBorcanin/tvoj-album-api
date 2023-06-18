<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as NotificationsVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmail extends NotificationsVerifyEmail
{
    // use Queueable;

    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable);
        }
        return (new MailMessage)
            ->subject('Verifikacija email adrese')
            ->line('Radi verifikacije email adrese klikni ne dugme ispod.')
            ->action(
                'Verifikuj email adresu',
                $this->verificationUrl($notifiable)
            )
            ->line('Ako nisi napravio/la nalog, ignoriši ovaj email.');
    }
}
