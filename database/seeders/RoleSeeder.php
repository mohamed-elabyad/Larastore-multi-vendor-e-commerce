<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin_role = Role::create(['name' => RolesEnum::Admin->value]);
        $vendor_role = Role::create(['name' => RolesEnum::Vendor->value]);
        $user_role = Role::create(['name' => RolesEnum::User->value]);

        $approve_vendors = Permission::create(['name' => PermissionsEnum::ApproveVendors->value]);
        $sell_products = Permission::create(['name' => PermissionsEnum::SellProducts->value]);
        $buy_products = Permission::create(['name' => PermissionsEnum::BuyProducts->value]);

        $admin_role->syncPermissions([
            $approve_vendors,
            $sell_products,
            $buy_products,
        ]);

        $vendor_role->syncPermissions([
            $sell_products,
            $buy_products,
        ]);

        $user_role->syncPermissions([$buy_products]);
    }
}
