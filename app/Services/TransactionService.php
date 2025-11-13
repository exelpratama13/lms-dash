<?php

namespace App\Services;

use App\Interfaces\TransactionRepositoryInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Models\Pricing;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Pastikan ini ada (sudah Anda tambahkan dari error sebelumnya)

class TransactionService implements TransactionServiceInterface
{
    protected $repository;

    public function __construct(TransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function storeTransaction(array $data): Transaction
    {
        // Perubahan: Menggunakan DB::transaction untuk locking dan atomicity
        return DB::transaction(function () use ($data) {
            
            // 1. GENERATE KODE TRANSAKSI MENGGUNAKAN LOGIKA BERURUTAN
            $newTrxCode = $this->generateSequentialTransactionCode();
            
            // 2. Persiapkan data transaksi
            $transactionData = array_merge($data, [
                // Menggunakan kode baru yang berurutan
                'booking_trx_id' => $newTrxCode, 
                'is_paid' => false, 
                'user_id' => $data['user_id'],
            ]);
            
            // 3. Buat Transaksi
            $transaction = $this->repository->createTransaction($transactionData);

            // --- LOGIKA BARU UNTUK LANGGANAN ---
            // 4. Ambil durasi dari pricing. Asumsi 'pricing_id' ada di $data.
            $pricing = Pricing::find($data['pricing_id']);
            
            $expiresAt = null;
            // Cek jika pricing ditemukan dan memiliki durasi
            if ($pricing && $pricing->duration) {
                $expiresAt = now()->addDays($pricing->duration);
            }

            // 5. Tambahkan User ke CourseStudents dengan data lengkap
            $this->repository->createCourseStudent([
                'user_id' => $data['user_id'],
                'course_id' => $data['course_id'],
                'course_batch_id' => $data['course_batch_id'], // Menyimpan ID batch
                'access_expires_at' => $expiresAt, // Menyimpan tanggal kedaluwarsa
            ]);
            // --- AKHIR LOGIKA BARU ---

            return $transaction;
        });
    }

    protected function generateSequentialTransactionCode(): string
    {
        $prefix = 'invd#';
        $paddingLength = 3; // Untuk 001, 002, dst.

        // Panggil Repositori untuk mendapatkan kode terakhir
        // Metode ini di asumsikan sudah ada di TransactionRepositoryInterface
        $lastCode = $this->repository->getLastTransactionCode();
        
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

    public function getMyTransactions(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getTransactionsByUserId($userId);
    }

    public function findTransactionById(string $bookingTrxId, int $userId): ?Transaction
    {
        return $this->repository->find($bookingTrxId, $userId);
    }
}