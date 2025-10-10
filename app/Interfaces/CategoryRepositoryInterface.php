<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function getAllCategories(): Collection;
}