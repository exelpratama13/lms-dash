<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(['id' => 1], [
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => 1,
            'created_at' => '2025-10-23 20:08:56',
            'updated_at' => '2025-10-23 20:08:56',
        ])->assignRole('admin');

        User::updateOrCreate(['id' => 2], [
            'name' => 'admin',
            'email' => 'admin@inovindo.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => 1,
            'created_at' => '2025-10-23 20:25:23',
            'updated_at' => '2025-10-23 20:25:23',
        ])->assignRole('admin');

        // Create 10 mentors
        User::factory()->count(10)->create()->each(function ($user) {
            $user->assignRole('mentor');
        });

        // Create 40 students
        User::factory()->count(40)->create()->each(function ($user) {
            $user->assignRole('student');
        });
    }
}
