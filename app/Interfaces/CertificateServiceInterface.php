<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Sertificate;

interface CertificateServiceInterface
{
    public function getMyCertificates(int $userId): Collection;
    public function createCertificate(array $data): Sertificate;
}
