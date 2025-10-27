<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RolePermissionSeeder::class,
            CategorySeeder::class,
            CourseSeeder::class,
            PricingSeeder::class,
            CourseBatchSeeder::class,
            TransactionSeeder::class,
            CourseSectionSeeder::class,
            CourseContentSeeder::class,
            QuizSeeder::class,
            CourseStudentSeeder::class,
            CourseMentorSeeder::class,
            CourseBenefitSeeder::class,
        ]);
    }
}
