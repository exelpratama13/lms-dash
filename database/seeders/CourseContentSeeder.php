<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseContent;

class CourseContentSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan sudah ada section sebelum menjalankan seeder ini
        if (\App\Models\CourseSection::count() === 0) {
            $this->command->warn('⚠️  Tidak ada CourseSection ditemukan. Jalankan CourseSectionSeeder terlebih dahulu.');
            return;
        }

        CourseContent::factory()->count(3)->create();
    }
}
