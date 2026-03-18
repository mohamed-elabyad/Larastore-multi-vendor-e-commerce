<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Draft = 'draft';

    case Paid = 'paid';

    case Shipped = 'shipped';

    case Delivered = 'delivered';

    case Cancelled = 'cancelled';

    /**
     * Get human-readable labels for each order status.
     */
    public static function labels()
    {
        return [
            self::Draft->value => 'Draft',
            self::Paid->value => 'Paid',
            self::Shipped->value => 'Shipped',
            self::Delivered->value => 'Delivered',
            self::Cancelled->value => 'Cancelled',
        ];
    }
}
