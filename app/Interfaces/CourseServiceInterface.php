<?php

namespace App\Interfaces;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

interface CourseServiceInterface
{
    public function getCourseCatalog(): Collection;
    
    public function getPopularCoursesData(): Collection;

    public function getCourseDetail(string $slug): ?Course;

    public function getCoursesByCategorySlug(string $categorySlug): Collection;

    public function createNewCourse(array $data): Course;

     public function updateCourse(int $id, array $data): Course;

    public function deleteCourse(int $id): bool;
}
