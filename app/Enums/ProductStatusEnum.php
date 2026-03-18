<?php

namespace App\Enums;

use App\Traits\EnumFeatures;

enum ProductStatusEnum: string
{
    use EnumFeatures;

    case Draft = 'draft';
    case Published = 'published';

    /**
     * Get human-readable labels for each product status.
     */
    public static function labels(): array
    {
        return [
            self::Draft->value => 'Draft',
            self::Published->value => 'Published',
        ];
    }

    /**
     * Map Filament color names to their corresponding status values.
     */
    public static function colors(): array
    {
        return [
            'gray' => self::Draft->value,
            'success' => self::Published->value,
        ];
    }
}
