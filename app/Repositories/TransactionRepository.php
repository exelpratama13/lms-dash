<?php

namespace App\Repositories;

use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Transaction; // Asumsi model Transaction, CourseStudent, CourseProgress sudah ada
use App\Models\CourseStudent;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function createTransaction(array $data): Transaction
    {
        return Transaction::create($data);
    }
    
    public function createCourseStudent(array $data): CourseStudent
    {
        return CourseStudent::create($data);
    }

    public function getLastTransactionCode(): ?string
    {
        // Ambil kode transaksi terakhir yang dibuat, diurutkan berdasarkan ID turun
        $lastTransaction = Transaction::select('booking_trx_id')
                            ->orderBy('id', 'desc')
                            ->first();

        return $lastTransaction ? $lastTransaction->booking_trx_id : null;
    }
    
    
}