<?php

namespace App\Filament\Widgets;

use App\Enums\UserStatus;
use App\Filament\Resources\Users\UserResource;
use App\Models\Profile;
use App\Models\User;
use App\Support\Permissions;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MembersOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Permissions::allows('view');
    }

    protected function getStats(): array
    {
        $counts = User::query()->where('is_admin', false)
            ->selectRaw('status, count(*) as total')->groupBy('status')
            ->pluck('total', 'status');

        $listed = Profile::query()->listed()->count();
        $featured = Profile::query()->listed()->where('is_featured', true)->count();
        $approved = (int) ($counts[UserStatus::Approved->value] ?? 0);
        $newThisWeek = User::query()->where('is_admin', false)->where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('Nouveaux inscrits', $newThisWeek)
                ->description('Ces 7 derniers jours')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info')
                ->url(UserResource::getUrl('index')),
            Stat::make('Membres validés', $approved)
                ->description(((int) ($counts[UserStatus::Suspended->value] ?? 0)).' suspendu(s)')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(UserResource::getUrl('index', ['tab' => 'approved'])),
            Stat::make("Dans l'annuaire", $listed)
                ->description($approved > 0 ? round($listed / $approved * 100).' % des membres validés' : 'Profil complet à '.Profile::minCompletion().' % minimum')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Profils à la une', $featured)
                ->description('Affichés en premier')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
