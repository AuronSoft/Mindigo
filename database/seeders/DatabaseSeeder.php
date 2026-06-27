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

        // Teacher account cố định để test
        User::firstOrCreate(['email' => 'teacher@mindigo.com'], [
            'name'     => 'Nguyễn Văn Giáo',
            'password' => Hash::make('123456'),
            'role'     => 'teacher',
            'is_active'=> true,
        ]);

        // Teacher accounts
        User::factory(10)->create([
            'role' => 'teacher',
        ]);

        // Student account cố định để test
        User::firstOrCreate(['email' => 'student@mindigo.com'], [
            'name'     => 'Trần Văn Học',
            'password' => Hash::make('123456'),
            'role'     => 'student',
            'is_active'=> true,
        ]);

        // Student accounts
        User::factory(30)->create([
            'role' => 'student',
        ]);
    }
}