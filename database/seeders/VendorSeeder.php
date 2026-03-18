<?php

namespace Database\Seeders;

use App\Enums\VendorStatusEnum;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        [
            'status' => VendorStatusEnum::Approved,
            'store_name' => 'Vendor Store',
            'store_address' => fake()->address(),
        ];
    }
}
