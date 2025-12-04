<?php

namespace App\Interfaces;

use App\Models\Transaction;

interface NewTransactionRepositoryInterface
{
    public function create(array $data): Transaction;
    public function getLastTransactionCode(): ?string;
    public function generateSequentialTransactionCode(): string;
    public function save(Transaction $transaction): void;
    public function processMidtransTransaction(array $data): Transaction;
}
