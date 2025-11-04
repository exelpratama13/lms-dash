<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseProgress>
 */
class CourseProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'course_id' => \App\Models\Course::inRandomOrder()->first()->id,
            'course_batch_id' => \App\Models\CourseBatch::inRandomOrder()->first()->id,
            'course_section_id' => \App\Models\CourseSection::inRandomOrder()->first()->id,
            'course_content_id' => \App\Models\CourseContent::inRandomOrder()->first()->id,
            'is_completed' => $this->faker->boolean,
            'completed_at' => $this->faker->boolean ? now() : null,
        ];
    }
}
