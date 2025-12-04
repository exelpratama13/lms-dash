<?php

namespace App\Services;

use App\Interfaces\MidtransServiceInterface;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService implements MidtransServiceInterface
{
    public function __construct()
    {
        $serverKey = config('midtrans.server_key');
        $clientKey = config('midtrans.client_key');

        // Log keys untuk debugging - tampilkan full length
        \Illuminate\Support\Facades\Log::info('Raw server key from config: [' . $serverKey . '] Length: ' . strlen($serverKey));
        \Illuminate\Support\Facades\Log::info('Raw client key from config: [' . $clientKey . '] Length: ' . strlen($clientKey));

        // Trim any whitespace
        $serverKey = trim($serverKey);
        $clientKey = trim($clientKey);

        \Illuminate\Support\Facades\Log::info('After trim server key: [' . $serverKey . '] Length: ' . strlen($serverKey));
        \Illuminate\Support\Facades\Log::info('After trim client key: [' . $clientKey . '] Length: ' . strlen($clientKey));

        Config::$serverKey = $serverKey;
        Config::$clientKey = $clientKey;
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        \Illuminate\Support\Facades\Log::info('Midtrans Config set - Is Production: ' . (config('midtrans.is_production') ? 'true' : 'false'));
    }

    public function getSnapToken(array $params): string
    {
        // Debug: Log current config before making request
        \Illuminate\Support\Facades\Log::info('About to call Snap::getSnapToken with config:', [
            'serverKey' => substr(Config::$serverKey, 0, 20) . '...',
            'clientKey' => substr(Config::$clientKey, 0, 20) . '...',
            'isProduction' => Config::$isProduction,
        ]);

        try {
            $token = Snap::getSnapToken($params);
            \Illuminate\Support\Facades\Log::info('Snap token generated successfully');
            return $token;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Snap::getSnapToken failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Full params sent: ' . json_encode($params));
            throw $e;
        }
    }
}
