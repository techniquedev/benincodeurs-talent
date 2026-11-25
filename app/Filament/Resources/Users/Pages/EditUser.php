<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('Voir'),
            UsersTable::verifyEmailAction(),
            UsersTable::featureAction(),
            UsersTable::viewInDirectoryAction(),
            UsersTable::reactivateAction(),
            UsersTable::suspendAction(),
            UsersTable::deleteAction(),
        ];
    }
}
