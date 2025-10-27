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
        CourseMentor::factory()->count(10)->create();
    }
}
