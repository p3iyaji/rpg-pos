<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'White Yam',
                'slug' => 'white-yam',
                'description' => 'Benue White yam',
                'unit_id' => 2,
                'category_id' => 2,
                'price' => 5000,
                'cost_price' => 10000,
                'quantity' => 500,
                'is_active' => true,
                'user_id' => 1,
            ],
            [
                'name' => 'Ijebu Garri',
                'slug' => 'ijebu-garri',
                'description' => 'Ijebu garri',
                'unit_id' => 4,
                'category_id' => 2,
                'price' => 7500,
                'cost_price' => 15000,
                'quantity' => 300,
                'is_active' => true,
                'user_id' => 1,
            ],
            [
                'name' => 'Tomato Rice 50KG',
                'slug' => 'tomato-rice-50kg',
                'description' => '50Kg bag of Tomato Rice',
                'unit_id' => 1,
                'category_id' => 1,
                'price' => 50000,
                'cost_price' => 80000,
                'quantity' => 200,
                'is_active' => true,
                'user_id' => 1,
            ],

        ]);
    }
}
