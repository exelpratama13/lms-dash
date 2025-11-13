<?php

namespace App\Services;

use App\Interfaces\PricingServiceInterface;
use App\Models\Course;

class PricingService implements PricingServiceInterface
{
    // Kita tidak lagi memerlukan repository di sini untuk logika ini
    // public function __construct(PricingRepositoryInterface $repository)
    // {
    //     $this->repository = $repository;
    // }

    public function listPricings(int $courseId): ?Course
    {
        // Mengambil data Course dan memuat relasi pricings dan batches
        // sekaligus menghitung jumlah siswa per batch
        return Course::with([
            'pricings',
            'batches' => fn($query) => $query->withCount('students')
        ])->find($courseId);
    }
}