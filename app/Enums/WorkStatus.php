<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/** Situation professionnelle actuelle. */
enum WorkStatus: string
{
    use HasOptions;

    case Employee = 'employee';
    case Freelance = 'freelance';
    case Entrepreneur = 'entrepreneur';
    case Student = 'student';
    case JobSeeker = 'job_seeker';

    public function label(): string
    {
        return match ($this) {
            self::Employee => 'Salarié',
            self::Freelance => 'Freelance',
            self::Entrepreneur => 'Entrepreneur',
            self::Student => 'Étudiant',
            self::JobSeeker => 'En recherche',
        };
    }
}
