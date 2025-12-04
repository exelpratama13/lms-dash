<?php

namespace App\Repositories;

use App\Interfaces\MidtransWebhookRepositoryInterface;
use App\Models\Transaction;

class MidtransWebhookRepository implements MidtransWebhookRepositoryInterface
{
    public function getTransactionByOrderId(string $orderId): ?Transaction
    {
        return Transaction::with('pricing')->where('booking_trx_id', $orderId)->first();
    }

    public function updateTransaction(Transaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }
}
