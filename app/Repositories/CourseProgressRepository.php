<?php

namespace App\Repositories;

use App\Interfaces\CourseProgressRepositoryInterface;
use App\Models\CourseContent;
use App\Models\CourseProgress;
use App\Models\User;

use Illuminate\Support\Facades\Log;

class CourseProgressRepository implements CourseProgressRepositoryInterface
{
    public function markAsComplete($userId, $courseId, $contentId, $batchId)
    {
        Log::info('markAsComplete called with:', compact('userId', 'courseId', 'contentId', 'batchId'));

        // Pengecekan enrollment sekarang ditangani di service layer.
        // Kita bisa langsung menggunakan data yang sudah divalidasi.
        $content = \App\Models\CourseContent::find($contentId);
        if (!$content) {
            throw new \Exception("Course content not found.");
        }
        
        // `course_batch_id` dimasukkan ke dalam array pertama (kondisi pencarian)
        // untuk memastikan keunikan data progress per batch.
        $result = \App\Models\CourseProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'course_id' => $courseId,
                'course_content_id' => $contentId,
                'course_batch_id' => $batchId, 
            ],
            [
                'is_completed' => true,
                'completed_at' => now(),
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
