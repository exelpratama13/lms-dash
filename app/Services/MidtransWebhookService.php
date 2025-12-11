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
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionReceiptMail;

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
            Log::info('Processing Midtrans webhook', [
                'order_id' => $notification->order_id,
                'transaction_status' => $notification->transaction_status,
                'timestamp' => now()->toDateTimeString(),
            ]);

            $transaction = $this->repository->getTransactionByOrderId($notification->order_id);

            if (!$transaction) {
                Log::warning('Webhook: Transaction not found', ['order_id' => $notification->order_id]);
                return ['status' => 404, 'message' => 'Transaction not found'];
            }

            Log::info('Webhook: Transaction found', [
                'transaction_id' => $transaction->id,
                'current_status' => $transaction->status,
                'order_id' => $notification->order_id,
            ]);

            if ($transaction->status === 'success') {
                Log::info('Webhook: Transaction already processed (idempotency check)', [
                    'transaction_id' => $transaction->id,
                    'order_id' => $notification->order_id,
                ]);
                return ['status' => 200, 'message' => 'Transaction already processed'];
            }

            $status = $this->getNewStatus($notification->transaction_status, $notification->fraud_status);
            $isPaid = in_array($status, ['success']);

            $this->repository->updateTransaction($transaction, [
                'status' => $status,
                'is_paid' => $isPaid,
            ]);

            // If payment is successful, generate receipt and update transaction BEFORE student enrollment
            if ($isPaid) {
                // Generate receipt PDF and update transaction proof
                try {
                    $transaction = $transaction->refresh();
                    if (empty($transaction->proof)) {
                        $pdf = PDF::loadView('receipts.transaction', ['transaction' => $transaction]);
                        $fileName = 'receipts/' . $transaction->booking_trx_id . '.pdf';
                        Storage::disk('public')->put($fileName, $pdf->output());
                        $proofUrl = Storage::disk('public')->url($fileName);
                        $this->repository->updateTransaction($transaction, [
                            'proof' => $proofUrl,
                        ]);
                        // Send receipt email synchronously (idempotent - wrap in try/catch)
                        try {
                            $localPath = Storage::disk('public')->path($fileName);
                            if ($transaction->user && $transaction->user->email) {
                                Mail::to($transaction->user->email)
                                    ->send(new TransactionReceiptMail($transaction, $localPath));
                            } else {
                                Log::warning('Transaction has no user email to send receipt', ['transaction_id' => $transaction->id]);
                            }
                        } catch (Exception $e) {
                            Log::error('Sending receipt email failed for transaction ' . ($transaction->id ?? 'unknown') . ': ' . $e->getMessage(), [
                                'exception' => $e,
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Receipt generation failed for transaction ' . ($transaction->id ?? 'unknown') . ': ' . $e->getMessage(), [
                        'exception' => $e,
                    ]);
                }

                // Now handle student enrollment or update
                if ($transaction->course_batch_id) {
                    $isAlreadyInThisBatch = CourseStudent::where('user_id', $transaction->user_id)
                        ->where('course_batch_id', $transaction->course_batch_id)
                        ->exists();
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
                } else {
                    // On-demand enrollment logic
                    $existingOnDemandEnrollment = CourseStudent::where('user_id', $transaction->user_id)
                        ->where('course_id', $transaction->course_id)
                        ->where('enrollment_type', 'on_demand')
                        ->first();
                    $pricing = $transaction->pricing;

                    // Determine if this is a lifetime access (duration = null or 0)
                    $isLifetimeAccess = !$pricing || !$pricing->duration;

                    if ($existingOnDemandEnrollment) {
                        // Student already has on-demand enrollment for this course
                        if ($isLifetimeAccess) {
                            // Lifetime access: set access_expires_at to null
                            // If already null, do nothing (avoid duplicate webhook issue)
                            if ($existingOnDemandEnrollment->access_expires_at !== null) {
                                $existingOnDemandEnrollment->update([
                                    'pricing_id' => $transaction->pricing_id,
                                    'access_expires_at' => null,
                                    'is_active' => true,
                                ]);
                                Log::info('Webhook: On-demand lifetime access updated (set to null)', [
                                    'user_id' => $transaction->user_id,
                                    'course_id' => $transaction->course_id,
                                ]);
                            } else {
                                // Already has lifetime access (null), skip duplicate creation
                                Log::info('Webhook: User already has lifetime access (null), skipping duplicate creation', [
                                    'user_id' => $transaction->user_id,
                                    'course_id' => $transaction->course_id,
                                ]);
                            }
                        } else {
                            // Timed access: extend expiry date or set new expiry
                            $newExpiryDate = null;
                            if ($pricing && $pricing->duration) {
                                // Calculate new expiry date based on existing expiry or current time
                                if (
                                    $existingOnDemandEnrollment->access_expires_at &&
                                    now()->isBefore($existingOnDemandEnrollment->access_expires_at)
                                ) {
                                    // Existing expiry is in the future, add duration to existing expiry
                                    $newExpiryDate = Carbon::parse($existingOnDemandEnrollment->access_expires_at)
                                        ->addDays($pricing->duration);
                                } else {
                                    // Existing expiry is past or null, add duration from now
                                    $newExpiryDate = now()->addDays($pricing->duration);
                                }
                            }
                            // If pricing has no duration (lifetime), newExpiryDate stays null
                            $existingOnDemandEnrollment->update([
                                'pricing_id' => $transaction->pricing_id,
                                'access_expires_at' => $newExpiryDate,
                                'is_active' => true,
                            ]);
                            Log::info('Webhook: On-demand enrollment updated (timed/lifetime access)', [
                                'user_id' => $transaction->user_id,
                                'course_id' => $transaction->course_id,
                                'new_expiry' => $newExpiryDate,
                                'duration_days' => $pricing->duration,
                                'is_lifetime' => $isLifetimeAccess,
                            ]);
                        }
                    } else {
                        // No existing enrollment, create new one
                        $accessExpiresAt = null;
                        if ($pricing && $pricing->duration) {
                            $accessExpiresAt = now()->addDays($pricing->duration);
                        }
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
                        Log::info('Webhook: On-demand enrollment created', [
                            'user_id' => $transaction->user_id,
                            'course_id' => $transaction->course_id,
                            'access_expires_at' => $accessExpiresAt,
                            'is_lifetime' => $isLifetimeAccess,
                        ]);
                    }
                }
            }

            DB::commit();

            return ['status' => 200, 'message' => 'Notification handled successfully'];
        } catch (Exception $e) {
            DB::rollBack();
            // Log the exception with stack trace for debugging
            Log::error('Midtrans webhook processing failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
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
