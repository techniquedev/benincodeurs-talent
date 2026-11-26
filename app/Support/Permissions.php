<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Droits de l'équipe dans l'admin : 5 cases globales, valables sur tout le tableau de bord
 * (membres, annuaire, compétences, catégories, export CSV). Stockés en permissions directes
 * (spatie/laravel-permission) nommées « admin.action ». Le super-admin (ADMIN_EMAIL) les a toutes.
 */
class Permissions
{
    /** @var array<string, string> Action => libellé, dans l'ordre d'affichage. */
    public const ABILITIES = [
        'view' => 'Voir',
        'create' => 'Créer',
        'update' => 'Modifier',
        'delete' => 'Supprimer',
        'export' => 'Exporter',
    ];

    /** Droit obligatoire : sans lui, aucune section n'est visible. */
    public const REQUIRED = 'view';

    public static function name(string $ability): string
    {
        return 'admin.'.$ability;
    }

    /** @return list<string> Tous les noms de permissions. */
    public static function all(): array
    {
        return array_map(self::name(...), array_keys(self::ABILITIES));
    }

    /** L'utilisateur connecté (ou celui donné) a-t-il ce droit ? */
    public static function allows(string $ability, ?User $user = null): bool
    {
        $user ??= Auth::user();

        return $user instanceof User && $user->can(self::name($ability));
    }

    /** @return list<string> Actions accordées à un utilisateur (ex. ['view', 'export']), dans l'ordre d'affichage. */
    public static function abilitiesOf(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return array_keys(self::ABILITIES);
        }

        $granted = $user->getDirectPermissions()->pluck('name')->all();

        return array_values(array_filter(array_keys(self::ABILITIES), fn (string $ability): bool => in_array(self::name($ability), $granted, true)));
    }

    /**
     * Remplace les droits d'un utilisateur par les actions cochées (« Voir » toujours inclus).
     *
     * @param  array<int, string>  $abilities
     */
    public static function sync(User $user, array $abilities): void
    {
        $abilities = array_intersect(array_keys(self::ABILITIES), [...$abilities, self::REQUIRED]);

        $user->syncPermissions(array_map(self::name(...), array_values($abilities)));
    }
}
