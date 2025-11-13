<?php

namespace App\Interfaces;

use App\Models\Course;

interface PricingServiceInterface
{
    public function listPricings(int $courseId): ?Course;
}