<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('first_name')->label('Prénom')->searchable()->sortable(),
                TextColumn::make('last_name')->label('Nom')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                TextColumn::make('whatsapp')->label('WhatsApp')->searchable()->toggleable(),
                TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn (UserStatus $state) => $state->label())
                    ->color(fn (UserStatus $state) => $state->color())
                    ->sortable(),
                IconColumn::make('email_verified')->label('E-mail vérifié')->boolean()
                    ->state(fn (User $record) => $record->email_verified_at !== null),
                IconColumn::make('is_admin')->label('Admin')->boolean()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Inscrit le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Statut')->options(UserStatus::options()),
                TernaryFilter::make('email_verified_at')->label('E-mail vérifié')->nullable(),
                TernaryFilter::make('is_admin')->label('Administrateur'),
            ])
            ->recordActions([
                self::approveAction(),
                self::suspendAction(),
                EditAction::make(),
            ]);
    }

    public static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Valider')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Valider ce compte ?')
            ->modalDescription("Le membre recevra un e-mail et aura accès à l'annuaire.")
            ->visible(fn (User $record) => ! $record->isApproved() && ! $record->is_admin)
            ->action(function (User $record) {
                $record->approve();
                Notification::make()->title('Compte validé, e-mail envoyé.')->success()->send();
            });
    }

    public static function suspendAction(): Action
    {
        return Action::make('suspend')
            ->label('Suspendre')
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Suspendre ce compte ?')
            ->modalDescription('Le membre sera déconnecté et ne pourra plus accéder au Talent Board.')
            ->visible(fn (User $record) => ! $record->isSuspended() && ! $record->is_admin)
            ->action(function (User $record) {
                $record->suspend();
                Notification::make()->title('Compte suspendu.')->warning()->send();
            });
    }
}
