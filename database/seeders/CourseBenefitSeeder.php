<?php

namespace Database\Seeders;

use App\Models\CourseBenefit;
use Illuminate\Database\Seeder;

class CourseBenefitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CourseBenefit::factory()->count(300)->create();
    }
}
