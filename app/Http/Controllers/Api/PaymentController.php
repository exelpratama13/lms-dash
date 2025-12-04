<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\TransactionServiceInterface;
use App\Http\Requests\StoreTransactionRequest;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    protected $service;

    public function __construct(TransactionServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Membuat Transaksi baru dan memulai pembayaran Midtrans.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            // Tambahkan user_id dari user yang sedang login
            $validatedData['user_id'] = auth()->id();

            // Untuk transaksi API, selalu gunakan Midtrans
            $validatedData['payment_type'] = 'midtrans';

            $transaction = $this->service->initiateMidtransPayment($validatedData);
            return response()->json([
                'status' => 'success',
                'message' => 'Midtrans payment initiated successfully.',
                'data' => [
                    'snap_token' => $transaction->midtrans_snap_token,
                ],
            ], 201);

        } catch (\Exception $e) {
            // Error 500 jika ada masalah di server/database/service
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate payment: ' . $e->getMessage(),
            ], 500);
        }
    }
}