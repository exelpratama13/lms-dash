<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface CertificateServiceInterface
{
    public function getMyCertificates(int $userId): Collection;
}
