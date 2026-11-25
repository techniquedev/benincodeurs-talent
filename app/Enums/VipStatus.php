<?php

namespace App\Enums;

/** Résultat de la vérification d'un e-mail auprès de l'API VIP du site principal. */
enum VipStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case NotFound = 'not_found';
}
