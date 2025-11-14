<?php

namespace Database\Factories;

use App\Models\CourseContent;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'course_content_id' => CourseContent::factory(),
        ];
    }
}
