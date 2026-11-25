<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserStatus;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\MembersCsvExport;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Exporter en CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->tooltip('Exporte les membres de la liste affichée (onglet, recherche et filtres compris).')
                ->action(fn () => MembersCsvExport::download($this->getFilteredSortedTableQuery() ?? User::query())),
        ];
    }

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        $members = fn () => User::query()->where('is_admin', false);

        return [
            'all' => Tab::make('Tous'),
            'pending' => Tab::make('En attente')
                ->badge(fn () => $members()->where('status', UserStatus::Pending)->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Pending)),
            'approved' => Tab::make('Validés')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Approved)),
            'suspended' => Tab::make('Suspendus')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Suspended)),
        ];
    }
}
