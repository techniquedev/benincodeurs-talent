<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Availability;
use App\Enums\ExperienceLevel;
use App\Enums\Profession;
use App\Enums\UserStatus;
use App\Models\Profile;
use App\Models\User;
use App\Support\Media;
use App\Support\MembersCsvExport;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('profile'))
            ->columns([
                ImageColumn::make('photo')->label('')
                    ->state(fn (User $record) => Media::url($record->profile?->photo_path, 80, 80))
                    ->circular()
                    ->defaultImageUrl(fn (User $record) => self::initialsAvatar($record)),
                TextColumn::make('name')->label('Membre')
                    ->state(fn (User $record) => $record->name)
                    ->description(fn (User $record) => $record->profile?->headline)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name']),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable()->toggleable(),
                TextColumn::make('whatsapp')->label('WhatsApp')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn (UserStatus $state) => $state->label())
                    ->color(fn (UserStatus $state) => $state->color())
                    ->sortable(),
                TextColumn::make('profile.profession')->label('Métier')
                    ->formatStateUsing(fn (?Profession $state) => $state?->label())
                    ->placeholder('—')->toggleable(),
                TextColumn::make('profile.level')->label('Niveau')
                    ->formatStateUsing(fn (?ExperienceLevel $state) => $state?->label())
                    ->placeholder('—')->toggleable(),
                TextColumn::make('profile.availability')->label('Disponibilité')->badge()
                    ->formatStateUsing(fn (?Availability $state) => $state?->label())
                    ->color(fn (?Availability $state) => $state?->color() ?? 'gray')
                    ->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('profile.city')->label('Ville')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('profile.completion')->label('Profil')
                    ->state(fn (User $record) => $record->profile?->completion ?? 0)
                    ->suffix(' %')
                    ->badge()
                    ->color(fn (int $state) => $state >= Profile::minCompletion() ? 'success' : ($state >= 40 ? 'warning' : 'gray')),
                IconColumn::make('profile.is_featured')->label('À la une')
                    ->state(fn (User $record) => (bool) $record->profile?->is_featured)
                    ->boolean()
                    ->trueIcon('heroicon-s-star')->trueColor('warning')
                    ->falseIcon('heroicon-o-minus')->falseColor('gray'),
                IconColumn::make('email_verified')->label('E-mail vérifié')->boolean()
                    ->state(fn (User $record) => $record->email_verified_at !== null)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_admin')->label('Admin')->boolean()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Inscrit le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Statut')->options(UserStatus::options()),
                SelectFilter::make('profession')->label('Métier')->options(Profession::options())
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null,
                        fn (Builder $q, string $value) => $q->whereHas('profile', fn (Builder $p) => $p->where('profession', $value)))),
                SelectFilter::make('level')->label('Niveau')->options(ExperienceLevel::options())
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null,
                        fn (Builder $q, string $value) => $q->whereHas('profile', fn (Builder $p) => $p->where('level', $value)))),
                SelectFilter::make('availability')->label('Disponibilité')->options(Availability::options())
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null,
                        fn (Builder $q, string $value) => $q->whereHas('profile', fn (Builder $p) => $p->where('availability', $value)))),
                Filter::make('listed')->label("Visibles dans l'annuaire")->toggle()
                    ->query(fn (Builder $query) => $query->whereHas('profile', fn (Builder $p) => $p->listed())),
                Filter::make('featured')->label('À la une')->toggle()
                    ->query(fn (Builder $query) => $query->whereHas('profile', fn (Builder $p) => $p->where('is_featured', true))),
                TernaryFilter::make('email_verified_at')->label('E-mail vérifié')->nullable(),
                TernaryFilter::make('is_admin')->label('Administrateur'),
            ])
            ->recordActions([
                ViewAction::make()->label('Voir'),
                EditAction::make()->label('Modifier'),
                ActionGroup::make([
                    self::featureAction(),
                    self::viewInDirectoryAction(),
                    self::reactivateAction(),
                    self::suspendAction(),
                    self::deleteAction(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('exportSelected')
                        ->label('Exporter la sélection (CSV)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Collection $records) => MembersCsvExport::download(User::query()->whereKey($records->modelKeys()))),
                ]),
            ]);
    }

    public static function reactivateAction(): Action
    {
        return Action::make('reactivate')
            ->label('Réactiver')
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Réactiver ce compte ?')
            ->modalDescription("Le membre retrouvera l'accès au Talent Board.")
            ->visible(fn (User $record) => ! $record->isApproved() && ! $record->is_admin)
            ->action(function (User $record) {
                $record->reactivate();
                Notification::make()->title('Compte réactivé.')->success()->send();
            });
    }

    /** Suppression définitive, après confirmation, avec les fichiers du membre. */
    public static function deleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->label('Supprimer')
            ->modalHeading('Supprimer ce compte ?')
            ->modalDescription('Le compte, le profil, les réalisations et les fichiers du membre seront définitivement supprimés. Cette action est irréversible.')
            ->modalSubmitActionLabel('Supprimer définitivement')
            ->successNotificationTitle('Compte supprimé.')
            ->visible(fn (User $record) => ! $record->is_admin)
            ->using(function (User $record): bool {
                $record->deleteWithMedia();

                return true;
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

    /** Marquer l'adresse e-mail comme vérifiée, quand le membre n'a pas reçu ou pas cliqué le lien de vérification. */
    public static function verifyEmailAction(): Action
    {
        return Action::make('verifyEmail')
            ->label("Marquer l'e-mail comme vérifié")
            ->icon('heroicon-o-envelope-open')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading("Marquer l'e-mail comme vérifié ?")
            ->modalDescription("À utiliser si vous avez confirmé l'adresse du membre par un autre moyen.")
            ->visible(fn (User $record) => ! $record->hasVerifiedEmail())
            ->action(function (User $record) {
                if ($record->markEmailAsVerified()) {
                    event(new Verified($record));
                }

                Notification::make()->title('E-mail marqué comme vérifié.')->success()->send();
            });
    }

    /** Mettre en avant / retirer de la une : les profils à la une s'affichent en premier dans l'annuaire. */
    public static function featureAction(): Action
    {
        return Action::make('feature')
            ->label(fn (User $record) => $record->profile?->is_featured ? 'Retirer de la une' : 'Mettre en avant')
            ->icon(fn (User $record) => $record->profile?->is_featured ? 'heroicon-o-star' : 'heroicon-s-star')
            ->color('warning')
            ->visible(fn (User $record) => ! $record->is_admin)
            ->action(function (User $record) {
                $profile = $record->ensureProfile();
                $profile->setFeatured(! $profile->is_featured);

                Notification::make()
                    ->title($profile->is_featured ? 'Profil mis en avant.' : 'Profil retiré de la une.')
                    ->body($profile->is_featured && ! $profile->isListed()
                        ? "Il s'affichera en premier dès qu'il sera visible dans l'annuaire (compte validé, profil complet à ".Profile::minCompletion().' %).'
                        : null)
                    ->success()
                    ->send();
            });
    }

    public static function viewInDirectoryAction(): Action
    {
        return Action::make('viewInDirectory')
            ->label("Voir dans l'annuaire")
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->color('gray')
            ->url(fn (User $record) => $record->profile ? route('directory.show', $record->profile) : null, shouldOpenInNewTab: true)
            ->visible(fn (User $record) => $record->profile !== null && $record->profile->completion > 0);
    }

    /** Avatar SVG aux initiales, généré localement (aucun service externe). */
    public static function initialsAvatar(User $user): string
    {
        $initials = e(mb_strtoupper(mb_substr($user->first_name, 0, 1).mb_substr($user->last_name, 0, 1)));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="32" fill="#280050"/>'
            .'<text x="32" y="41" font-family="Inter,Arial,sans-serif" font-size="24" font-weight="800" fill="#D4F01F" text-anchor="middle">'.$initials.'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
