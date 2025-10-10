<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Interfaces\CourseServiceInterface; // Panggil Service Interface
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    protected $courseService;

    // Dependency Injection: Controller membutuhkan Service
    public function __construct(CourseServiceInterface $courseService)
    {
        $this->courseService = $courseService;
    }

    //  * Mengambil daftar semua Course.
    public function index(): JsonResponse 
    {
        try {
            $courses = $this->courseService->getCourseCatalog();

            return response()->json([
                'status' => 'success',
                'message' => 'Course catalog retrieved successfully',
                'count' => $courses->count(),
                'data' => $courses,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve course catalog: ' . $e->getMessage(),
            ], 500);
        }
    }

    //  * Mengambil daftar Course yang Populer.
    public function getPopularCourses(): JsonResponse
    {
        try {
            // Panggil Service Layer untuk mendapatkan data
            $courses = $this->courseService->getPopularCoursesData();

            return response()->json([
                'status' => 'success',
                'message' => 'Popular courses retrieved successfully',
                'data' => $courses,
            ], 200);

        } catch (\Exception $e) {
            // Handle error logging
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve popular courses: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $slug): JsonResponse // Menggunakan 'show'
    {
        try {
            $course = $this->courseService->getCourseDetail($slug);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Course details retrieved successfully',
                'data' => $course,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve course details: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function courseByCategory(string $categorySlug): JsonResponse // Tambahkan ini
    {
        try {
            $courses = $this->courseService->getCoursesByCategorySlug($categorySlug);
            
            if ($courses->isEmpty()) {
                // Respons 404 jika kategori tidak ada, atau tidak ada course di kategori tersebut
                return response()->json([
                    'status' => 'error',
                    'message' => "No courses found for category: {$categorySlug}",
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => "Courses for category '{$categorySlug}' retrieved successfully",
                'count' => $courses->count(),
                'data' => $courses,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve courses by category: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        try {
            // Data sudah lolos validasi dan otorisasi (di StoreCourseRequest)
            $course = $this->courseService->createNewCourse($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Course created successfully',
                'data' => $course,
            ], 201); // 201 Created

        } catch (\Exception $e) {
            // Log::error($e->getMessage()); // Jika perlu logging
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create course: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateCourseRequest $request, int $id): JsonResponse
    {
        try {
            // Data sudah lolos validasi dan otorisasi
            $course = $this->courseService->updateCourse($id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Course updated successfully',
                'data' => $course,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update course: ' . $e->getMessage(),
            ], 404); // 404 Not Found jika Course tidak ditemukan
        }
    }

    public function destroy(int $id): JsonResponse
    {
        // Otorisasi dilakukan langsung di sini karena tidak ada validasi data yang kompleks
        $request = app(UpdateCourseRequest::class);
        if (!$request->authorize()) {
            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        }
        
        try {
            $this->courseService->deleteCourse($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Course deleted successfully',
            ], 204); // 204 No Content for successful deletion

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete course: ' . $e->getMessage(),
            ], 404); // 404 Not Found
        }
    }
}