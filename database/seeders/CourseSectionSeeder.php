<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseSection;

class CourseSectionSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan sudah ada course terlebih dahulu
        if (\App\Models\Course::count() === 0) {
            $this->command->warn('⚠️  Tidak ada data Course ditemukan. Jalankan CourseSeeder terlebih dahulu.');
            return;
        }

        CourseSection::factory()->count(15)->create();
    }
}
