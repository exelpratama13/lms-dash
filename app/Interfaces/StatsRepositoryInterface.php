<?php

namespace App\Interfaces;

use App\Models\User;

interface StatsRepositoryInterface
{
    public function getCounts(): array;

    public function getMentorCounts(User $mentor): array;

    public function getFinancialStats(): array;

    public function getUserGrowthStats(int $days = 30): array;

    public function getCoursePopularityStats(int $limit = 5): array;

    public function getMentorPerformanceStats(User $mentor): array;

    public function getStudentProgressDistribution(User $mentor, ?int $courseId = null): array;

    public function getTotalStudentsCompletedCourses(User $mentor): int;

    public function getMostPopularCourse(User $mentor): ?array;

    public function getCurrentlyActiveStudents(User $mentor): int;

    public function getAverageQuizPassRate(User $mentor): float;

    public function getMentorCoursePopularityStats(User $mentor, int $limit = 5): array;

    public function getDailyActiveStudents(User $mentor, int $days = 7): array;

    public function getMentorIssuedCertificatesCount(User $mentor): int;

    public function getMentorEnrollmentTrend(User $mentor, int $days = 30): array;

    public function getTopPerformingStudents(User $mentor, int $limit = 5): \Illuminate\Database\Eloquent\Builder;

    public function getStudentProgressInCourse(User $mentor, int $courseId): \Illuminate\Database\Eloquent\Builder;

    public function getCourseContentAccessStats(User $mentor, int $limit = 5, string $orderBy = 'most_accessed'): \Illuminate\Database\Eloquent\Builder;

    public function getMentorCourseCompletionRates(User $mentor): array;
}
