<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //using factory
        //Category::factory()->count(10)->create();

        //without factory
        DB::table('categories')->insert([

            [
                'name' => 'Grains & Cereals',
                'slug' => 'grains-&-cereals',
                'description' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Tubers & Root Crops',
                'slug' => 'tubers-&-root-crops',
                'description' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Legumes & Pulses',
                'slug' => 'legumes-&-pulses',
                'description' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Oils',
                'slug' => 'oils',
                'description' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Liquids',
                'slug' => 'liquids',
                'description' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Seasoning',
                'slug' => 'seasoning',
                'description' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Fresh Produce',
                'slug' => 'fresh-produce',
                'description' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Protein',
                'slug' => 'protein',
                'description' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Clothes or Fabrics',
                'slug' => 'clothes-or-fabrics',
                'description' => '',
                'is_active' => true,
            ],


        ]);

    }
}
