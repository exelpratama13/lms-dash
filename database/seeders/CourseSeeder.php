<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::factory()->count(5)->create([
            'category_id' => 1,
            'is_popular' => true,
        ]);
    }
}
