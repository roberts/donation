<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view donor',
            'edit donor',
            'create donor',
            'view donation',
            'edit donation',
            'create donation',
            'view address',
            'edit address',
            'create address',
            'view note',
            'edit note',
            'create note',
            'delete note',
            'view school',
            'edit school',
            'create school',
            'delete school',
            'view user',
            'edit user',
            'create user',
            'view transaction',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign existing permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view donor',
            'view donation',
            'view address',
            'view school',
            'view user',
            'view transaction',
            'view note',
            'edit note',
            'create note',
            'delete note',
        ]);

        $donorRole = Role::firstOrCreate(['name' => 'donor']);
        $donorRole->givePermissionTo([
            'view donor',
            'view donation',
            'view address',
            'view school',
            'view user',
            'view transaction',
        ]);
    }
}
