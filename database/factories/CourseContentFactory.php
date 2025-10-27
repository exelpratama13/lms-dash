<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CourseSection;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseContent>
 */
class CourseContentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(4),
            'course_section_id' => CourseSection::inRandomOrder()->value('id') ?? 1,
            'content' => $this->faker->paragraph(6),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
}
