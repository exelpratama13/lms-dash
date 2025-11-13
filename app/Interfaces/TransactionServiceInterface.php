<?php

namespace App\Interfaces;

use App\Models\Transaction;

interface TransactionServiceInterface
{
    public function storeTransaction(array $data): Transaction;
    public function getMyTransactions(int $userId): \Illuminate\Database\Eloquent\Collection;
    public function findTransactionById(string $bookingTrxId, int $userId): ?Transaction;
}