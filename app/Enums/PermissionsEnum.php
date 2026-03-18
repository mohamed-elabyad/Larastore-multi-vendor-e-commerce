<?php

namespace App\Enums;

use App\Traits\EnumFeatures;

enum PermissionsEnum: string
{
    use EnumFeatures;

    case ApproveVendors = 'approve_vendors';
    case SellProducts = 'sell_products';
    case BuyProducts = 'buy_products';
}
