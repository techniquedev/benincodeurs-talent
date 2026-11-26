<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Concerns\AuthorizesBySection;
use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Support\Permissions;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use UnitEnum;

/**
 * Rôles de l'équipe : un nom et une grille de droits (une ligne par section de l'admin, une case par action).
 * Les changements s'appliquent immédiatement aux utilisateurs qui ont ce rôle.
 */
class RoleResource extends Resource
{
    use AuthorizesBySection;

    protected static ?string $model = Role::class;

    protected static ?string $slug = 'roles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'Équipe';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'rôle';

    protected static ?string $pluralModelLabel = 'rôles';

    protected static ?string $navigationLabel = 'Rôles';

    protected static ?string $recordTitleAttribute = 'name';

    public static function permissionSection(): string
    {
        return 'roles';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nom du rôle')
                    ->required()->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Ex. Recrutement, Lecture seule…'),
                Section::make('Permissions')
                    ->description('Cochez ce que ce rôle peut faire dans chaque section. Sans « Voir », la section est masquée du menu.')
                    ->schema(collect(Permissions::SECTIONS)->map(
                        fn (array $section, string $key): CheckboxList => CheckboxList::make("permissions_$key")
                            ->label($section['label'])
                            ->options(Permissions::abilitiesFor($key))
                            ->columns(5)
                            ->gridDirection('row')
                            ->inlineLabel()
                            ->bulkToggleable()
                            ->afterStateHydrated(fn (CheckboxList $component, ?Role $record) => $component->state(
                                $record ? self::abilitiesOf($record, $key) : []
                            ))
                    )->values()->all()),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('Rôle')->searchable()->sortable(),
                TextColumn::make('permissions_count')->label('Permissions')->counts('permissions')
                    ->suffix(' / '.count(Permissions::all())),
                TextColumn::make('team_count')->label('Utilisateurs')
                    ->state(fn (Role $record): int => $record->users()->count()),
                TextColumn::make('updated_at')->label('Modifié le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Modifier')
                    ->using(function (Role $record, array $data): Role {
                        $record->update(['name' => $data['name']]);
                        $record->syncPermissions(self::permissionsFromFormData($data));

                        return $record;
                    }),
                DeleteAction::make()->label('Supprimer')
                    ->modalHeading('Supprimer ce rôle ?')
                    ->modalDescription('Les utilisateurs qui ont ce rôle perdront les droits correspondants.'),
            ]);
    }

    /** @return list<string> Actions cochées pour une section (ex. ['view', 'export']). */
    private static function abilitiesOf(Role $role, string $section): array
    {
        return $role->permissions->pluck('name')
            ->filter(fn (string $name): bool => str_starts_with($name, $section.'.'))
            ->map(fn (string $name): string => substr($name, strlen($section) + 1))
            ->values()
            ->all();
    }

    /**
     * Noms des permissions cochées dans la grille.
     *
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    public static function permissionsFromFormData(array $data): array
    {
        return collect(Permissions::SECTIONS)
            ->flatMap(fn (array $section, string $key) => collect($data["permissions_$key"] ?? [])
                ->intersect($section['abilities'])
                ->map(fn (string $ability): string => Permissions::name($key, $ability)))
            ->values()
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}
