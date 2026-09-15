<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed base roles & permissions. Trigger/action-specific permissions
     * will be added once those modules are built in a later phase.
     */
    public function run(): void
    {
        $permissions = [
            'access admin',
            'manage users',
            'manage admin users',
            'manage roles',
            'manage features',
            'manage packages',
            'manage subscriptions',
            'manage gifts',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $superAdmin = Role::findOrCreate('Super Admin');
        $superAdmin->syncPermissions($permissions);

        $operator = Role::findOrCreate('Operator');
        $operator->syncPermissions(['access admin', 'manage users']);

        $user = User::firstOrCreate(
            ['email' => 'admin@sampoernafinity.test'],
            ['name' => 'Super Admin', 'username' => 'superadmin', 'password' => bcrypt('password'), 'is_active' => true]
        );

        $user->syncRoles([$superAdmin]);
    }
}
