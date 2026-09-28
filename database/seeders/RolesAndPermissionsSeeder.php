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
        $companyAdmin = Role::firstOrCreate(['name' => 'company_admin']); // owner
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $employee = Role::firstOrCreate(['name' => 'employee']); // member
        // Legacy aliases kept for existing data.
        Role::firstOrCreate(['name' => 'master_admin']);

        $superAdmin->givePermissionTo(Permission::all());
        // Owner: manage company, billing, members, delete.
        $companyAdmin->givePermissionTo(['manage companies', 'manage company settings', 'manage departments', 'manage users', 'assign tasks', 'view reports', 'delete tasks', 'create tasks', 'edit tasks', 'manage time']);
        // Manager: create tasks/departments, assign, report. No billing, no company delete.
        $manager->givePermissionTo(['manage departments', 'manage users', 'assign tasks', 'view reports', 'create tasks', 'edit tasks', 'manage time']);
        // Member: work on assigned tasks, comment, log time.
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
