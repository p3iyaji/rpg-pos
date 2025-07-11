<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('units')->insert([
            [
                'name' => 'Olonka',
                'slug' => 'olonka',
                'description' => 'Standard metal measuring cup (approx. 1.2–1.5 kg of rice)',
                'is_active' => true,
            ],
            [
                'name' => 'Tubbers',
                'slug' => 'tubbers',
                'description' => 'Standard for some root crops',
                'is_active' => true,
            ],
            [
                'name' => 'Derica',
                'slug' => 'derica',
                'description' => 'Tomato paste tin used as a unit (≈ 1/4 of an olonka)',
                'is_active' => true,
            ],
            [
                'name' => 'Custard Bucket',
                'slug' => 'custard-bucket',
                'description' => 'A plastic bucket (~2–2.5 kg depending on the item)',
                'is_active' => true,
            ],
            [
                'name' => 'Paint Rubber',
                'slug' => 'paint-rubber',
                'description' => '4-liter paint container (~3.5–5 kg of dry food)',
                'is_active' => true,
            ],
            [
                'name' => 'Bag',
                'slug' => 'bag',
                'description' => 'A sack, typically 50 kg or 100 kg depending on the commodity',
                'is_active' => true,
            ],
            [
                'name' => 'Mudu',
                'slug' => 'mudu',
                'description' => 'Hausa-origin unit (~1 kg or more, varies by region)',
                'is_active' => true,
            ],
            [
                'name' => 'Kongo',
                'slug' => 'kongo',
                'description' => 'Similar to olonka; used for grains, beans, etc',
                'is_active' => true,
            ],


        ]);
    }
}
