<?php

namespace App\Enums;

use App\Traits\EnumFeatures;

enum VendorStatusEnum: string
{
    use EnumFeatures;

    case Pending = 'pending';

    case Approved = 'approved';

    case Rejected = 'rejected';

    /**
     * Get human-readable labels for each vendor status.
     */
    public static function labels(): array
    {
        return [
            self::Pending->value => 'Pending',
            self::Approved->value => 'Approved',
            self::Rejected->value => 'Rejected',
        ];
    }

    /**
     * Map Filament color names to their corresponding status values.
     */
    public static function colors(): array
    {
        return [
            'gray' => self::Pending->value,
            'success' => self::Approved->value,
            'danger' => self::Rejected->value,
        ];
    }
}
