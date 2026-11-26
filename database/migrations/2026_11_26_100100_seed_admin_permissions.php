<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crée les permissions de l'admin (section.action) et un rôle « Administrateur » qui les a toutes,
 * attribué aux administrateurs existants pour qu'ils gardent leur accès. Le super-admin (ADMIN_EMAIL)
 * n'a pas besoin de rôle : il a tous les droits. Converti ensuite en droits globaux (migration suivante).
 */
return new class extends Migration
{
    /** @var list<string> Permissions par section, figées à la date de cette migration. */
    private const PERMISSIONS = [
        'members.view', 'members.update', 'members.delete', 'members.export',
        'directory.view',
        'skills.view', 'skills.create', 'skills.update', 'skills.delete',
        'skill_categories.view', 'skill_categories.create', 'skill_categories.update', 'skill_categories.delete',
        'team.view', 'team.create', 'team.update', 'team.delete',
        'roles.view', 'roles.create', 'roles.update', 'roles.delete',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $role = Role::findOrCreate('Administrateur', 'web');
        $role->syncPermissions(self::PERMISSIONS);

        User::query()->where('is_admin', true)->each(fn (User $admin) => $admin->assignRole($role));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Role::query()->where('name', 'Administrateur')->delete();
        Permission::query()->whereIn('name', self::PERMISSIONS)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
