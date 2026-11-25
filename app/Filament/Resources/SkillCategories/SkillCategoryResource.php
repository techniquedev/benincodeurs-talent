<?php

namespace App\Filament\Resources\SkillCategories;

use App\Filament\Resources\SkillCategories\Pages\ManageSkillCategories;
use App\Models\SkillCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SkillCategoryResource extends Resource
{
    protected static ?string $model = SkillCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|UnitEnum|null $navigationGroup = 'Compétences';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'catégorie';

    protected static ?string $pluralModelLabel = 'catégories';

    protected static ?string $navigationLabel = 'Catégories';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                TextInput::make('sort_order')
                    ->label('Ordre')
                    ->helperText("Plus petit = affiché en premier dans le formulaire de profil et l'annuaire.")
                    ->numeric()
                    ->minValue(0)
                    ->default(fn () => (int) SkillCategory::max('sort_order') + 10)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                TextColumn::make('skills_count')->label('Compétences')->counts('skills')->badge()->sortable(),
                TextColumn::make('sort_order')->label('Ordre')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription('Seules les catégories vides peuvent être supprimées.')
                    ->visible(fn (SkillCategory $record) => ! $record->skills()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSkillCategories::route('/'),
        ];
    }
}
