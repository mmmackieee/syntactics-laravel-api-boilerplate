<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Optional but recommended: clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Roles (using sanctum guard explicitly)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);

        $permissions = ['manage users', 'view users', 'edit settings'];

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'sanctum',
            ]);

            // Assign to admin
            $admin->givePermissionTo($permission);
        }

        // Assign basic permission to user
        $user->givePermissionTo('view users');
    }
}
