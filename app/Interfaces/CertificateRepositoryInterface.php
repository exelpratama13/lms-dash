<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface CertificateRepositoryInterface
{
    public function getCertificatesByUserId(int $userId): Collection;
}
