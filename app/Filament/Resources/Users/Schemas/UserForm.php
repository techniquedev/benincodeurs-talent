<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\SkillLevel;
use App\Enums\UserStatus;
use App\Models\Profile;
use App\Models\User;
use App\Support\Media;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Propaganistas\LaravelPhone\PhoneNumber;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')->label('Prénom')->required()->maxLength(100),
                        TextInput::make('last_name')->label('Nom')->required()->maxLength(100),
                        TextInput::make('email')->label('E-mail')->email()->required()->unique(ignoreRecord: true),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->rule('phone:INTERNATIONAL,'.config('talent.phone_country'))
                            ->dehydrateStateUsing(fn (?string $state) => filled($state)
                                ? (new PhoneNumber($state, config('talent.phone_country')))->formatE164()
                                : null)
                            ->unique(ignoreRecord: true),
                    ]),
                Section::make('Compte')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('status')->label('Statut')->badge()
                            ->formatStateUsing(fn (UserStatus $state) => $state->label())
                            ->color(fn (UserStatus $state) => $state->color()),
                        TextEntry::make('email_verified_at')->label('E-mail vérifié le')->dateTime('d/m/Y H:i')->placeholder('Non vérifié'),
                        TextEntry::make('created_at')->label('Inscrit le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('approved_at')->label('Validé le')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('suspended_at')->label('Suspendu le')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('is_admin')->label('Administrateur')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Oui' : 'Non'),
                    ])
                    ->visible(fn (?User $record) => $record !== null),
                self::profileSection(),
            ]);
    }

    /** Profil du membre, en lecture seule : c'est le membre qui le remplit. */
    private static function profileSection(): Section
    {
        $profile = fn (User $record): ?Profile => $record->profile;

        return Section::make('Profil')
            ->description('Rempli par le membre. Lecture seule.')
            ->columns(3)
            ->collapsible()
            ->visible(fn (?User $record) => $record !== null)
            ->schema([
                ImageEntry::make('profile_photo')->label('Photo')
                    ->state(fn (User $record) => Media::url($profile($record)?->photo_path, 200, 200))
                    ->circular()->imageSize(96)
                    ->placeholder('Aucune photo'),
                TextEntry::make('profile_completion')->label('Complétion')
                    ->state(fn (User $record) => ($profile($record)?->completion ?? 0).' %')
                    ->badge()
                    ->color(fn (User $record) => ($profile($record)?->completion ?? 0) >= Profile::minCompletion() ? 'success' : 'warning')
                    ->helperText(fn (User $record) => $profile($record)?->isListed() ? "Visible dans l'annuaire." : "Pas encore visible dans l'annuaire."),
                TextEntry::make('profile_featured')->label('À la une')
                    ->state(fn (User $record) => $profile($record)?->is_featured ? 'Oui, depuis le '.$profile($record)->featured_at?->format('d/m/Y') : 'Non'),
                TextEntry::make('profile_headline')->label('Titre')->state(fn (User $record) => $profile($record)?->headline)->placeholder('—'),
                TextEntry::make('profile_city')->label('Ville')->state(fn (User $record) => $profile($record)?->city)->placeholder('—'),
                TextEntry::make('profile_profession')->label('Métier')->state(fn (User $record) => $profile($record)?->profession?->label())->placeholder('—'),
                TextEntry::make('profile_level')->label('Niveau')->state(fn (User $record) => $profile($record)?->level?->label())->placeholder('—'),
                TextEntry::make('profile_years')->label("Années d'expérience")->state(fn (User $record) => $profile($record)?->years_experience)->placeholder('—'),
                TextEntry::make('profile_availability')->label('Disponibilité')->state(fn (User $record) => $profile($record)?->availability?->label())->placeholder('—'),
                TextEntry::make('profile_situation')->label('Situation')
                    ->state(fn (User $record) => collect([
                        $profile($record)?->work_status?->label(),
                        $profile($record)?->company ? $profile($record)->company.($profile($record)->company_visible ? '' : ' (masquée)') : null,
                    ])->filter()->join(' · '))
                    ->placeholder('—'),
                TextEntry::make('profile_contact')->label('Contact visible')
                    ->state(fn (User $record) => collect([
                        $profile($record)?->whatsapp_visible ? 'WhatsApp' : null,
                        $profile($record)?->email_visible ? 'E-mail' : null,
                    ])->filter()->join(', '))
                    ->placeholder('Masqué'),
                TextEntry::make('profile_bio')->label('Présentation')
                    ->state(fn (User $record) => $profile($record)?->bio)
                    ->placeholder('—')->columnSpanFull(),
                TextEntry::make('profile_skills')->label('Compétences')
                    ->state(fn (User $record) => $profile($record)?->skills
                        ->map(fn ($skill) => $skill->name.' · '.(SkillLevel::tryFrom($skill->pivot->level)?->label() ?? ''))
                        ->all() ?? [])
                    ->badge()
                    ->icon(fn (User $record, string $state): ?Heroicon => $profile($record)?->skills
                        ->first(fn ($skill) => str_starts_with($state, $skill->name.' · '))?->pivot->is_highlighted ? Heroicon::Star : null)
                    ->color(fn (User $record, string $state): string => $profile($record)?->skills
                        ->first(fn ($skill) => str_starts_with($state, $skill->name.' · '))?->pivot->is_highlighted ? 'primary' : 'gray')
                    ->placeholder('Aucune')
                    ->columnSpanFull(),
                TextEntry::make('profile_projects')->label('Réalisations')
                    ->state(fn (User $record) => $profile($record)?->projects->pluck('title')->all() ?? [])
                    ->listWithLineBreaks()->bulleted()
                    ->placeholder('Aucune')
                    ->columnSpanFull(),
                TextEntry::make('profile_links')->label('Liens')
                    ->state(fn (User $record) => array_values($profile($record)?->links() ?? []))
                    ->listWithLineBreaks()
                    ->placeholder('Aucun')
                    ->columnSpanFull(),
            ]);
    }
}
