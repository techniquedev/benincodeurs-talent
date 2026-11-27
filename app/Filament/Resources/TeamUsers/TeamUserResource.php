<?php

namespace App\Filament\Resources\TeamUsers;

use App\Enums\UserStatus;
use App\Filament\Resources\TeamUsers\Pages\ManageTeamUsers;
use App\Models\User;
use App\Support\Permissions;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * Comptes de l'équipe Bénin Codeurs (administrateurs), distincts des talents : créés ici, sans vérification VIP.
 * Section réservée au super-admin (ADMIN_EMAIL). Chaque compte reçoit 5 droits globaux (Voir, Créer, Modifier,
 * Supprimer, Exporter) valables sur tout le tableau de bord. Le compte principal n'est jamais supprimable.
 */
class TeamUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'utilisateurs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Équipe';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $pluralModelLabel = 'utilisateurs';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $recordTitleAttribute = 'email';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_admin', true);
    }

    /** Toute la section (menu, liste, création, modification, suppression) est réservée au super-admin. */
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $currentUser = Auth::user();

        return $currentUser instanceof User && $currentUser->isSuperAdmin() ? Response::allow() : Response::deny();
    }

    /** Le compte principal n'est modifiable que par lui-même. */
    public static function getEditAuthorizationResponse(Model $record): Response
    {
        if ($record instanceof User && $record->isSuperAdmin() && ! $record->is(Auth::user())) {
            return Response::deny('Seul le super-admin peut modifier son propre compte.');
        }

        return parent::getEditAuthorizationResponse($record);
    }

    /** Jamais de suppression du compte principal, ni de son propre compte. */
    public static function getDeleteAuthorizationResponse(Model $record): Response
    {
        if ($record instanceof User && ($record->isSuperAdmin() || $record->is(Auth::user()))) {
            return Response::deny();
        }

        return parent::getDeleteAuthorizationResponse($record);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')->label('Prénom')->required()->maxLength(100),
                        TextInput::make('last_name')->label('Nom')->required()->maxLength(100),
                        TextInput::make('email')->label('E-mail')->email()->required()->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (string $state): string => strtolower(trim($state)))
                            ->disabled(fn (?User $record): bool => (bool) $record?->isSuperAdmin())
                            ->helperText(fn (?User $record): ?string => $record?->isSuperAdmin() ? "L'e-mail du compte principal est fixé par ADMIN_EMAIL." : null),
                        TextInput::make('password')->label('Mot de passe')
                            ->password()->revealable()
                            ->minLength(12)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? '12 caractères minimum. Communiquez-le à la personne, qui pourra le changer avec « Mot de passe oublié ».'
                                : 'Laisser vide pour garder le mot de passe actuel.'),
                    ]),
                Section::make('Droits')
                    ->description('Valables sur tout le tableau de bord : membres, annuaire, compétences, catégories et export CSV. Modifiables à tout moment.')
                    ->schema([
                        Text::make('Super-admin : tous les droits, y compris la gestion des utilisateurs. Ce compte ne peut pas être supprimé.')
                            ->visible(fn (?User $record): bool => (bool) $record?->isSuperAdmin()),
                        CheckboxList::make('abilities')->hiddenLabel()
                            ->validationAttribute('droits')
                            ->options(Permissions::ABILITIES)
                            ->default([Permissions::REQUIRED])
                            ->disableOptionWhen(fn (string $value): bool => $value === Permissions::REQUIRED)
                            // Filament retire les options verrouillées de la règle « in » : « Voir », coché d'office, y est réautorisé.
                            ->in(array_keys(Permissions::ABILITIES))
                            ->validationMessages(['*.in' => 'Droit inconnu : cochez uniquement Voir, Créer, Modifier, Supprimer ou Exporter.'])
                            ->dehydrateStateUsing(fn (?array $state): array => array_values(array_unique([Permissions::REQUIRED, ...($state ?? [])])))
                            ->descriptions([Permissions::REQUIRED => 'Obligatoire'])
                            ->columns(['default' => 2, 'sm' => 3, 'lg' => 5])
                            ->gridDirection('row')
                            ->afterStateHydrated(function (CheckboxList $component, ?User $record): void {
                                if ($record) {
                                    $component->state(Permissions::abilitiesOf($record));
                                }
                            })
                            ->hidden(fn (?User $record): bool => (bool) $record?->isSuperAdmin()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('permissions'))
            ->columns([
                TextColumn::make('name')->label('Nom')
                    ->state(fn (User $record): string => $record->name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name']),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                TextColumn::make('abilities')->label('Droits')->badge()
                    ->state(fn (User $record): array => $record->isSuperAdmin()
                        ? ['Super-admin']
                        : array_map(fn (string $ability): string => Permissions::ABILITIES[$ability], Permissions::abilitiesOf($record)))
                    ->color(fn (string $state): string => $state === 'Super-admin' ? 'primary' : 'gray')
                    ->placeholder('Aucun droit'),
                TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Modifier')
                    ->using(fn (User $record, array $data): User => self::updateTeamUser($record, $data)),
                DeleteAction::make()->label('Supprimer')
                    ->modalHeading('Supprimer cet utilisateur ?')
                    ->modalDescription("Il perdra immédiatement l'accès à l'admin. Cette action est irréversible."),
            ]);
    }

    /**
     * Crée un compte de l'équipe : administrateur, validé, e-mail vérifié, sans vérification VIP.
     *
     * @param  array{first_name: string, last_name: string, email: string, password: string, abilities?: list<string>}  $data
     */
    public static function createTeamUser(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = new User(Arr::only($data, ['first_name', 'last_name', 'email', 'password']));
            $user->forceFill([
                'is_admin' => true,
                'status' => UserStatus::Approved,
                'approved_at' => now(),
                'email_verified_at' => now(),
            ])->save();

            Permissions::sync($user, $data['abilities'] ?? []);

            return $user;
        });
    }

    /**
     * Met à jour l'identité et les droits d'un compte de l'équipe (le super-admin garde tous ses droits).
     *
     * @param  array{first_name?: string, last_name?: string, email?: string, password?: string, abilities?: list<string>}  $data
     */
    public static function updateTeamUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->update(Arr::only($data, ['first_name', 'last_name', 'email', 'password']));

            if (! $user->isSuperAdmin()) {
                Permissions::sync($user, $data['abilities'] ?? []);
            }

            return $user;
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTeamUsers::route('/'),
        ];
    }
}
