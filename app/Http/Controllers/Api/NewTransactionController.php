<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\NewTransactionServiceInterface;
use App\Http\Requests\StoreTransactionRequest;
use Illuminate\Http\JsonResponse;

class NewTransactionController extends Controller
{
    protected $newTransactionService;

    public function __construct(NewTransactionServiceInterface $newTransactionService)
    {
        $this->newTransactionService = $newTransactionService;
    }

    /**
     * Membuat Transaksi Midtrans baru (versi terpisah).
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            // Tambahkan user_id dari user yang sedang login
            $validatedData['user_id'] = auth()->id();

            // Untuk transaksi API, selalu gunakan Midtrans
            $validatedData['payment_type'] = 'midtrans';

            $transaction = $this->newTransactionService->createMidtransTransaction($validatedData);
            return response()->json([
                'status' => 'success',
                'message' => 'New separate Midtrans payment initiated successfully.',
                'data' => [
                    'snap_token' => $transaction->midtrans_snap_token,
                ],
            ], 201);

        } catch (\Exception $e) {
            // Error 500 jika ada masalah di server/database/service
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create new separate transaction: ' . $e->getMessage(),
            ], 500);
        }
    }
}
