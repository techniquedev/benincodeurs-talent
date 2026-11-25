<?php

namespace App\Filament\Widgets;

use App\Enums\ExperienceLevel;

class MembersByLevelChart extends ProfileBreakdownChart
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Membres par niveau';

    protected function column(): string
    {
        return 'level';
    }

    protected function enum(): string
    {
        return ExperienceLevel::class;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
        ];
    }
}
