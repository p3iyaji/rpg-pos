<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('discounts')->insert([
            [
                'name' => 'Summer Sales',
                'code' => 'SS10',
                'type' => 'percentage',
                'scope' => 'general',
                'value' => 10,
                'start_date' => '2025-06-01T08:31:00',
                'end_date' => '2025-12-01T08:31:00',
                'min_quantity' => 1,
                'min_amount' => 1,
                'apply_to_all_products' => false,
                'is_active' => true
            ],
            [
                'name' => 'Weekend glory',
                'code' => 'WG10',
                'type' => 'percentage',
                'scope' => 'general',
                'value' => 10,
                'start_date' => '2025-06-01T08:31:00',
                'end_date' => '2025-12-01T08:31:00',
                'min_quantity' => 1,
                'min_amount' => 1,
                'apply_to_all_products' => false,
                'is_active' => true
            ]
        ]);
    }
}
