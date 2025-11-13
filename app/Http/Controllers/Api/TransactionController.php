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
            
            // Tambahkan user_id dari user yang sedang login
            $validatedData['user_id'] = auth()->id();
            
            $transaction = $this->service->storeTransaction($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction created successfully. Waiting for payment.',
                'data' => $transaction,
            ], 201); // Menggunakan 201 Created

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
}