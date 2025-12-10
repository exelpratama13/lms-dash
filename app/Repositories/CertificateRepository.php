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

        // If recipient_name not provided, snapshot user's current name
        if (empty($data['recipient_name']) && !empty($data['user_id'])) {
            $user = \App\Models\User::find($data['user_id']);
            if ($user) {
                $data['recipient_name'] = $user->name;
            }
        }

        // Create the certificate record
        return Sertificate::create($data);
    }
}
