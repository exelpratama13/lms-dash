<?php

namespace App\Interfaces;

use App\Models\Transaction;

interface MidtransWebhookRepositoryInterface
{
    public function getTransactionByOrderId(string $orderId): ?Transaction;
    public function updateTransaction(Transaction $transaction, array $data): bool;
}
