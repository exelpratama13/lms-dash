<?php

namespace App\Services;

use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\CategoryServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryService implements CategoryServiceInterface
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAvailableCategories(): Collection
    {
        $categories = $this->categoryRepository->getAllCategories();

        // Contoh Logika Bisnis: Hanya kembalikan kategori yang memiliki setidaknya 1 course
        return $categories;
    }
}