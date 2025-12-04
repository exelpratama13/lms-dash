<?php

namespace App\Interfaces;

interface CourseProgressServiceInterface
{
    public function markAsComplete($userId, $courseId, $contentId, $batchId);
    public function markAsIncomplete($userId, $courseId, $contentId);
}
