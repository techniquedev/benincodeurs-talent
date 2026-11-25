<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/** Niveau global d'un membre dans son métier. */
enum ExperienceLevel: string
{
    use HasOptions;

    case Explorer = 'explorer';
    case Practitioner = 'practitioner';
    case Confirmed = 'confirmed';
    case Senior = 'senior';

    public function label(): string
    {
        return match ($this) {
            self::Explorer => 'Explorateur',
            self::Practitioner => 'Praticien',
            self::Confirmed => 'Confirmé',
            self::Senior => 'Senior',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Explorer => "J'apprends les bases et je réalise mes premiers projets, souvent accompagné.",
            self::Practitioner => 'Je mène des projets simples seul et je progresse en continu.',
            self::Confirmed => 'Je livre des projets complets en autonomie et je sais conseiller les autres.',
            self::Senior => "Je pilote des projets complexes, j'encadre et je prends les décisions techniques.",
        };
    }
}
