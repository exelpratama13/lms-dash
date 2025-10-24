<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\CourseSectionServiceInterface;
use App\Http\Requests\CourseStructureRequest;
use App\Http\Requests\CourseContentRequest;
use Illuminate\Http\JsonResponse;

class CourseSectionController extends Controller
{
    protected $service;

    public function __construct(CourseSectionServiceInterface $service)
    {
        $this->service = $service;
    }

    public function listSections(int $courseId): JsonResponse
    {
        try {
            $sections = $this->service->listSections($courseId);

            return response()->json([
                'status' => 'success',
                'data' => $sections,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve sections: ' . $e->getMessage(),
            ], 404);
        }
    }

    public function showSection(int $sectionId): JsonResponse
    {
        try {
            $section = $this->service->getSectionDetail($sectionId);

            return response()->json([
                'status' => 'success',
                'data' => $section,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function listContents(int $sectionId): JsonResponse
    {
        try {
            $contents = $this->service->listContents($sectionId);

            return response()->json([
                'status' => 'success',
                'data' => $contents,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function showContent(int $contentId): JsonResponse
    {
        try {
            $content = $this->service->getContentDetail($contentId);

            return response()->json([
                'status' => 'success',
                'data' => $content,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function storeSection(CourseStructureRequest $request): JsonResponse
    {
        try {
            $section = $this->service->createSection($request->validated());
            return response()->json(['status' => 'success', 'data' => $section], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateSection(CourseStructureRequest $request, int $sectionId): JsonResponse
    {
        try {
            $section = $this->service->updateSection($sectionId, $request->validated());
            return response()->json(['status' => 'success', 'data' => $section]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function destroySection(int $sectionId): JsonResponse
    {
        if (!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'mentor'])) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        try {
            $this->service->deleteSection($sectionId);
            return response()->json(['status' => 'success', 'message' => 'Section deleted.'], 204);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function storeContent(CourseContentRequest $request): JsonResponse
    {
        try {
            $content = $this->service->createContent($request->validated());
            return response()->json(['status' => 'success', 'data' => $content], 201);
            
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal membuat konten: ' . $e->getMessage()], 500);
        }
    }

    public function updateContent(CourseContentRequest $request, int $contentId): JsonResponse
    {
        try {
            $content = $this->service->updateContent($contentId, $request->validated());
            return response()->json(['status' => 'success', 'data' => $content]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() == 404 ? 404 : 500;
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }

    public function destroyContent(int $contentId): JsonResponse
    {
        if (!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'mentor'])) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        try {
            $this->service->deleteContent($contentId);
            return response()->json(['status' => 'success', 'message' => 'Content deleted successfully'], 204);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() == 404 ? 404 : 500;
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $statusCode);
        }
    }
}
