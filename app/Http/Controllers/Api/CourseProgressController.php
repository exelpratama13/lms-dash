<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Course;
use App\Models\CourseContent;
use App\Models\CourseProgress;
use Illuminate\Support\Facades\Auth;

class CourseProgressController extends Controller
{
    public function markAsComplete(Request $request, int $courseId, int $contentId): JsonResponse
    {
        $user = Auth::user();
        $course = Course::find($courseId);
        $content = CourseContent::find($contentId);

        if (!$course || !$content) {
            return response()->json(['message' => 'Course or content not found'], 404);
        }

        // Check if the user is enrolled in the course
        $isEnrolled = $user->courses()->where('course_id', $courseId)->exists();
        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        // Create or update the course progress
        $progress = CourseProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $courseId,
                'course_content_id' => $contentId,
            ],
            [
                'is_completed' => true,
                'completed_at' => now(),
                // The following fields are required but are not available in this context
                // I will set them to the first available values
                'course_batch_id' => $user->enrolledCourses()->where('course_id', $courseId)->first()->course_batch_id,
                'course_section_id' => $content->course_section_id,
            ]
        );

        return response()->json(['message' => 'Content marked as complete'], 200);
    }
}
