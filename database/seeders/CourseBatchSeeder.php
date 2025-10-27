<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseBatch;

class CourseBatchSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\CourseBatch::factory()->count(5)->create();
    }
}
