<?php

namespace App\Repositories;

use App\Interfaces\QuizAttemptRepositoryInterface;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;

class QuizAttemptRepository implements QuizAttemptRepositoryInterface
{
    public function getAllQuizAttempts()
    {
        return QuizAttempt::where('user_id', Auth::id())
            ->with(['studentAnswers.questionOption'])
            ->get();
    }

    public function getQuizAttemptById($id)
    {
        return QuizAttempt::where('user_id', Auth::id())->findOrFail($id);
    }

    public function createQuizAttempt(array $data)
    {
        return QuizAttempt::create($data);
    }

    public function updateQuizAttempt($id, array $data)
    {
        $quizAttempt = $this->getQuizAttemptById($id);
        $quizAttempt->update($data);
        return $quizAttempt;
    }

    public function deleteQuizAttempt($id)
    {
        $quizAttempt = $this->getQuizAttemptById($id);
        return $quizAttempt->delete();
    }
}
