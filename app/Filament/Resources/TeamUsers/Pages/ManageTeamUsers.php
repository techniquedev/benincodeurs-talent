<?php

namespace App\Filament\Resources\TeamUsers\Pages;

use App\Filament\Resources\TeamUsers\TeamUserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTeamUsers extends ManageRecords
{
    protected static string $resource = TeamUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel utilisateur')
                ->using(fn (array $data): User => TeamUserResource::createTeamUser($data)),
        ];
    }
}
