<?php

namespace App\Enums\Concerns;

/** Liste « valeur => libellé » d'une énumération qui expose label(). */
trait HasOptions
{
    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
