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

    public function getTransactionsByUserId(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::where('user_id', $userId)
            ->with(['course', 'pricing']) // Eager load relasi course dan pricing jika diperlukan
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function find(string $bookingTrxId, int $userId): ?Transaction
    {
        return Transaction::where('booking_trx_id', $bookingTrxId)
            ->where('user_id', $userId)
            ->with(['course', 'pricing'])
            ->first();
    }
}