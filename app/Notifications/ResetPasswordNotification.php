<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Réinitialise ton mot de passe')
            ->greeting('Bonjour,')
            ->line('Tu reçois cet e-mail car une réinitialisation du mot de passe a été demandée pour ton compte.')
            ->action('Choisir un nouveau mot de passe', $url)
            ->line("Ce lien expire dans {$expire} minutes.")
            ->line("Si tu n'as rien demandé, ignore cet e-mail : ton mot de passe ne change pas.")
            ->salutation("L'équipe Bénin Codeurs");
    }
}
