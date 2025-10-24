<?php

namespace App\Interfaces;

use App\Models\Transaction;

interface TransactionServiceInterface
{
    public function storeTransaction(array $data): Transaction;
}