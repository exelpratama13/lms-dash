<?php

namespace App\Interfaces;

use Midtrans\Notification;

interface MidtransWebhookServiceInterface
{
    public function handleWebhookNotification(Notification $notification): array;
}
