<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // top-level categories
            [
                'name' => 'Electronics',
                'department_id' => 1,
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fashion',
                'department_id' => 2,
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // //// subcategories deapth 1
            [
                'name' => 'Computers',
                'department_id' => 1,
                'parent_id' => 1, // parent is electronics
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Smartphones',
                'department_id' => 1,
                'parent_id' => 1, // parent is electronics
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // //// subcategories deapth 2
            [
                'name' => 'Desktops',
                'department_id' => 1,
                'parent_id' => 3, // parent is computers
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Labtops',
                'department_id' => 1,
                'parent_id' => 3, // parent is computers
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // subcategories deapth 2
            [
                'name' => 'Androids',
                'department_id' => 1,
                'parent_id' => 4, // parent is Smartphones
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Iphones',
                'department_id' => 1,
                'parent_id' => 4, // parent is Smartphones
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('categories')->insert($categories);
    }
}
