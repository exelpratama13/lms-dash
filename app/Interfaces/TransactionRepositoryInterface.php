<?php

namespace App\Interfaces;

use App\Models\Transaction;
use App\Models\CourseStudent;
use Illuminate\Database\Eloquent\Collection; // Add this line

interface TransactionRepositoryInterface
{
    public function createTransaction(array $data): Transaction;
    public function getLastTransactionCode(): ?string;
    public function getTransactionsByUserId(int $userId): Collection;
    public function find(string $bookingTrxId, int $userId): ?Transaction;
    public function createCourseStudent(array $data): CourseStudent;
    public function generateSequentialTransactionCode(): string;
    public function updateTransaction(Transaction $transaction, array $data): bool;
}