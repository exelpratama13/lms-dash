<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed roles (ids preserved to match SQL dump)
        DB::table('roles')->upsert([
            ['id' => 1, 'name' => 'admin', 'guard_name' => 'web', 'created_at' => '2025-10-23 20:25:23', 'updated_at' => '2025-10-23 20:25:23'],
            ['id' => 2, 'name' => 'mentor', 'guard_name' => 'web', 'created_at' => '2025-10-23 20:25:23', 'updated_at' => '2025-10-23 20:25:23'],
            ['id' => 3, 'name' => 'student', 'guard_name' => 'web', 'created_at' => '2025-10-23 20:25:23', 'updated_at' => '2025-10-23 20:25:23'],
        ], ['id']);

        // Permissions can be added here if needed. For now we'll keep tables consistent.

        // Assign role to user (model_has_roles)
        // Example from SQL: role_id=1 for App\Models\User id=2
        DB::table('model_has_roles')->upsert([
            ['role_id' => 1, 'model_type' => 'App\\Models\\User', 'model_id' => 2],
        ], ['role_id', 'model_type', 'model_id']);
    }
}
