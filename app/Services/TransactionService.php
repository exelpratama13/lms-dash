<?php

namespace App\Services;

use App\Interfaces\CourseRepositoryInterface;
use App\Interfaces\MidtransServiceInterface;
use App\Interfaces\PricingRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Pricing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService implements TransactionServiceInterface
{
    protected $repository;
    protected $pricingRepository;
    protected $courseRepository;
    protected $midtransService;

    public function __construct(
        TransactionRepositoryInterface $repository,
        PricingRepositoryInterface     $pricingRepository,
        CourseRepositoryInterface      $courseRepository,
        MidtransServiceInterface       $midtransService
    )
    {
        $this->repository = $repository;
        $this->pricingRepository = $pricingRepository;
        $this->courseRepository = $courseRepository;
        $this->midtransService = $midtransService;
    }

    public function initiateMidtransPayment(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $pricing = $this->pricingRepository->findById($data['pricing_id']);
            $course = $this->courseRepository->find($data['course_id']);
            $user = User::find($data['user_id']);

            if (!$user) {
                throw new \Exception("User not found");
            }
            if (!$pricing) {
                throw new \Exception("Pricing not found");
            }

            // New logic: Check if the course is free
            if ((float)$pricing->price === 0.0) {
                return $this->handleFreeEnrollment($user, $course, $pricing, $data);
            }

            // Existing logic for paid courses
            return $this->handlePaidTransaction($user, $course, $pricing, $data);
        });
    }

    private function handleFreeEnrollment(User $user, Course $course, Pricing $pricing, array $data): Transaction
    {
        $courseBatchId = $data['course_batch_id'] ?? null;
        $newTrxCode = $this->repository->generateSequentialTransactionCode();
        $bookingTrxId = (string)Str::uuid();

        // 1. Create the Transaction record for the free course
        $transaction = $this->repository->createTransaction([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'pricing_id' => $pricing->id,
            'course_batch_id' => $courseBatchId,
            'sub_total_amount' => 0,
            'grand_total_amount' => 0,
            'total_tax_amount' => 0,
            'payment_type' => 'free',
            'transaction_code' => $newTrxCode,
            'status' => 'success',
            'is_paid' => true,
            'booking_trx_id' => $bookingTrxId,
        ]);

        // 2. Create the CourseStudent record (logic copied from MidtransWebhookService)
        $isEnrolled = CourseStudent::where('user_id', $transaction->user_id)
            ->where('course_id', $transaction->course_id)
            ->exists();

        if (!$isEnrolled) {
            $enrollmentType = $transaction->course_batch_id ? 'batch' : 'on_demand';
            $accessExpiresAt = null;
            $accessStartsAt = now();

            if ($enrollmentType === 'batch') {
                $batch = $transaction->courseBatch;
                if ($batch) {
                    $accessExpiresAt = $batch->end_date;
                    if (now()->isBefore($batch->start_date)) {
                        $accessStartsAt = $batch->start_date;
                    }
                }
            } else {
                if ($transaction->pricing && $transaction->pricing->duration) {
                    $accessExpiresAt = now()->addDays($transaction->pricing->duration);
                }
            }

            $this->repository->createCourseStudent([
                'user_id' => $transaction->user_id,
                'course_id' => $transaction->course_id,
                'course_batch_id' => $transaction->course_batch_id,
                'pricing_id' => $transaction->pricing_id,
                'access_starts_at' => $accessStartsAt,
                'access_expires_at' => $accessExpiresAt,
                'enrollment_type' => $enrollmentType,
                'is_active' => true,
            ]);
        }

        return $transaction;
    }

    private function handlePaidTransaction(User $user, Course $course, Pricing $pricing, array $data): Transaction
    {
        $courseBatchId = $data['course_batch_id'] ?? null;

        // Calculate tax and grand total
        $taxAmount = $pricing->price * 0.12;
        $grandTotal = $pricing->price + $taxAmount;

        $newTrxCode = $this->repository->generateSequentialTransactionCode();
        $bookingTrxId = (string)Str::uuid();

        $transaction = $this->repository->createTransaction([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'pricing_id' => $pricing->id,
            'course_batch_id' => $courseBatchId,
            'sub_total_amount' => $pricing->price,
            'grand_total_amount' => $grandTotal,
            'total_tax_amount' => $taxAmount,
            'payment_type' => 'midtrans',
            'transaction_code' => $newTrxCode,
            'is_paid' => false,
            'booking_trx_id' => $bookingTrxId,
        ]);

        $userName = trim($user->name) ?: 'Customer';
        $userEmail = trim($user->email) ?: 'noemail@example.com';
        $courseName = trim($course->name) ?: 'Course'; // Corrected from $course->title to $course->name

        if ($pricing->name) {
            $courseName .= ' - ' . $pricing->name;
        }

        $params = [
            'transaction_details' => [
                'order_id' => $bookingTrxId,
                'gross_amount' => (int)$grandTotal,
            ],
            'customer_details' => [
                'first_name' => $userName,
                'email' => $userEmail,
            ],
            'item_details' => [
                [
                    'id' => (string)$course->id,
                    'price' => (int)$pricing->price,
                    'quantity' => 1,
                    'name' => $courseName,
                ],
                [
                    'id' => 'TAX',
                    'price' => (int)$taxAmount,
                    'quantity' => 1,
                    'name' => 'Pajak 12%',
                ]
            ],
        ];

        $snapToken = $this->midtransService->getSnapToken($params);

        $this->repository->updateTransaction($transaction, ['midtrans_snap_token' => $snapToken]);

        return $transaction->refresh();
    }


    public function createMidtransTransaction(array $data): Transaction
    {
        return $this->initiateMidtransPayment($data);
    }

    public function getMyTransactions(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getTransactionsByUserId($userId);
    }

    public function findTransactionById(string $bookingTrxId, int $userId): ?Transaction
    {
        return $this->repository->find($bookingTrxId, $userId);
    }

    // This method seems to be old and not used by the current API flow, keeping it as is.
    public function storeTransaction(array $data): Transaction
    {
        // Perubahan: Menggunakan DB::transaction untuk locking dan atomicity
        return DB::transaction(function () use ($data) {

            // 1. GENERATE KODE TRANSAKSI MENGGUNAKAN LOGIKA BERURUTAN
            $newTrxCode = $this->repository->generateSequentialTransactionCode();

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
            $pricing = \App\Models\Pricing::find($data['pricing_id']);

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
}
