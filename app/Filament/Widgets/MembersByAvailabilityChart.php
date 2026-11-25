<?php

namespace App\Filament\Widgets;

use App\Enums\Availability;

class MembersByAvailabilityChart extends ProfileBreakdownChart
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Membres par disponibilité';

    protected function column(): string
    {
        return 'availability';
    }

    protected function enum(): string
    {
        return Availability::class;
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
