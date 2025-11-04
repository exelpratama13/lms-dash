<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentAnswer>
 */
class StudentAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_attempt_id' => \App\Models\QuizAttempt::inRandomOrder()->first()->id,
            'question_id' => \App\Models\Question::inRandomOrder()->first()->id,
            'question_option_id' => \App\Models\QuestionOption::inRandomOrder()->first()->id,
        ];
    }
}
