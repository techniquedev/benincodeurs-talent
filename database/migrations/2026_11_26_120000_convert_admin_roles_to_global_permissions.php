<?php

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Remplace les rôles par 5 droits globaux (admin.view, admin.create, admin.update, admin.delete, admin.export).
 * Chaque compte de l'équipe reçoit l'union des actions de ses anciens rôles, toutes sections confondues
 * (ex. « members.export » + « skills.create » → Exporter + Créer), plus « Voir », obligatoire. Les droits des
 * anciennes sections Utilisateurs et Rôles ne comptent pas : Utilisateurs est désormais réservé au super-admin.
 * Les rôles et les anciennes permissions par section sont ensuite supprimés.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        User::query()->where('is_admin', true)->with(['roles.permissions', 'permissions'])->each(function (User $admin): void {
            $abilities = $admin->roles->flatMap->permissions
                ->merge($admin->permissions)
                ->pluck('name')
                ->reject(fn (string $name): bool => str_starts_with($name, 'team.') || str_starts_with($name, 'roles.'))
                ->map(fn (string $name): string => str_contains($name, '.') ? substr($name, strrpos($name, '.') + 1) : $name)
                ->unique()
                ->values()
                ->all();

            $admin->syncRoles([]);
            Permissions::sync($admin, $abilities);
        });

        Role::query()->delete();
        Permission::query()->whereNotIn('name', Permissions::all())->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** Recrée un rôle « Administrateur » complet pour les comptes de l'équipe (les droits fins ne sont pas restaurés). */
    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $sectionPermissions = [
            'members.view', 'members.update', 'members.delete', 'members.export',
            'directory.view',
            'skills.view', 'skills.create', 'skills.update', 'skills.delete',
            'skill_categories.view', 'skill_categories.create', 'skill_categories.update', 'skill_categories.delete',
            'team.view', 'team.create', 'team.update', 'team.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
        ];

        foreach ($sectionPermissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $role = Role::findOrCreate('Administrateur', 'web');
        $role->syncPermissions($sectionPermissions);

        User::query()->where('is_admin', true)->each(fn (User $admin) => $admin->syncPermissions([])->assignRole($role));

        Permission::query()->whereIn('name', Permissions::all())->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
