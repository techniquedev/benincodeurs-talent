<?php

namespace App\Filament\Widgets;

use App\Enums\UserStatus;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Comptes en attente, à valider directement depuis le tableau de bord. */
class PendingMembers extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Comptes en attente de validation')
            ->query(User::query()->with('profile')->where('is_admin', false)->where('status', UserStatus::Pending))
            ->defaultSort('created_at')
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Aucun compte en attente')
            ->emptyStateDescription('Tout est à jour.')
            ->columns([
                TextColumn::make('name')->label('Membre')->state(fn (User $record) => $record->name)
                    ->description(fn (User $record) => $record->email),
                TextColumn::make('whatsapp')->label('WhatsApp'),
                TextColumn::make('email_verified_at')->label('E-mail vérifié')
                    ->state(fn (User $record) => $record->email_verified_at ? 'Oui' : 'Non')
                    ->badge()->color(fn (string $state) => $state === 'Oui' ? 'success' : 'gray'),
                TextColumn::make('profile.completion')->label('Profil')
                    ->state(fn (User $record) => $record->profile?->completion ?? 0)->suffix(' %'),
                TextColumn::make('created_at')->label('Inscrit')->since(),
            ])
            ->recordActions([
                UsersTable::approveAction(),
                Action::make('open')->label('Fiche')->icon('heroicon-o-eye')->color('gray')
                    ->url(fn (User $record) => UserResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
