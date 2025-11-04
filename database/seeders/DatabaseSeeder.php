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
            RolePermissionSeeder::class,
            UserSeeder::class,
            AdminPasswordSeeder::class,
            CategorySeeder::class,
            CourseSeeder::class,
            PricingSeeder::class,
            CoursePricingSeeder::class, // Pivot table
            CourseBatchSeeder::class,
            TransactionSeeder::class,
            CourseSectionSeeder::class,
            CourseContentSeeder::class,
            CourseAttachmentSeeder::class,
            CourseVideoSeeder::class,
            QuizSeeder::class,
            QuestionSeeder::class,
            QuestionOptionSeeder::class,
            CourseStudentSeeder::class,
            CourseMentorSeeder::class,
            CourseBenefitSeeder::class,
            QuizAttemptSeeder::class,
            StudentAnswerSeeder::class,
            CourseProgressSeeder::class,
            SertificateSeeder::class,
        ]);
    }
}
