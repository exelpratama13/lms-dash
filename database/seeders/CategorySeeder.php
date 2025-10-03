<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{

    public function run()
    {

        $categories = [
            ['name' => 'Web Development', 'slug' => 'web-development'],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development'],
            ['name' => 'Data Science & AI', 'slug' => 'data-science-ai'],
            ['name' => 'Cyber Security', 'slug' => 'cyber-security'],
            ['name' => 'UI/UX Design', 'slug' => 'ui-ux-design'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
