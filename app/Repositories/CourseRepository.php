<?php

namespace App\Repositories;

use App\Interfaces\CourseRepositoryInterface;
use App\Models\Course;
use Illuminate\Support\Collection;

class CourseRepository implements CourseRepositoryInterface
{

    public function getPopularCourses(): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember('courses.popular', 60, function () {
            return Course::with('category:id,name,slug')
                ->where('is_popular', true)
                ->get([
                    'id',
                    'name',
                    'slug',
                    'thumbnail',
                    'category_id',
                    'is_popular'
                ]);
        });
    }

    public function getCourseDetailsBySlug(string $slug): ?Course
    {
        $query = Course::with([
            'category:id,name,slug',
            'mentors.user',
            'benefits:id,course_id,name,description',
            'sections' => function ($query) {
                $query->orderBy('position', 'asc');
            },
            'sections.contents' => function ($query) {
                $query->orderBy('position', 'asc');
            },
            'sections.contents.video',
            'pricings',
            'batches',
        ]);

        if (auth('api')->check()) {
            $userId = auth('api')->id();
            $query->withExists(['students as has_access' => function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where(function ($q) {
                        $q->whereNull('access_expires_at')
                            ->orWhere('access_expires_at', '>', now());
                    });
            }]);
        }

        return $query->where('slug', $slug)->first();
    }

    public function getCourseMateriBySlug(string $slug): ?Course
    {
        $query = Course::with([
            'sections' => function ($query) {
                $query->orderBy('position', 'asc');
            },
            'sections.contents' => function ($query) {
                $query->orderBy('position', 'asc');
            },
            'sections.contents.quiz.questions.options',
            'sections.contents.quiz.quizAttempts.studentAnswers',
            'sections.contents.attachment',
            'sections.contents.video',
        ]);

        if (auth('api')->check()) {
            $userId = auth('api')->id();
            $query->with(['sections.contents.courseProgress' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }]);
        }

        return $query->where('slug', $slug)->first();
    }

    public function getAllCoursesList(): Collection
    {
        $query = Course::with([
            'category:id,name',
            'mentors.user',
            'pricings',
        ])
        ->withCount(['mentors', 'students'])
        ->orderBy('students_count', 'desc');

        if (auth('api')->check()) {
            $userId = auth('api')->id();
            $query->withExists(['students as has_access' => function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where(function ($q) {
                        $q->whereNull('access_expires_at')
                            ->orWhere('access_expires_at', '>', now());
                    });
            }]);
        }

        return $query->get([
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
        // LANGKAH 1: Ambil semua progress & kelompokkan
        $allProgress = \App\Models\CourseProgress::where('user_id', $userId)
            ->where('is_completed', true)
            ->get();
        $progressByBatch = $allProgress->groupBy('course_batch_id');

        // LANGKAH 2: Ambil semua data pendaftaran (enrollment)
        $enrollments = \App\Models\CourseStudent::where('user_id', $userId)
            ->with([
                // Eager-load the course and its necessary nested relationships for each enrollment
                'course' => function ($query) { // Hapus use ($userId) karena tidak lagi diperlukan di sini
                    $query->with([
                        'category:id,name,slug',
                        'mentors.user',
                    ])
                    ->withCount('contents'); // Hapus withCount progress yang salah dari sini
                },
                // Eager-load batch and pricing directly on the enrollment
                'batch', 
                'pricing'
            ])
            ->get();

        // LANGKAH 3: Map hasilnya dan hitung progress dengan data yang benar
        return $enrollments->map(function ($enrollment) use ($progressByBatch, $allProgress) {
            $course = $enrollment->course;
            
            if (!$course) {
                return null;
            }

            $completedCount = 0;
            $enrollmentBatchId = $enrollment->course_batch_id; // Bisa null

            // Logika untuk batch-based courses
            if ($enrollmentBatchId !== null) {
                if ($progressByBatch->has($enrollmentBatchId)) {
                    $completedCount = $progressByBatch->get($enrollmentBatchId)->count();
                }
            }
            // Logika untuk on-demand courses (course_batch_id adalah null)
            else {
                // Untuk on-demand, kita harus memastikan hanya menghitung progress
                // di mana `course_batch_id` juga null.
                $completedCount = $allProgress->where('course_id', $course->id)
                                              ->where('course_batch_id', null)
                                              ->count();
            }

            $percentage = ($course->contents_count > 0)
                ? ($completedCount / $course->contents_count) * 100
                : 0;
            
            // Create a base data array from the course model
            $courseData = $course->toArray();

            // Format mentor data from the nested relationship
            $courseData['mentors'] = $course->mentors->map(function ($courseMentor) {
                // Pastikan relasi user ada untuk menghindari error
                if ($courseMentor->user) {
                    return [
                        'name' => $courseMentor->user->name,
                        'photo' => $courseMentor->user->photo,
                    ];
                }
                return null;
            })->filter()->values(); // filter() untuk menghapus nilai null & values() untuk reset index

            // Unset other relationships that are not needed
            unset($courseData['progress']);
            unset($courseData['contents']);

            $courseData['enrollment_id'] = $enrollment->id;
            $courseData['progress_percentage'] = round($percentage);
            $courseData['access_expires_at'] = $enrollment->access_expires_at;
            $courseData['enrollment_type'] = $enrollment->enrollment_type;
            $courseData['is_active'] = $enrollment->is_active;
            $courseData['is_accessible'] = $enrollment->is_active && ($enrollment->access_expires_at === null || now()->isBefore($enrollment->access_expires_at));

            if ($enrollment->batch) {
                $courseData['batch_info'] = [
                    'id' => $enrollment->batch->id,
                    'name' => $enrollment->batch->name,
                    'start_date' => $enrollment->batch->start_date,
                    'end_date' => $enrollment->batch->end_date,
                ];
            }
            if ($enrollment->pricing) {
                $courseData['pricing_info'] = [
                    'id' => $enrollment->pricing->id,
                    'name' => $enrollment->pricing->name,
                    'duration' => $enrollment->pricing->duration,
                ];
            }

            return $courseData;
        })->filter();
    }

    public function searchCourses(string $query): Collection
    {
        return Course::with([
                'category:id,name',
                'mentors.user',
                'pricings',
            ])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('about', 'like', "%{$query}%");
            })
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
}
