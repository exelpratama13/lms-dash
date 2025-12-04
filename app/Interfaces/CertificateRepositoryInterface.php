<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Sertificate;

interface CertificateRepositoryInterface
{
    public function getCertificatesByUserId(int $userId): Collection;
    public function createCertificate(array $data): Sertificate;
}
