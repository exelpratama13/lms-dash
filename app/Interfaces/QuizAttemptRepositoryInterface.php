<?php

namespace App\Interfaces;

interface QuizAttemptRepositoryInterface
{
    public function getAllQuizAttempts();
    public function getQuizAttemptById($id);
    public function createQuizAttempt(array $data);
    public function updateQuizAttempt($id, array $data);
    public function deleteQuizAttempt($id);
}
