<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        Role::create(['name' => 'cashier']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'admin']);

        // Create permissions
        Permission::create(['name' => 'create-orders']);
        Permission::create(['name' => 'void-orders']);
        Permission::create(['name' => 'manage-inventory']);
        Permission::create(['name' => 'view-reports']);

        // Assign permissions to roles
        $cashierRole = Role::findByName('cashier');
        $cashierRole->givePermissionTo('create-orders');

        $managerRole = Role::findByName('manager');
        $managerRole->givePermissionTo(['create-orders', 'void-orders', 'view-reports']);

        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());

    }
}
