<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SimpleSeeder extends Seeder
{
    public function run()
    {
        // Insert Super Admin directly
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'super@clusterfiy.com',
            'password' => Hash::make('password'),
            'is_super_admin' => 1,
            'email_verified_at' => now(),
        ]);

        // You can also insert roles/permissions via DB::table('roles')->insert(...)
        // But it's easier to use Spatie's models directly in Tinker (see below)
    }
}
