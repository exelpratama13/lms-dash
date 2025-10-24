<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface PricingServiceInterface
{
    public function listPricings(int $courseId): Collection;
}