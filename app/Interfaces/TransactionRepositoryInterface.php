<?php

namespace App\Interfaces;

use App\Models\Transaction;
use App\Models\CourseProgress;
use App\Models\CourseStudent;

interface TransactionRepositoryInterface
{
    public function createTransaction(array $data): Transaction;
    public function createCourseStudent(array $data): CourseStudent;
    public function getTransactionsByUserId(int $userId): \Illuminate\Database\Eloquent\Collection;
    public function find(string $bookingTrxId, int $userId): ?Transaction; // Added

    public function getLastTransactionCode(): ?string;
    // public function createCourseProgress(array $data): CourseProgress;
}