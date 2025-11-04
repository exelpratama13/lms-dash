<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset password for known admin emails so you can login during development
        $emails = [
            'admin@inovindo.com',
            'admin@example.com',
        ];

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->password = Hash::make('12345678');
                $user->save();
                // Ensure role 'admin' assigned (uses Spatie HasRoles)
                try {
                    $user->assignRole('admin');
                } catch (\Throwable $e) {
                    // ignore if roles table not present or other issues
                }
            }
        }
    }
}
