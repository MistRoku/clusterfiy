<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage companies',
            'manage company settings',
            'manage departments',
            'manage users',
            'create tasks',
            'edit tasks',
            'delete tasks',
            'assign tasks',
            'view reports',
            'manage time',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $masterAdmin = Role::firstOrCreate(['name' => 'master_admin']);
        $companyAdmin = Role::firstOrCreate(['name' => 'company_admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $employee = Role::firstOrCreate(['name' => 'employee']);

        $superAdmin->givePermissionTo(Permission::all());
        $masterAdmin->givePermissionTo(['manage company settings', 'view reports']);
        $companyAdmin->givePermissionTo(['manage departments', 'manage users', 'assign tasks', 'view reports', 'delete tasks']);
        $manager->givePermissionTo(['manage users', 'assign tasks', 'view reports', 'create tasks', 'edit tasks']);
        $employee->givePermissionTo(['create tasks', 'edit tasks', 'manage time']);

        $user = User::firstOrCreate(
            ['email' => 'super@clusterfiy.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('super_admin');
    }
}
