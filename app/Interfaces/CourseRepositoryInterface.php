<?php

namespace App\Interfaces;

use App\Models\Course;
use Illuminate\Support\Collection;

interface CourseRepositoryInterface
{
    public function getAllCoursesList(): Collection;

    public function getPopularCourses(): Collection;

    public function getCourseDetailsBySlug(string $slug): ?Course;
    public function getCourseMateriBySlug(string $slug): ?Course;

    public function getCoursesByCategorySlug(string $categorySlug): Collection;

    public function getMyCourses(int $userId): Collection;

    public function searchCourses(string $query): Collection;

    public function createCourse(array $data): Course;

    public function find(int $id): ?Course;

    public function update(Course $course, array $data): Course;

    public function delete(Course $course): bool;

    // Return total number of courses
    public function getCourseCount(): int;
}
