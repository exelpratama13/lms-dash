<?php

namespace App\Services;

use App\Interfaces\StatsServiceInterface;
use App\Interfaces\StatsRepositoryInterface;

class StatsService implements StatsServiceInterface
{
    protected $repository;

    public function __construct(StatsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getCounts(): array
    {
        return $this->repository->getCounts();
    }
}
