<?php

namespace App\Services;

use App\Interfaces\CourseSectionRepositoryInterface;
use App\Interfaces\CourseSectionServiceInterface;
use App\Models\CourseSection;
use App\Models\CourseContent;
use Illuminate\Database\Eloquent\Collection;

class CourseSectionService implements CourseSectionServiceInterface
{
    protected $repository;

    public function __construct(CourseSectionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function listSections(int $courseId): Collection
    {
        // Logika bisnis: Misalnya, cek apakah course ID valid
        // Jika tidak, repository akan mengembalikan koleksi kosong
        return $this->repository->getSectionsByCourseId($courseId);
    }

    public function getSectionDetail(int $sectionId): CourseSection
    {
        $section = $this->repository->findSectionById($sectionId);

        if (!$section) {
            throw new \Exception("Course Section not found.", 404);
        }
        return $section;
    }

    public function listContents(int $sectionId): Collection
    {
        // Logika bisnis: Cek apakah section ada sebelum mengambil konten
        if (!$this->repository->findSectionById($sectionId)) {
            throw new \Exception("Course Section not found.", 404);
        }

        return $this->repository->getContentsBySectionId($sectionId);
    }

    public function getContentDetail(int $contentId): CourseContent
    {
        $content = $this->repository->findContentById($contentId);

        if (!$content) {
            throw new \Exception("Section Content not found.", 404);
        }

        // Logika otorisasi di sini: apakah user sudah membeli course ini?

        return $content;
    }

    public function createSection(array $data): CourseSection
    {
        return $this->repository->createSection($data);
    }

    public function updateSection(int $sectionId, array $data): CourseSection
    {
        $section = $this->repository->findSectionById($sectionId);
        if (!$section) {
            throw new \Exception("Course Section not found.", 404);
        }
        return $this->repository->updateSection($section, $data);
    }

    public function deleteSection(int $sectionId): bool
    {
        $section = $this->repository->findSectionById($sectionId);

        if (!$section) {
            throw new \Exception("Course Section not found.", 404);
        }

        return $this->repository->deleteSection($section);
    }

    public function createContent(array $data): CourseContent
    {
        return $this->repository->createContent($data);
    }

    public function updateContent(int $contentId, array $data): CourseContent
    {
        $content = $this->repository->findContentById($contentId);
        if (!$content) {
            throw new \Exception("Course Content not found.", 404);
        }
        return $this->repository->updateContent($content, $data);
    }

    public function deleteContent(int $contentId): bool
    {
        $content = $this->repository->findcontentById($contentId);
        if (!$content) {
            throw new \Exception("Course Content not found.", 404);
        }
        return $this->repository->deleteContent($content);
    }
}
