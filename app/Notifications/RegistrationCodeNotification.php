<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Code à 6 chiffres envoyé pendant l'inscription pour confirmer l'adresse e-mail. */
class RegistrationCodeNotification extends Notification
{
    public function __construct(#[\SensitiveParameter] public string $code) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ton code de confirmation : '.$this->code)
            ->greeting('Bienvenue sur le Talent Board !')
            ->line('Voici ton code pour confirmer ton adresse e-mail :')
            ->line('**'.$this->code.'**')
            ->line('Ce code est valable '.config('talent.registration.code_ttl_minutes').' minutes.')
            ->line("Si tu n'as pas demandé à t'inscrire, ignore simplement cet e-mail.")
            ->salutation("À très vite,\nL'équipe Bénin Codeurs");
    }
}
