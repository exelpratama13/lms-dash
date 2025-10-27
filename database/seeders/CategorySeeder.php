<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::insert([
            ['id' => 1, 'name' => 'Web Development', 'slug' => 'web-development', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Mobile Development', 'slug' => 'mobile-development', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Data Science & AI', 'slug' => 'data-science-ai', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Cyber Security', 'slug' => 'cyber-security', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'UI/UX Design', 'slug' => 'ui-ux-design', 'created_at' => now(), 'updated_at' => now()],
        ]);
        Category::factory()->count(10)->create();
    }
}
