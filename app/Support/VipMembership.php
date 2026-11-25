<?php

namespace App\Support;

use App\Enums\VipStatus;

/** Réponse de l'API VIP : statut de l'adhésion et données de pré-remplissage (prénom, WhatsApp). */
final readonly class VipMembership
{
    public function __construct(
        public VipStatus $status,
        public ?string $firstName = null,
        public ?string $whatsapp = null,
    ) {}

    public function isActive(): bool
    {
        return $this->status === VipStatus::Active;
    }
}
