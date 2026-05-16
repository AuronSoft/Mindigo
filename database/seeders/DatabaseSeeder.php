<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Mindigo\Auth\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin account
        User::create([
            'name' => 'Admin',
            'email' => 'admin@mindigo.com',
            'password' => Hash::make('123456'),
            'role' => 'admin',
        ]);

        // Teacher accounts
        User::factory(10)->create([
            'role' => 'teacher',
        ]);

        // Student accounts
        User::factory(30)->create([
            'role' => 'student',
        ]);
    }
}