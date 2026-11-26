<?php

namespace App\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Approved => 'Validé',
            self::Suspended => 'Suspendu',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Suspended => 'danger',
        };
    }

    /** Icône Heroicon du badge de statut (admin), pour un statut lisible sans la couleur. */
    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::Approved => 'heroicon-m-check-circle',
            self::Suspended => 'heroicon-m-no-symbol',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
