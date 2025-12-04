<?php

namespace App\Repositories;

use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Transaction;
use App\Models\CourseStudent;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function __construct()
    {
        // Constructor is now empty as dependencies are moved to the service
    }

    public function createTransaction(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function updateTransaction(Transaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    public function createCourseStudent(array $data): CourseStudent
    {
        return CourseStudent::create($data);
    }

    public function getLastTransactionCode(): ?string
    {
        // Ambil kode transaksi terakhir yang dibuat, diurutkan berdasarkan ID turun
        $lastTransaction = Transaction::select('transaction_code')
            ->orderBy('id', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->transaction_code : null;
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

    public function generateSequentialTransactionCode(): string
    {
        $prefix = 'invd#';
        $paddingLength = 3; // Untuk 001, 002, dst.

        // Panggil Repositori untuk mendapatkan kode terakhir
        // Metode ini di asumsikan sudah ada di TransactionRepositoryInterface
        $lastCode = $this->getLastTransactionCode();

        $lastNumber = 0;

        if ($lastCode && strpos($lastCode, $prefix) === 0) {
            $numberPart = substr($lastCode, strlen($prefix));
            $lastNumber = (int) $numberPart;
        }

        $newNumber = $lastNumber + 1;

        // Format angka menjadi string dengan zero padding (misal 5 menjadi '005')
        $newCode = $prefix . str_pad($newNumber, $paddingLength, '0', STR_PAD_LEFT);

        return $newCode;
    }
}
