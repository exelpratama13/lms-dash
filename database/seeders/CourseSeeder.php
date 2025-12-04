<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Create 5 courses for each of the first 3 categories
        for ($i = 1; $i <= 3; $i++) {
            Course::factory()->count(5)->create([
                'category_id' => $i,
                'is_popular' => true,
            ]);
        }
    }
}
