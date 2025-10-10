<?php

namespace App\Repositories;

use App\Interfaces\MentorRepositoryInterface;
use App\Models\Category;
use App\Models\User;
use App\Models\CourseMentor; // Diperlukan untuk eager loading relasi balik (jika ada)
use Illuminate\Database\Eloquent\Collection;

class MentorRepository implements MentorRepositoryInterface
{

    public function getMentorProfile(int $userId): ?User
    {
        // 1. Mengambil data User (Mentor)
        // 2. Eager Loading relasi 'courseMentors' untuk mendapatkan detail kursus yang diajarnya.
        // 3. Menggunakan 'courseMentors.course' untuk nested eager loading detail Course
        //    (Asumsi relasi 'courseMentors' ada di Model User).

        return User::with([
            'courseMentors.course:id,name,slug,thumbnail', // Asumsi relasi courseMentors ada di Model User
            'courseMentors:id,user_id,job,about,course_id' // Ambil kolom spesifik dari CourseMentor
        ])
            ->where('id', $userId)
            ->first();
    }

    public function getAllMentors(): Collection
    {
        return User::whereHas('roles', function ($query) {
            // Asumsi: Jika Anda menggunakan package Spatie/Permission
            $query->where('name', 'mentor');
        })
            // Eager loading hanya data yang diperlukan untuk list
            ->withCount('courseMentors') // Menghitung berapa banyak course yang diajarkan
            ->orderBy('name')
            ->get(['id', 'name', 'photo', 'title']); // Hanya ambil kolom yang diperlukan
    }

    public function getCoursesByMentor(int $mentorId): Collection
    {
        // Menggunakan CourseMentor untuk menemukan semua entri yang dimiliki mentor
        return CourseMentor::where('user_id', $mentorId)
            // Eager Load detail Course dan Category untuk list yang ringkas
            ->with([
                'course' => function ($query) {
                    $query->with('category:id,name')
                        ->select('id', 'name', 'slug', 'thumbnail', 'category_id', 'is_popular');
                }
            ])
            // Ambil semua entri CourseMentor yang memenuhi filter
            ->get();

        // Catatan: Anda mungkin hanya ingin mengembalikan objek Course saja, 
        // jika iya, gunakan $courseMentors->pluck('course')->filter()
    }

    public function getMentorsByCategorySlug(string $categorySlug): Collection
    {
        // 1. Temukan ID Category dari slug
        $category = Category::where('slug', $categorySlug)->first(['id']);

        if (!$category) {
            return collect(); // Kembalikan koleksi kosong jika kategori tidak ditemukan
        }
        
        // 2. Cari semua Mentor (User) yang memiliki entri di CourseMentor
        //    dimana CourseMentor tersebut terikat pada category_id yang dicari.
        return User::whereHas('courseMentors', function ($query) use ($category) {
                // Filter CourseMentor yang memiliki category_id yang cocok
                $query->where('category_id', $category->id);
            })
            // 3. Eager load CourseMentor dan hitungan course-nya untuk list
            ->withCount('courseMentors')
            ->orderBy('name')
            ->get(['id', 'name', 'photo', 'job', 'about']); // Kolom yang dibutuhkan untuk list
    }
}
