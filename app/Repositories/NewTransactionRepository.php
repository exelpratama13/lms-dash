<?php

namespace App\Repositories;

use App\Interfaces\NewTransactionRepositoryInterface;
use App\Interfaces\PricingRepositoryInterface;
use App\Interfaces\CourseRepositoryInterface;
use App\Interfaces\MidtransServiceInterface;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\User; // Import User model
use App\Models\CourseStudent; // Import CourseStudent model
use Illuminate\Support\Str; // Import Str for UUID generation

class NewTransactionRepository implements NewTransactionRepositoryInterface
{
    protected $pricingRepository;
    protected $courseRepository;
    protected $midtransService;

    public function __construct(
        PricingRepositoryInterface $pricingRepository,
        CourseRepositoryInterface $courseRepository,
        MidtransServiceInterface $midtransService
    ) {
        $this->pricingRepository = $pricingRepository;
        $this->courseRepository = $courseRepository;
        $this->midtransService = $midtransService;
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function getLastTransactionCode(): ?string
    {
        return Transaction::orderBy('id', 'desc')->value('transaction_code');
    }

    public function generateSequentialTransactionCode(): string
    {
        $prefix = 'invd#';
        $paddingLength = 3;

        $lastCode = $this->getLastTransactionCode();
        $lastNumber = 0;

        if ($lastCode && strpos($lastCode, $prefix) === 0) {
            $numberPart = substr($lastCode, strlen($prefix));
            $lastNumber = (int) $numberPart;
        }

        $newNumber = $lastNumber + 1;
        $newCode = $prefix . str_pad($newNumber, $paddingLength, '0', STR_PAD_LEFT);

        return $newCode;
    }

    public function save(Transaction $transaction): void
    {
        $transaction->save();
    }

    public function processMidtransTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $pricing = $this->pricingRepository->findById($data['pricing_id']);
            $course = $this->courseRepository->find($data['course_id']);
            $user = User::find($data['user_id']);

            if (!$user) {
                throw new \Exception("User not found");
            }

            // Get the first available CourseBatch for the course
            $courseBatch = $course->batches()->first();

            if (!$courseBatch) {
                throw new \Exception("No active course batch found for this course.");
            }

            $newTrxCode = $this->generateSequentialTransactionCode();
            $bookingTrxId = (string) Str::uuid(); // Generate a unique UUID for booking_trx_id

            $transaction = $this->create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'pricing_id' => $pricing->id,
                'course_batch_id' => $courseBatch->id, // Use retrieved batch ID
                'sub_total_amount' => $pricing->price,
                'grand_total_amount' => $pricing->price,
                'total_tax_amount' => 0,
                'payment_type' => 'midtrans',
                'transaction_code' => $newTrxCode,
                'is_paid' => false,
                'booking_trx_id' => $bookingTrxId, // Use the generated UUID
            ]);

            // Always use actual user data from database
            $userName = trim($user->name) ?: 'Customer';
            $userEmail = trim($user->email) ?: 'noemail@example.com';
            $courseName = trim($course->title) ?: 'Course';

            $params = [
                'transaction_details' => [
                    'order_id' => $newTrxCode,
                    'gross_amount' => (int) $pricing->price,
                ],
                'customer_details' => [
                    'first_name' => $userName,
                    'email' => $userEmail,
                ],
                'item_details' => [
                    [
                        'id' => (string) $course->id,
                        'price' => (int) $pricing->price,
                        'quantity' => 1,
                        'name' => $courseName,
                    ]
                ],
            ];

            $snapToken = $this->midtransService->getSnapToken($params);
            $transaction->midtrans_snap_token = $snapToken;
            $this->save($transaction);

            return $transaction;
        });
    }
}
