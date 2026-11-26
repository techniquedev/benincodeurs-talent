<?php

namespace App\Filament\Resources\Skills;

use App\Filament\Concerns\AuthorizesByAbility;
use App\Filament\Resources\Skills\Pages\ManageSkills;
use App\Models\Skill;
use App\Models\SkillCategory;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class SkillResource extends Resource
{
    use AuthorizesByAbility;

    protected static ?string $model = Skill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Compétences';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'compétence';

    protected static ?string $pluralModelLabel = 'compétences';

    protected static ?string $navigationLabel = 'Compétences';

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
                Select::make('skill_category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name', fn ($query) => $query->orderBy('sort_order'))
                    ->required()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->label('Nom de la catégorie')->required()->maxLength(100)->unique('skill_categories', 'name'),
                    ]),
                TextInput::make('sort_order')
                    ->label('Ordre dans la catégorie')
                    ->numeric()
                    ->minValue(0)
                    ->default(100)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('name')
            ->modifyQueryUsing(fn ($query) => $query->with('category'))
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Catégorie')->badge()->sortable(),
                TextColumn::make('profiles_count')->label('Membres')->counts('profiles')->sortable(),
                TextColumn::make('sort_order')->label('Ordre')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('skill_category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription('La compétence sera retirée des profils qui l\'utilisent.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('moveToCategory')
                        ->label('Changer de catégorie')
                        ->authorize(fn (): bool => self::allows('update'))
                        ->icon('heroicon-o-folder-arrow-down')
                        ->schema([
                            Select::make('skill_category_id')
                                ->label('Nouvelle catégorie')
                                ->options(fn () => SkillCategory::orderBy('sort_order')->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update(['skill_category_id' => $data['skill_category_id']]);
                            Notification::make()->title('Compétences déplacées.')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSkills::route('/'),
        ];
    }
}
