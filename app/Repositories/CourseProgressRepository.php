<?php

namespace App\Repositories;

use App\Interfaces\CourseProgressRepositoryInterface;
use App\Models\CourseContent;
use App\Models\CourseProgress;
use App\Models\User;

use Illuminate\Support\Facades\Log;

class CourseProgressRepository implements CourseProgressRepositoryInterface
{
    public function markAsComplete($userId, $courseId, $contentId)
    {
        Log::info('markAsComplete called with:', compact('userId', 'courseId', 'contentId'));

        $user = User::find($userId);
        $content = CourseContent::find($contentId);

        // Find the enrollment record to get the batch ID
        $enrollment = \App\Models\CourseStudent::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        Log::info('Enrollment found:', ['enrollment' => $enrollment]);

        if (!$enrollment) {
            // This should ideally be handled in the service layer, but as a safeguard:
            throw new \Exception("User is not enrolled in this course.");
        }

        $result = CourseProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'course_id' => $courseId,
                'course_content_id' => $contentId,
            ],
            [
                'is_completed' => true,
                'completed_at' => now(),
                'course_batch_id' => $enrollment->course_batch_id,
                'course_section_id' => $content->course_section_id,
            ]
        );

        Log::info('updateOrCreate result:', ['result' => $result]);

        return $result;
    }

    public function markAsIncomplete($userId, $courseId, $contentId)
    {
        return CourseProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('course_content_id', $contentId)
            ->delete();
    }
}
