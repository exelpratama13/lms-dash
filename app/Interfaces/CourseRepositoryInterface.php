<?php

namespace App\Interfaces;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepositoryInterface
{
    public function getAllCoursesList(): Collection;

    public function getPopularCourses(): Collection;

    public function getCourseDetailsBySlug(string $slug): ?Course;

    public function getCoursesByCategorySlug(string $categorySlug): Collection;

    public function createCourse(array $data): Course;

    public function find(int $id): ?Course;

    public function update(Course $course, array $data): Course;

    public function delete(Course $course): bool; 


}