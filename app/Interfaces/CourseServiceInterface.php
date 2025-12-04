<?php

namespace App\Interfaces;

use App\Models\Course;
use Illuminate\Support\Collection;

interface CourseServiceInterface
{
    public function getCourseCatalog(): Collection;

    public function getPopularCoursesData(): Collection;

    public function getCourseDetail(string $slug): ?array;
    public function getCourseMateri(string $slug): ?array;

    public function getCoursesByCategorySlug(string $categorySlug): Collection;

    public function getMyCourses(): Collection;

    public function searchCourses(string $query): Collection;

    public function createNewCourse(array $data): Course;

    public function updateCourse(int $id, array $data): Course;

    public function deleteCourse(int $id): bool;
}
