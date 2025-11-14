<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\QuizAttemptServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuizAttemptController extends Controller
{
    protected $quizAttemptService;

    public function __construct(QuizAttemptServiceInterface $quizAttemptService)
    {
        $this->quizAttemptService = $quizAttemptService;
    }

    public function index(): JsonResponse
    {
        try {
            $quizAttempts = $this->quizAttemptService->getUserQuizAttempts();

            return response()->json([
                'status' => 'success',
                'message' => 'Quiz attempts retrieved successfully',
                'data' => $quizAttempts,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve quiz attempts: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'score' => 'required|integer',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'passed' => 'required|boolean',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.question_option_id' => 'required|exists:question_options,id',
        ]);

        try {
            $quizAttempt = $this->quizAttemptService->createQuizAttempt($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Quiz attempt stored successfully',
                'data' => $quizAttempt,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store quiz attempt: ' . $e->getMessage(),
            ], 500);
        }
    }
}
