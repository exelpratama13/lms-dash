<?php

namespace App\Services;

use App\Interfaces\QuizAttemptRepositoryInterface;
use App\Interfaces\QuizAttemptServiceInterface;
use Illuminate\Support\Facades\Auth;

class QuizAttemptService implements QuizAttemptServiceInterface
{
    protected $quizAttemptRepository;

    public function __construct(QuizAttemptRepositoryInterface $quizAttemptRepository)
    {
        $this->quizAttemptRepository = $quizAttemptRepository;
    }

    public function getUserQuizAttempts()
    {
        return $this->quizAttemptRepository->getAllQuizAttempts();
    }

    public function createQuizAttempt(array $data)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $answers = $data['answers'];
            unset($data['answers']);

            $data['user_id'] = Auth::id();
            $quizAttempt = $this->quizAttemptRepository->createQuizAttempt($data);

            foreach ($answers as $answer) {
                $quizAttempt->studentAnswers()->create([
                    'question_id' => $answer['question_id'],
                    'question_option_id' => $answer['question_option_id'],
                ]);
            }

            return $quizAttempt;
        });
    }
}
