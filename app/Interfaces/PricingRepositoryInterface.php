<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface PricingRepositoryInterface
{
    public function getPricingsByCourseId(int $courseId): Collection;
    public function findById(int $id);
}