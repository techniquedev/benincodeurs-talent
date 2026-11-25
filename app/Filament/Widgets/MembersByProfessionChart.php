<?php

namespace App\Filament\Widgets;

use App\Enums\Profession;

class MembersByProfessionChart extends ProfileBreakdownChart
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Membres par métier';

    protected function column(): string
    {
        return 'profession';
    }

    protected function enum(): string
    {
        return Profession::class;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
