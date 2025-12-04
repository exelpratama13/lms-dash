<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\TransactionServiceInterface;
use App\Http\Requests\StoreTransactionRequest;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    protected $service;

    public function __construct(TransactionServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Membuat Transaksi baru.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = auth()->id();

            // The service will now handle both free and paid courses
            $transaction = $this->service->createMidtransTransaction($validatedData);

            // If a snap token exists, it's a paid course requiring payment
            if ($transaction->midtrans_snap_token) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Midtrans payment initiated successfully.',
                    'data' => [
                        'snap_token' => $transaction->midtrans_snap_token,
                        'booking_trx_id' => $transaction->booking_trx_id,
                    ],
                ], 201);
            }

            // If no snap token, it was a free course that was enrolled immediately
            return response()->json([
                'status' => 'success',
                'message' => 'Free course enrolled successfully.',
                'data' => $transaction,
            ], 201);

        } catch (\Exception $e) {
            // Error 500 jika ada masalah di server/database/service
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create transaction: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil daftar transaksi untuk user yang sedang login.
     */
    public function myTransactions(): JsonResponse
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $transactions = $this->service->getMyTransactions($userId);

            return response()->json([
                'status' => 'success',
                'message' => 'My transactions retrieved successfully.',
                'count' => $transactions->count(),
                'data' => $transactions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve my transactions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil detail transaksi berdasarkan booking_trx_id.
     */
    public function show(string $bookingTrxId): JsonResponse
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $transaction = $this->service->findTransactionById($bookingTrxId, $userId);

            if (!$transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction not found or you do not have access to it.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction details retrieved successfully.',
                'data' => $transaction,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve transaction details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Membuat Transaksi Midtrans baru (versi baru).
     */
    public function storeNewMidtransTransaction(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = auth()->id();

            // The service now handles both free and paid courses
            $transaction = $this->service->createMidtransTransaction($validatedData);

            // If a snap token exists, it's a paid course requiring payment
            if ($transaction->midtrans_snap_token) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'New Midtrans payment initiated successfully.',
                    'data' => [
                        'snap_token' => $transaction->midtrans_snap_token,
                    ],
                ], 201);
            }

            // If no snap token, it was a free course that was enrolled immediately
            return response()->json([
                'status' => 'success',
                'message' => 'Free course enrolled successfully.',
                'data' => $transaction,
            ], 201);

        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Illuminate\Support\Facades\Log::error('Transaction creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create new transaction: ' . $e->getMessage(),
            ], 500);
        }
    }
}
