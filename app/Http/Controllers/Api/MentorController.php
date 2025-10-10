<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\MentorServiceInterface;
use Illuminate\Http\JsonResponse;

class MentorController extends Controller
{
    protected $mentorService;

    public function __construct(MentorServiceInterface $mentorService)
    {
        $this->mentorService = $mentorService;
    }


    public function index(): JsonResponse
    {
        try {
            $mentors = $this->mentorService->getMentorList();

            return response()->json([
                'status' => 'success',
                'message' => 'Mentor list retrieved successfully',
                'data' => $mentors,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve mentor list: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Mengambil detail profil publik Mentor berdasarkan user ID.
     */
    public function show(int $userId): JsonResponse
    {
        try {
            $mentor = $this->mentorService->getPublicProfile($userId);

            if (!$mentor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mentor not found or is not active',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Mentor profile retrieved successfully',
                'data' => $mentor,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve mentor profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function coursesTaught(int $mentorId): JsonResponse // Tambahkan ini
    {
        try {
            $courses = $this->mentorService->getCoursesTaughtByMentor($mentorId);

            if ($courses->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No courses found taught by this mentor.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Courses taught by mentor retrieved successfully',
                'count' => $courses->count(),
                'data' => $courses,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve mentor courses: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function mentorsByCategory(string $categorySlug): JsonResponse // Tambahkan ini
    {
        try {
            $mentors = $this->mentorService->getMentorsByCategory($categorySlug);
            
            if ($mentors->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "No mentors found teaching courses in category: {$categorySlug}",
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => "Mentors for category '{$categorySlug}' retrieved successfully",
                'count' => $mentors->count(),
                'data' => $mentors,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve mentors by category: ' . $e->getMessage(),
            ], 500);
        }
    }
}
