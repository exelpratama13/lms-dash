<?php

namespace App\Interfaces;

use App\Models\CourseContent;
use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Collection;

interface CourseSectionRepositoryInterface
{
    public function getSectionsByCourseId(int $courseId): Collection;
    public function findSectionById(int $sectionId): ?CourseSection;
    public function getContentsBySectionId(int $sectionId): Collection;
    public function findContentById(int $contentId): ?CourseContent;
    public function createSection(array $data): CourseSection;
    public function updateSection(CourseSection $section, array $data): CourseSection;
    public function deleteSection(CourseSection $section): bool;
    public function createContent(array $data): CourseContent;
    public function updateContent(CourseContent $content, array $data): CourseContent;
    public function deleteContent(CourseContent $content): bool;
    
}