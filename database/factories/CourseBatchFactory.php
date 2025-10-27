<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseBatch>
 */
class CourseBatchFactory extends Factory
{
    public function definition(): array
    {
        // Pastikan ada mentor user terlebih dahulu
        $mentor = User::whereHas('roles', function ($q) {
            $q->where('name', 'mentor');
        })->inRandomOrder()->first();

        // Kalau belum ada mentor, buat satu dummy
        if (!$mentor) {
            $mentor = User::factory()->create([
                'name' => 'Mentor ' . $this->faker->firstName(),
                'email' => $this->faker->unique()->safeEmail(),
                'password' => bcrypt('password'),
            ]);
            // Assign role mentor jika pakai Spatie
            if (method_exists($mentor, 'assignRole')) {
                $mentor->assignRole('mentor');
            }
        }

        return [
            'course_id' => Course::inRandomOrder()->value('id') ?? 1,
            'mentor_id' => $mentor->id,
            'name' => 'Batch ' . $this->faker->numberBetween(1, 10),
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+4 months'),
            'quota' => $this->faker->numberBetween(10, 50),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
