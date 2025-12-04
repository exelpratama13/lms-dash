<?php

namespace App\Interfaces;

interface CourseProgressRepositoryInterface
{
    public function markAsComplete($userId, $courseId, $contentId, $batchId);
    public function markAsIncomplete($userId, $courseId, $contentId);
}
