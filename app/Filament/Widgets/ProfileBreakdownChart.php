<?php

namespace App\Filament\Widgets;

use App\Enums\UserStatus;
use App\Models\Profile;
use App\Support\Permissions;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Répartition des membres validés selon une colonne du profil (métier, niveau, disponibilité).
 * Les classes filles indiquent la colonne et l'énumération à utiliser.
 */
abstract class ProfileBreakdownChart extends ChartWidget
{
    protected const COLORS = ['#280050', '#6d2399', '#D4F01F', '#a76bd1', '#1a0035', '#c2dd10'];

    protected ?string $maxHeight = '260px';

    protected ?string $description = 'Membres validés ayant renseigné ce champ.';

    public static function canView(): bool
    {
        return Permissions::allows('view');
    }

    abstract protected function column(): string;

    /** @return class-string<\BackedEnum> */
    abstract protected function enum(): string;

    protected function getData(): array
    {
        $column = $this->column();

        $counts = Profile::query()
            ->whereHas('user', fn (Builder $q) => $q->where('status', UserStatus::Approved)->where('is_admin', false))
            ->whereNotNull($column)
            ->selectRaw("$column as value, count(*) as total")
            ->groupBy($column)
            ->pluck('total', 'value');

        $cases = $this->enum()::cases();

        return [
            'datasets' => [[
                'label' => 'Membres',
                'data' => array_map(fn ($case) => (int) ($counts[$case->value] ?? 0), $cases),
                'backgroundColor' => array_slice(self::COLORS, 0, count($cases)),
                'borderColor' => $this->getType() === 'bar' ? 'transparent' : '#ffffff',
                'borderRadius' => $this->getType() === 'bar' ? 8 : 0,
            ]],
            'labels' => array_map(fn ($case) => $case->label(), $cases),
        ];
    }
}
