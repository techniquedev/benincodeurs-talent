<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/** Disponibilité affichée sur la carte du membre. */
enum Availability: string
{
    use HasOptions;

    case OpenToMissions = 'missions';
    case OpenToJob = 'job';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::OpenToMissions => 'Ouvert aux missions',
            self::OpenToJob => 'Ouvert à un emploi',
            self::Unavailable => 'Pas disponible',
        };
    }

    /** Couleur Filament (admin). */
    public function color(): string
    {
        return match ($this) {
            self::OpenToMissions => 'success',
            self::OpenToJob => 'info',
            self::Unavailable => 'gray',
        };
    }

    /** Classes Tailwind du badge (site). */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::OpenToMissions => 'bg-lime text-ink',
            self::OpenToJob => 'bg-violet/10 text-violet',
            self::Unavailable => 'bg-gray-100 text-gray-600',
        };
    }
}
