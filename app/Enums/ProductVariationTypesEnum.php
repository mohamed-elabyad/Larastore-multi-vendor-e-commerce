<?php

namespace App\Enums;

use App\Traits\EnumFeatures;

enum ProductVariationTypesEnum: string
{
    use EnumFeatures;

    case Select = 'select';
    case Radio = 'radio';
    case Image = 'image';

    /**
     * Get human-readable labels for each variation display type.
     */
    public static function labels(): array
    {

        return [
            self::Select->value => 'Select',
            self::Radio->value => 'Radio',
            self::Image->value => 'Image',
        ];
    }
}
