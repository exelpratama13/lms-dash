<?php

namespace App\Interfaces;

interface QuizAttemptServiceInterface
{
    public function getUserQuizAttempts();
    public function createQuizAttempt(array $data);
}
