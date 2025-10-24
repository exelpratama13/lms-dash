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
}