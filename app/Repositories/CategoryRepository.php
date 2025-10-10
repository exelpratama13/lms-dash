<?php

namespace App\Repositories;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category; // Asumsikan Anda punya Model Category
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{

    public function getAllCategories(): Collection
    {
        // Mengambil semua kategori. Kita bisa menambahkan hitungan jumlah course per kategori
        return Category::withCount('courses') // Asumsikan relasi courses() ada di Model Category
            ->orderBy('name')
            ->get();
    }
}