<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface CategoryServiceInterface
{
    public function getAvailableCategories(): Collection;
}