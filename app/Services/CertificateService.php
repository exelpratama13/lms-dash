<?php

namespace App\Services;

use App\Interfaces\CertificateRepositoryInterface;
use App\Interfaces\CertificateServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class CertificateService implements CertificateServiceInterface
{
    protected $certificateRepository;

    public function __construct(CertificateRepositoryInterface $certificateRepository)
    {
        $this->certificateRepository = $certificateRepository;
    }

    public function getMyCertificates(int $userId): Collection
    {
        return $this->certificateRepository->getCertificatesByUserId($userId);
    }
}
