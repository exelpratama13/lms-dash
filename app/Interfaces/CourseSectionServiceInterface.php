<?php

namespace App\Interfaces;

use App\Models\CourseSection;
use App\Models\CourseContent;
use Illuminate\Database\Eloquent\Collection;

interface CourseSectionServiceInterface
{
    public function listSections(int $courseId): Collection;
    public function getSectionDetail(int $sectionId): CourseSection;
    public function listContents(int $sectionId): Collection;
    public function getContentDetail(int $contentId): CourseContent;

    //crud section
    public function createSection(array $data): CourseSection;
    public function updateSection(int $sectionId, array $data): CourseSection;
    public function deleteSection(int $sectionId): bool;
}