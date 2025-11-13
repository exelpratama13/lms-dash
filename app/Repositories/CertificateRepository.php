<?php

namespace App\Repositories;

use App\Interfaces\CertificateRepositoryInterface;
use App\Models\Sertificate; // Assuming your model is named Sertificate
use Illuminate\Database\Eloquent\Collection;

class CertificateRepository implements CertificateRepositoryInterface
{
    public function getCertificatesByUserId(int $userId): Collection
    {
        return Sertificate::where('user_id', $userId)
            ->with('course') // Eager load course information
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
