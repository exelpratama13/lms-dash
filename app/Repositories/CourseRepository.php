<?php

namespace App\Repositories;

use App\Interfaces\CourseRepositoryInterface;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository implements CourseRepositoryInterface
{

    public function getPopularCourses(): Collection
    {
        return Course::with('category:id,name,slug')
            ->where('is_popular', true)
            ->get();
    }

    public function getCourseDetailsBySlug(string $slug): ?Course
    {
        // Eager load semua relasi yang dibutuhkan untuk halaman detail course
        return Course::with([
            'category:id,name,slug',
            'mentors.user:id,name,photo', // Nested loading: Mentor dan detail User (Mentor)
            'benefits:id,course_id,name,description',
            'sections.contents', // CourseSection dan SectionContents
            'pricings',
            'batches',
        ])
            ->where('slug', $slug)
            // Tambahkan kondisi untuk memastikan course aktif/dipublikasikan (jika ada)
            // ->where('is_published', true)
            ->first();
    }

    public function getAllCoursesList(): Collection
    {
        // Eager load relasi yang diperlukan untuk list: category, dan hitungan mentor
        return Course::with('category:id,name')
            ->withCount('mentors') // Hitung jumlah mentor per course
            // Urutkan berdasarkan nama atau created_at terbaru
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'thumbnail',
                'about',
                'category_id',
                'is_popular'
            ]);
    }

    public function getCoursesByCategorySlug(string $categorySlug): Collection
    {
        // Temukan ID Category dari slug
        $category = \App\Models\Category::where('slug', $categorySlug)->first(['id']);

        if (!$category) {
            return collect(); // Mengembalikan koleksi kosong jika kategori tidak ditemukan
        }

        // Ambil course berdasarkan category_id yang ditemukan
        return Course::with('category:id,name')
            ->where('category_id', $category->id)
            ->withCount('mentors')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'thumbnail',
                'about',
                'category_id',
                'is_popular'
            ]);
    }

    public function createCourse(array $data): Course
    {
        // Metode create akan mengisikan data, termasuk name (yang akan menghasilkan slug otomatis)
        return Course::create($data);
    }

    public function find(int $id): ?Course
    {
        return Course::find($id); // Memanggil metode find() dari Model Eloquent
    }

    public function update(Course $course, array $data): Course
    {
        // Implementasi untuk mengatasi error update()
        $course->update($data);
        return $course;
    }

    public function delete(Course $course): bool
    {
        return $course->delete();
    }

    public function getCourseCount(): int
    {
        return Course::count();
    }
}
