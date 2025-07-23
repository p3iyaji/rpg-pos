<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('expense_categories')->insert([
            [
                'name' => 'Salaries',
            ],
            [
                'name' => 'Fuel'
            ],
            [
                'name' => 'Transportation'
            ],
            [
                'name' => 'Internet'
            ]
        ]);
    }
}
