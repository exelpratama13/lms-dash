<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sertificate>
 */
class SertificateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::role('student')->inRandomOrder()->first()->id,
            'code' => Str::random(10),
            'course_id' => \App\Models\Course::inRandomOrder()->first()->id,
            'course_batch_id' => \App\Models\CourseBatch::inRandomOrder()->first()->id,
            'course_progress_id' => \App\Models\CourseProgress::inRandomOrder()->first()->id,
        ];
    }
}
