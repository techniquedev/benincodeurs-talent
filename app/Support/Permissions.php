<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Droits de l'équipe dans l'admin, par section et par action : « section.action » (ex. « members.export »).
 * Les rôles (spatie/laravel-permission) regroupent ces droits ; le super-admin (ADMIN_EMAIL) les a tous.
 */
class Permissions
{
    /** @var array<string, string> Action => libellé, dans l'ordre des colonnes de la grille. */
    public const ABILITIES = [
        'view' => 'Voir',
        'create' => 'Créer',
        'update' => 'Modifier',
        'delete' => 'Supprimer',
        'export' => 'Exporter',
    ];

    /**
     * Sections de l'admin et actions possibles dans chacune.
     *
     * @var array<string, array{label: string, abilities: list<string>}>
     */
    public const SECTIONS = [
        'members' => ['label' => 'Membres (talents)', 'abilities' => ['view', 'update', 'delete', 'export']],
        'directory' => ['label' => 'Annuaire', 'abilities' => ['view']],
        'skills' => ['label' => 'Compétences', 'abilities' => ['view', 'create', 'update', 'delete']],
        'skill_categories' => ['label' => 'Catégories de compétences', 'abilities' => ['view', 'create', 'update', 'delete']],
        'team' => ['label' => 'Utilisateurs (équipe)', 'abilities' => ['view', 'create', 'update', 'delete']],
        'roles' => ['label' => 'Rôles et permissions', 'abilities' => ['view', 'create', 'update', 'delete']],
    ];

    public static function name(string $section, string $ability): string
    {
        return $section.'.'.$ability;
    }

    /** @return list<string> Tous les noms de permissions. */
    public static function all(): array
    {
        return collect(self::SECTIONS)
            ->flatMap(fn (array $section, string $key) => collect($section['abilities'])->map(fn (string $ability) => self::name($key, $ability)))
            ->values()
            ->all();
    }

    /** @return array<string, string> Actions possibles d'une section : action => libellé. */
    public static function abilitiesFor(string $section): array
    {
        return array_intersect_key(self::ABILITIES, array_flip(self::SECTIONS[$section]['abilities']));
    }

    /** L'utilisateur connecté (ou celui donné) a-t-il ce droit ? */
    public static function allows(string $section, string $ability, ?User $user = null): bool
    {
        $user ??= Auth::user();

        return $user instanceof User && $user->can(self::name($section, $ability));
    }
}
