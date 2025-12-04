<?php

namespace App\Interfaces;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection; // Add this line

interface TransactionServiceInterface
{
    public function storeTransaction(array $data): Transaction;
    public function initiateMidtransPayment(array $data): Transaction;
    public function createMidtransTransaction(array $data): Transaction;
    public function getMyTransactions(int $userId): Collection;
    public function findTransactionById(string $bookingTrxId, int $userId): ?Transaction;
}
