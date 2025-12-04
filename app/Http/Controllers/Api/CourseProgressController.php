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
        // Ambil model Course untuk menentukan apakah batch_id diperlukan
        $course = \App\Models\Course::find($courseId);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $rules = [
            // batch_id awalnya nullable, nanti bisa jadi required
            'batch_id' => 'nullable|integer|exists:course_batches,id', 
        ];

        // Tentukan apakah kursus ini memiliki batch aktif.
        // Jika ada, maka batch_id wajib ada.
        $courseHasActiveBatches = \App\Models\CourseBatch::where('course_id', $courseId)
                                                      ->where('end_date', '>', now()) // Hanya batch yang belum kadaluwarsa
                                                      ->exists();

        if ($courseHasActiveBatches) {
            $rules['batch_id'] = 'required|integer|exists:course_batches,id';
        }

        // Lakukan validasi sesuai aturan yang sudah ditentukan secara kondisional
        $validated = $request->validate($rules);
        
        // batchId yang akan diteruskan ke service, bisa null jika tidak required dan tidak diberikan
        $batchId = $validated['batch_id'] ?? null;

        $result = $this->courseProgressService->markAsComplete(Auth::id(), $courseId, $contentId, $batchId);
        $responseData = ['message' => $result['message']];
        if (isset($result['data']) && isset($result['data']->id)) {
            $responseData['id'] = $result['data']->id;
        }
        return response()->json($responseData, $result['code']);
    }

    public function markAsIncomplete(Request $request, int $courseId, int $contentId): JsonResponse
    {
        $result = $this->courseProgressService->markAsIncomplete(Auth::id(), $courseId, $contentId);
        return response()->json(['message' => $result['message']], $result['code']);
    }
}
