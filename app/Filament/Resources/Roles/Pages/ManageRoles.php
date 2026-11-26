<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Spatie\Permission\Models\Role;

class ManageRoles extends ManageRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau rôle')
                ->using(function (array $data): Role {
                    $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
                    $role->syncPermissions(RoleResource::permissionsFromFormData($data));

                    return $role;
                }),
        ];
    }
}
