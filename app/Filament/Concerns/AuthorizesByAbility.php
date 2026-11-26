<?php

namespace App\Filament\Concerns;

use App\Support\Permissions;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Autorise une ressource Filament à partir des 5 droits globaux de l'utilisateur (App\Support\Permissions) :
 * le menu, les pages et les actions standard (créer, modifier, supprimer…) suivent ces droits.
 */
trait AuthorizesByAbility
{
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $action = $action instanceof UnitEnum ? $action->name : $action;

        $ability = match ($action) {
            'viewAny', 'view' => 'view',
            'create', 'replicate' => 'create',
            'update', 'reorder' => 'update',
            'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny', 'restore', 'restoreAny' => 'delete',
            default => null,
        };

        return $ability !== null && static::allows($ability) ? Response::allow() : Response::deny();
    }

    public static function allows(string $ability): bool
    {
        return Permissions::allows($ability);
    }
}
