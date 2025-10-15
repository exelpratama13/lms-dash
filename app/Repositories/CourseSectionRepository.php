<?php

namespace App\Repositories;

use App\Interfaces\CourseSectionRepositoryInterface;
use App\Models\CourseContent;
use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Collection;

class CourseSectionRepository implements CourseSectionRepositoryInterface
{
    public function getSectionsByCourseId(int $courseId): Collection
    {
        // 1. Mengambil semua Section di Course, dengan eager loading Content
        return CourseSection::with('contents:id,name,course_section_id')
            ->where('course_id', $courseId)
            ->orderBy('position', 'asc')
            ->get();
    }

    public function findSectionById(int $sectionId): ?CourseSection
    {
        // 2. Mengambil detail Section dengan Course dan semua Content-nya
        return CourseSection::with('course:id,name,slug', 'contents')
            ->find($sectionId);
    }

    public function getContentsBySectionId(int $sectionId): Collection
    {
        // 3. Mengambil semua Content di dalam Section tertentu
        return CourseContent::where('course_section_id', $sectionId)
            ->orderBy('id')
            ->get();
    }

    public function findContentById(int $contentId): ?CourseContent
    {
        // 4. Mengambil detail Content
        // Eager load section agar tahu section mana ia berada
        return CourseContent::with('courseSection')
            ->find($contentId);
    }

    public function createSection(array $data): CourseSection
    {
        return CourseSection::create($data);
    }

    public function updateSection(CourseSection $section, array $data): CourseSection
    {
        $section->update($data);
        return $section;
    }

    public function deleteSection(CourseSection $section): bool
    {
        return $section->delete();
    }

    public function createContent(array $data): CourseContent
    {
        return CourseContent::create($data);
    }
    
    public function updateContent(CourseContent $content, array $data): CourseContent
    {
        $content->update($data);
        return $content;
    }
    
    public function deleteContent(CourseContent $content): bool
    {
        // Melakukan penghapusan fisik
        return $content->delete(); 
    }
}
