<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('users')->insert([
        //     [
        //         'name' => 'Test User',
        //         'email' => 'admin@hunter.com',
        //         'password' => Hash::make('hunter123')
        //     ]
        // ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@hunter.com',
            'password' => Hash::make('nano123')
        ]);
        $admin->assignRole('admin');

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@hunter.com',
            'password' => Hash::make('nano123')
        ]);
        $manager->assignRole('manager');

        $cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@hunter.com',
            'password' => Hash::make('nano123')
        ]);
        $cashier->assignRole('cashier');
    }
}
