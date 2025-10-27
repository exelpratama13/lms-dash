<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseBenefit;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseBenefitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseBenefit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'course_id' => Course::factory(),
        ];
    }
}
