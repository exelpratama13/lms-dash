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

    public function getCourseMateriBySlug(string $slug): ?Course
    {
        $course = Course::with([
            'sections.contents.quiz.questions.options',
            'sections.contents.quiz.quizAttempts.studentAnswers',
            'sections.contents.attachment',
            'sections.contents.video',
        ])
            ->where('slug', $slug)
            ->first();

        if (!$course) {
            return null;
        }

        // If a user is authenticated, attach their completion status to each content
        if (auth('api')->check()) {
            $userId = auth('api')->id();
            $progress = \App\Models\CourseProgress::where('user_id', $userId)
                ->where('course_id', $course->id)
                ->get()
                ->keyBy('course_content_id'); // More robust lookup

            foreach ($course->sections as $section) {
                foreach ($section->contents as $content) {
                    $content->is_completed = $progress->has($content->id);
                }
            }
        }

        return $course;
    }

    public function getAllCoursesList(): Collection
    {
        // Eager load relasi yang diperlukan untuk list: category, dan data mentor
        return Course::with([
                'category:id,name',
                'mentors.user:id,name,photo' // Memuat data mentor (nama dan foto)
            ])
            ->withCount('mentors') // Tetap hitung jumlah mentor
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

    public function getMyCourses(int $userId): Collection
    {
        // Temukan CourseStudent berdasarkan userId
        $enrolledCourses = \App\Models\CourseStudent::where('user_id', $userId)
            ->with([
                'course.category:id,name,slug', // Eager load course dan category-nya
                'course.mentors.user:id,name,photo' // Eager load mentor dari course
            ])
            ->get()
            ->map(function ($enrollment) use ($userId) {
                $course = $enrollment->course;
                if ($course) {
                    // Hitung total konten dalam course
                    $totalContents = $course->contents()->count();

                    // Hitung konten yang sudah diselesaikan oleh user untuk course ini
                    $completedContents = \App\Models\CourseProgress::where('user_id', $userId)
                        ->where('course_id', $course->id)
                        ->where('is_completed', true)
                        ->count();

                    // Hitung persentase
                    $percentage = ($totalContents > 0)
                        ? ($completedContents / $totalContents) * 100
                        : 0;

                    // Tambahkan atribut progress_percentage ke model course
                    $course->setAttribute('progress_percentage', round($percentage));
                }
                return $course;
            })
            ->filter(); // Hapus item null jika ada course yang tidak ditemukan

        return new Collection($enrolledCourses);
    }
}
