<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Memastikan akun Master Admin terdaftar permanen di database
        User::updateOrCreate(
            ['email' => 'admin.master@kilat.com'],
            [
                'name'     => 'Master Admin',
                'role'     => 'admin',
                'password' => Hash::make('1111'),
            ]
        );
    }
}
