<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '11111111',
        ])->assignRole(RolesEnum::Admin->value);

        $vendor = User::factory()->create([
            'name' => 'Vendor',
            'email' => 'vendor@example.com',
            'password' => '11111111',

        ]);
        $vendor->assignRole(RolesEnum::Vendor->value);
        Vendor::factory()->create([
            'user_id' => $vendor->id,
            'status' => VendorStatusEnum::Approved,
            'store_name' => 'Vendor Store',
        ]);

        User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => '11111111',
        ])->assignRole(RolesEnum::User->value);
    }
}
