<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/** Niveau d'un membre sur une compétence. */
enum SkillLevel: string
{
    use HasOptions;

    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
    case Expert = 'expert';

    public function label(): string
    {
        return match ($this) {
            self::Beginner => 'Débutant',
            self::Intermediate => 'Intermédiaire',
            self::Advanced => 'Avancé',
            self::Expert => 'Expert',
        };
    }

    /** Nombre de points (sur 4) pour l'indicateur visuel. */
    public function rank(): int
    {
        return match ($this) {
            self::Beginner => 1,
            self::Intermediate => 2,
            self::Advanced => 3,
            self::Expert => 4,
        };
    }
}
