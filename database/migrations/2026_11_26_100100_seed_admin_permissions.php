<?php

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crée les permissions de l'admin (section.action) et un rôle « Administrateur » qui les a toutes,
 * attribué aux administrateurs existants pour qu'ils gardent leur accès. Le super-admin (ADMIN_EMAIL)
 * n'a pas besoin de rôle : il a tous les droits.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $role = Role::findOrCreate('Administrateur', 'web');
        $role->syncPermissions(Permissions::all());

        User::query()->where('is_admin', true)->each(fn (User $admin) => $admin->assignRole($role));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Role::query()->where('name', 'Administrateur')->delete();
        Permission::query()->whereIn('name', Permissions::all())->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
