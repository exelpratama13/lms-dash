<?php

namespace App\Services;

use App\Interfaces\CourseProgressRepositoryInterface;
use App\Interfaces\CourseProgressServiceInterface;
use App\Models\Course;
use App\Models\CourseContent;
use App\Models\User;

class CourseProgressService implements CourseProgressServiceInterface
{
    protected $courseProgressRepository;

    public function __construct(CourseProgressRepositoryInterface $courseProgressRepository)
    {
        $this->courseProgressRepository = $courseProgressRepository;
    }

    public function markAsComplete($userId, $courseId, $contentId, $batchId)
    {
        try {
            $user = \App\Models\User::find($userId);
            $course = \App\Models\Course::find($courseId);
            $content = \App\Models\CourseContent::find($contentId);

            if (!$course || !$content) {
                return ['message' => 'Course or content not found', 'code' => 404];
            }

            // Pengecekan pendaftaran yang lebih spesifik menggunakan batch_id
            $isEnrolled = \App\Models\CourseStudent::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->where('course_batch_id', $batchId)
                ->exists();

            if (!$isEnrolled) {
                return ['message' => 'You are not enrolled in this specific batch of the course.', 'code' => 403];
            }

            $result = $this->courseProgressRepository->markAsComplete($userId, $courseId, $contentId, $batchId);

            return ['message' => 'Content marked as complete', 'code' => 200, 'data' => $result];
        } catch (\Exception $e) {
            return ['message' => 'Failed to mark content as complete: ' . $e->getMessage(), 'code' => 500];
        }
    }

    public function markAsIncomplete($userId, $courseId, $contentId)
    {
        $user = User::find($userId);
        $course = Course::find($courseId);
        $content = CourseContent::find($contentId);

        if (!$course || !$content) {
            return ['message' => 'Course or content not found', 'code' => 404];
        }

        $isEnrolled = $user->courses()->where('course_id', $courseId)->exists();
        if (!$isEnrolled) {
            return ['message' => 'You are not enrolled in this course', 'code' => 403];
        }

        $this->courseProgressRepository->markAsIncomplete($userId, $courseId, $contentId);

        return ['message' => 'Content marked as incomplete', 'code' => 200];
    }
}
