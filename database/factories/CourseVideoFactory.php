<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseVideo>
 */
class CourseVideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_youtube' => $this->faker->randomElement(['ScMzIvxBSi4', '7g0_p_e_g_e_s', 'C-p-g_p_e_s_s']),
            'course_content_id' => \App\Models\CourseContent::inRandomOrder()->first()->id,
        ];
    }
}
