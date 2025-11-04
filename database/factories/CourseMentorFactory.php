<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseMentor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseMentorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseMentor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'job' => $this->faker->jobTitle,
            'about' => $this->faker->paragraph,
            'foto' => $this->faker->imageUrl(),
        ];
    }
}
