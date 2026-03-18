<?php

namespace App\Traits;

trait EnumFeatures
{
    /**
     * Get all case values as a flat array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Build a value => name array suitable for form select fields.
     */
    public static function toSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->name])
            ->toArray();
    }
}
