<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Pricing;

class CoursePricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $pricings = Pricing::all();

        if ($courses->isEmpty() || $pricings->isEmpty()) {
            return;
        }

        foreach ($courses as $course) {
            $course->pricings()->attach(
                $pricings->random(rand(1, $pricings->count()))->pluck('id')->toArray()
            );
        }
    }
}
