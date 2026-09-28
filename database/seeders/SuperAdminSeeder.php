<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'super@clusterfiy.com',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
