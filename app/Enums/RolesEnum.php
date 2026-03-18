<?php

namespace App\Enums;

use App\Traits\EnumFeatures;

enum RolesEnum: string
{
    use EnumFeatures;

    case Admin = 'admin';
    case Vendor = 'vendor';
    case User = 'user';
}
