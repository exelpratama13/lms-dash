<?php

namespace App\Interfaces;

interface CourseProgressServiceInterface
{
    public function markAsComplete($userId, $courseId, $contentId);
    public function markAsIncomplete($userId, $courseId, $contentId);
}
