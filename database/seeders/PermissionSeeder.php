<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['show', 'add', 'edit', 'delete'];

        foreach ($actions as $action) {
            Permission::create([
                'name' => 'roles-' . $action,
                'menu' => 'Roles',
                'guard_name' => 'web',
            ]);

            Permission::create([
                'name' => 'user-' . $action,
                'menu' => 'User',
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::create(['name' => 'admin']);

        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        $user = User::first();
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
