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
        // Insert users matching the SQL dump (preserve IDs and hashed passwords)
        DB::table('users')->upsert([
            [
                'id' => 1,
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => '$2y$12$7s/gaNx5734r6.mLCJgja.aVNh1h.hH60d.mO5yj5W8o4bE3jG2ge',
                'photo' => null,
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => '2025-10-23 20:08:56',
                'updated_at' => '2025-10-23 20:08:56',
            ],
            [
                'id' => 2,
                'name' => 'admin',
                'email' => 'admin@inovindo.com',
                'password' => '$2y$12$DG7uLNGLzVZ6ZC5nO2jBQOmsGUb.2fdKyn9dHgMbaYAXQG06bTgOS',
                'photo' => null,
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => '2025-10-23 20:25:23',
                'updated_at' => '2025-10-23 20:25:23',
            ],
        ], ['id']);

        User::factory()->count(50)->create();
    }
}
