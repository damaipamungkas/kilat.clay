<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin.damai@kilat.com'],
            [
                'name' => 'Admin Damai',
                'password' => Hash::make('1111'),
                'role' => 'admin', // Sesuaikan dengan kolom role/level di database Anda jika ada
            ]
        );
    }
}
