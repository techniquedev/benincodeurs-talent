<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirme ton adresse e-mail')
            ->greeting('Bienvenue sur le Talent Board !')
            ->line('Clique sur le bouton ci-dessous pour confirmer ton adresse e-mail.')
            ->action('Confirmer mon e-mail', $url)
            ->line('Ce lien est valable '.config('auth.verification.expire', 60).' minutes.')
            ->line("Si tu n'as pas créé de compte, ignore simplement cet e-mail.")
            ->salutation("À très vite,\nL'équipe Bénin Codeurs");
    }
}
