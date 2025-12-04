<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\MidtransWebhookServiceInterface;
use Illuminate\Http\Request;
use Midtrans\Notification;

class MidtransWebhookController extends Controller
{
    protected $midtransWebhookService;

    public function __construct(MidtransWebhookServiceInterface $midtransWebhookService)
    {
        $this->midtransWebhookService = $midtransWebhookService;
    }

    public function handle(Request $request)
    {
        // It's recommended to configure Midtrans server key in config/midtrans.php
        // Explicitly set the server key from config before using the library
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        
        // The Notification class constructor will read the request body from 'php://input'
        try {
            $notification = new Notification();
        } catch (\Exception $e) {
            // This can happen if the request body is not valid JSON, etc.
            return response()->json(['message' => 'Failed to process notification: ' . $e->getMessage()], 400);
        }

        $result = $this->midtransWebhookService->handleWebhookNotification($notification);

        return response()->json(['message' => $result['message']], $result['status']);
    }
}
