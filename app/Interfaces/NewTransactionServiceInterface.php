<?php

namespace App\Interfaces;

use App\Models\Transaction;

interface NewTransactionServiceInterface
{
    public function createMidtransTransaction(array $data): Transaction;
}
