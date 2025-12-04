<?php

namespace App\Services;

use App\Interfaces\MidtransWebhookRepositoryInterface;
use App\Interfaces\MidtransWebhookServiceInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\CourseStudent;
use Illuminate\Support\Facades\DB;
use Midtrans\Notification;
use Exception;
use Carbon\Carbon;

class MidtransWebhookService implements MidtransWebhookServiceInterface
{
    protected $repository;
    protected $transactionRepository;

    public function __construct(
        MidtransWebhookRepositoryInterface $repository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->repository = $repository;
        $this->transactionRepository = $transactionRepository;
    }

    public function handleWebhookNotification(Notification $notification): array
    {
        DB::beginTransaction();
        try {
            $transaction = $this->repository->getTransactionByOrderId($notification->order_id);

            if (!$transaction) {
                return ['status' => 404, 'message' => 'Transaction not found'];
            }

            if ($transaction->status === 'success') {
                return ['status' => 200, 'message' => 'Transaction already processed'];
            }

            $status = $this->getNewStatus($notification->transaction_status, $notification->fraud_status);
            $isPaid = in_array($status, ['success']);

            $this->repository->updateTransaction($transaction, [
                'status' => $status,
                'is_paid' => $isPaid,
            ]);

            // If payment is successful, handle student enrollment or update
            if ($isPaid) {
                // Case 1: The new purchase is for a BATCH.
                if ($transaction->course_batch_id) {
                    // Check if the user is already enrolled in this specific batch to prevent duplicates.
                    $isAlreadyInThisBatch = CourseStudent::where('user_id', $transaction->user_id)
                        ->where('course_batch_id', $transaction->course_batch_id)
                        ->exists();

                    // If not already in this specific batch, create a new enrollment record for it.
                    if (!$isAlreadyInThisBatch) {
                        $batch = $transaction->courseBatch;
                        $accessExpiresAt = null;
                        $accessStartsAt = now();
                        if ($batch) {
                            $accessExpiresAt = $batch->end_date;
                            if (now()->isBefore($batch->start_date)) {
                                $accessStartsAt = $batch->start_date;
                            }
                        }
                        $this->transactionRepository->createCourseStudent([
                            'user_id' => $transaction->user_id,
                            'course_id' => $transaction->course_id,
                            'course_batch_id' => $transaction->course_batch_id,
                            'pricing_id' => $transaction->pricing_id,
                            'access_starts_at' => $accessStartsAt,
                            'access_expires_at' => $accessExpiresAt,
                            'enrollment_type' => 'batch',
                            'is_active' => true,
                        ]);
                    }
                    // If user is already in this batch, do nothing.

                // Case 2: The new purchase is ON-DEMAND.
                } else {
                    // Look for an existing on-demand enrollment for this course.
                    $existingOnDemandEnrollment = CourseStudent::where('user_id', $transaction->user_id)
                        ->where('course_id', $transaction->course_id)
                        ->where('enrollment_type', 'on_demand')
                        ->first();

                    $pricing = $transaction->pricing;

                    // If an on-demand enrollment already exists, update (extend) it.
                    if ($existingOnDemandEnrollment && $pricing && $pricing->duration) {
                        // Extend from today or from the future expiry date, whichever is later.
                        $newExpiryDate = (now()->isAfter($existingOnDemandEnrollment->access_expires_at))
                            ? now()->addDays($pricing->duration)
                            : Carbon::parse($existingOnDemandEnrollment->access_expires_at)->addDays($pricing->duration);

                        $existingOnDemandEnrollment->update([
                            'pricing_id' => $transaction->pricing_id,
                            'access_expires_at' => $newExpiryDate,
                            'is_active' => true, // Ensure it's active
                        ]);

                    // If no on-demand enrollment exists, create a new one.
                    } else {
                        $accessExpiresAt = null;
                        if ($pricing && $pricing->duration) {
                            $accessExpiresAt = now()->addDays($pricing->duration);
                        }
        
                        // Create course student record
                        $this->transactionRepository->createCourseStudent([
                            'user_id' => $transaction->user_id,
                            'course_id' => $transaction->course_id,
                            'course_batch_id' => null,
                            'pricing_id' => $transaction->pricing_id,
                            'access_starts_at' => now(),
                            'access_expires_at' => $accessExpiresAt,
                            'enrollment_type' => 'on_demand',
                            'is_active' => true,
                        ]);
                    }
                }
            }

            DB::commit();
            return ['status' => 200, 'message' => 'Notification handled successfully'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => 'Failed to handle notification: ' . $e->getMessage()];
        }
    }

    private function getNewStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                return 'challenge';
            } else if ($fraudStatus == 'accept') {
                return 'success';
            }
        } else if ($transactionStatus == 'settlement') {
            return 'success';
        } else if ($transactionStatus == 'pending') {
            return 'pending';
        } else if ($transactionStatus == 'deny') {
            return 'failed';
        } else if ($transactionStatus == 'expire') {
            return 'expired';
        } else if ($transactionStatus == 'cancel') {
            return 'cancelled';
        }

        return 'pending'; // Default status
    }
}
