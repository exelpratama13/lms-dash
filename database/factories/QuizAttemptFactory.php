<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizAttempt>
 */
class QuizAttemptFactory extends Factory
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
            'quiz_id' => \App\Models\Quiz::inRandomOrder()->first()->id,
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'score' => $this->faker->numberBetween(0, 100),
            'passed' => $this->faker->boolean,
        ];
    }
}
