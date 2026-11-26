<?php

namespace App\Filament\Resources\TeamUsers;

use App\Enums\UserStatus;
use App\Filament\Concerns\AuthorizesBySection;
use App\Filament\Resources\TeamUsers\Pages\ManageTeamUsers;
use App\Models\User;
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
use UnitEnum;

/**
 * Comptes de l'équipe Bénin Codeurs (administrateurs), distincts des talents : créés ici, sans vérification VIP.
 * Leurs droits viennent de leurs rôles. Le compte principal (ADMIN_EMAIL) est super-admin : tous les droits,
 * jamais supprimable ni rétrogradable, et modifiable par lui seul.
 */
class TeamUserResource extends Resource
{
    use AuthorizesBySection;

    protected static ?string $model = User::class;

    protected static ?string $slug = 'utilisateurs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Équipe';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $pluralModelLabel = 'utilisateurs';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $recordTitleAttribute = 'email';

    public static function permissionSection(): string
    {
        return 'team';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_admin', true);
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
                Section::make('Rôles')
                    ->description('Les droits de chaque rôle se règlent dans « Rôles ». Un utilisateur sans rôle ne voit aucune section.')
                    ->schema([
                        Text::make('Super-admin : tous les droits, quels que soient ses rôles. Ce compte ne peut être ni supprimé ni rétrogradé.')
                            ->visible(fn (?User $record): bool => (bool) $record?->isSuperAdmin()),
                        CheckboxList::make('roles')->label('Rôles')
                            ->relationship('roles', 'name')
                            ->columns(2)
                            ->hidden(fn (?User $record): bool => (bool) $record?->isSuperAdmin()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('roles'))
            ->columns([
                TextColumn::make('name')->label('Nom')
                    ->state(fn (User $record): string => $record->name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name']),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                TextColumn::make('access')->label('Rôles')->badge()
                    ->state(fn (User $record): array => $record->isSuperAdmin() ? ['Super-admin'] : $record->roles->pluck('name')->all())
                    ->color(fn (string $state): string => $state === 'Super-admin' ? 'primary' : 'gray')
                    ->placeholder('Aucun rôle'),
                TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn (UserStatus $state): string => $state->label())
                    ->color(fn (UserStatus $state): string => $state->color())
                    ->icon(fn (UserStatus $state): string => $state->icon()),
                TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Modifier'),
                DeleteAction::make()->label('Supprimer')
                    ->modalHeading('Supprimer cet utilisateur ?')
                    ->modalDescription("Il perdra immédiatement l'accès à l'admin. Cette action est irréversible."),
            ]);
    }

    /**
     * Crée un compte de l'équipe : administrateur, validé, e-mail vérifié, sans vérification VIP.
     *
     * @param  array<string, mixed>  $data
     */
    public static function createTeamUser(array $data): User
    {
        $user = new User(Arr::only($data, ['first_name', 'last_name', 'email', 'password']));
        $user->forceFill([
            'is_admin' => true,
            'status' => UserStatus::Approved,
            'approved_at' => now(),
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTeamUsers::route('/'),
        ];
    }
}
