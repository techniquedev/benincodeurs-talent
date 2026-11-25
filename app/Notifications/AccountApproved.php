<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Envoyée quand l'équipe valide un compte. Envoi immédiat (pas de file d'attente) :
 * le service Render n'a pas de worker dédié.
 */
class AccountApproved extends Notification
{
    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ton compte Talent Board est validé')
            ->greeting('Bonne nouvelle, '.$notifiable->first_name.' !')
            ->line("L'équipe Bénin Codeurs a validé ton compte. Tu as maintenant accès à l'annuaire des membres.")
            ->line("Pense à compléter ton profil : il apparaît dans l'annuaire dès qu'il est rempli à 70 % ou plus.")
            ->action('Accéder au Talent Board', route('dashboard'))
            ->salutation("À très vite,\nL'équipe Bénin Codeurs");
    }
}
