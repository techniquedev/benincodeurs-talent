<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/** Métier principal d'un membre. */
enum Profession: string
{
    use HasOptions;

    case Developer = 'developer';
    case Designer = 'designer';
    case DataAi = 'data_ai';
    case ProjectProduct = 'project_product';
    case Marketing = 'marketing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Developer => 'Développeur',
            self::Designer => 'Designer',
            self::DataAi => 'Data ou IA',
            self::ProjectProduct => 'Chef de projet ou product',
            self::Marketing => 'Marketing digital ou contenu',
            self::Other => 'Autre',
        };
    }
}
