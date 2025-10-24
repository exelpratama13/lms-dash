<?php

namespace App\Services;

use App\Interfaces\PricingRepositoryInterface;
use App\Interfaces\PricingServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class PricingService implements PricingServiceInterface
{
    protected $repository;

    public function __construct(PricingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function listPricings(int $courseId): Collection
    {
        return $this->repository->getPricingsByCourseId($courseId);
    }
}