<?php

namespace Database\Seeders;

use App\Models\CourseMentor;
use Illuminate\Database\Seeder;

class CourseMentorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mentors = \App\Models\User::role('mentor')->get();

        foreach ($mentors as $mentor) {
            \App\Models\CourseMentor::factory()->create([
                'user_id' => $mentor->id,
                'course_id' => \App\Models\Course::inRandomOrder()->first()->id,
            ]);
        }
    }
}
