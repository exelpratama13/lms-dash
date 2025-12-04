<?php

namespace App\Repositories;

use App\Interfaces\CertificateRepositoryInterface;
use App\Models\Sertificate; // Assuming your model is named Sertificate
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CertificateRepository implements CertificateRepositoryInterface
{
    public function getCertificatesByUserId(int $userId): Collection
    {
        return Sertificate::where('user_id', $userId)
            ->with(['course:id,name,slug,thumbnail', 'courseBatch:id,name']) // Eager load course and batch information
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createCertificate(array $data): Sertificate
    {
        // Generate a unique certificate code
        $data['code'] = 'CERT-' . strtoupper(Str::random(10));

        // Create the certificate record
        return Sertificate::create($data);
    }
}
