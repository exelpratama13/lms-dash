<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\CourseProgressServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CourseProgressController extends Controller
{
    protected $courseProgressService;

    public function __construct(CourseProgressServiceInterface $courseProgressService)
    {
        $this->courseProgressService = $courseProgressService;
    }

    public function markAsComplete(Request $request, int $courseId, int $contentId): JsonResponse
    {
        $result = $this->courseProgressService->markAsComplete(Auth::id(), $courseId, $contentId);
        return response()->json(['message' => $result['message']], $result['code']);
    }

    public function markAsIncomplete(Request $request, int $courseId, int $contentId): JsonResponse
    {
        $result = $this->courseProgressService->markAsIncomplete(Auth::id(), $courseId, $contentId);
        return response()->json(['message' => $result['message']], $result['code']);
    }
}
