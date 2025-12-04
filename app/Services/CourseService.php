<?php

namespace App\Services;

use App\Interfaces\CourseRepositoryInterface;
use App\Interfaces\CourseServiceInterface;
use App\Models\Course;
use App\Models\CourseStudent;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseService implements CourseServiceInterface
{
    protected $courseRepository;

    // Dependency Injection: Service membutuhkan Repository
    public function __construct(CourseRepositoryInterface $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }


    //Mengambil data Course populer melalui Repository.

    public function getPopularCoursesData(): Collection
    {
        // Panggil Repository untuk mengambil data
        $courses = $this->courseRepository->getPopularCourses();

        // Contoh Logika Bisnis Tambahan (Misal: membatasi jumlah hasil)
        // return $courses->take(10);

        return $courses;
    }

    public function getCourseDetail(string $slug): ?array
    {
        $course = $this->courseRepository->getCourseDetailsBySlug($slug);

        if (!$course) {
            return null; // Course not found
        }

        // Eager load active batches and their pricing/mentor, and course pricings
        $course->load(['batches' => function($query) {
            $query->with('pricing', 'mentor')->withCount('students');
        }, 'pricings']);

        $hasActiveBatch = $course->batches->isNotEmpty();

        // Check for authenticated user and get their enrolled batch IDs for this course
        $enrolledBatchIds = collect(); // Default to an empty collection
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            $courseBatchIds = $course->batches->pluck('id');
            
            if ($courseBatchIds->isNotEmpty()) {
                $enrolledBatchIds = CourseStudent::where('user_id', $user->id)
                    ->whereIn('course_batch_id', $courseBatchIds)
                    ->pluck('course_batch_id');
            }
        }

        $response = $course->toArray();
        $response['has_batch'] = $hasActiveBatch;

        // Unset the raw relations to build a clean response
        unset($response['batches']);
        unset($response['pricings']);
        unset($response['course_pricings']);

        if ($hasActiveBatch) {
            // Map all active batches to the response
            $response['batches'] = $course->batches->map(function ($batch) use ($enrolledBatchIds) {
                return [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'start_date' => $batch->start_date->toDateString(),
                    'end_date' => $batch->end_date->toDateString(),
                    'quota' => $batch->quota,
                    'mentor' => $batch->mentor ? ['id' => $batch->mentor->id, 'name' => $batch->mentor->name] : null,
                    'pricing' => $batch->pricing ? [
                        'id' => $batch->pricing->id,
                        'name' => $batch->pricing->name,
                        'price' => $batch->pricing->price,
                        'duration' => $batch->pricing->duration,
                    ] : null,
                    'student_count' => $batch->students_count, // Use the count from withCount
                    'is_available' => now()->isBefore($batch->end_date),
                    'days_remaining' => now()->diffInDays($batch->end_date, false),
                    'terdaftar' => $enrolledBatchIds->contains($batch->id), // Added 'terdaftar' property
                ];
            })->toArray();
        } else {
            // On-demand logic
            $response['pricings'] = $course->pricings->map(function($pricing) {
                return [
                    'id' => $pricing->id,
                    'name' => $pricing->name,
                    'price' => $pricing->price,
                    'duration' => $pricing->duration,
                ];
            })->toArray();
        }

        $firstVideoId = null;
        if ($firstSection = $course->sections->first()) {
            if ($firstContent = $firstSection->contents->first()) {
                if ($firstContent->video) {
                    $firstVideoId = $firstContent->video->id_youtube;
                }
            }
        }

        $response['video'] = $firstVideoId ? 'https://www.youtube.com/watch?v=' . $firstVideoId : null;

        $mentorsDetails = $course->mentors->map(function ($courseMentor) {
            if (!$mentorUser = $courseMentor->user) {
                return null;
            }

            $mentorUser->loadCount('taughtBatches as classes_taught_count');
            $mentorUser->load(['taughtBatches' => fn($q) => $q->withCount('students')]);

            $totalStudents = $mentorUser->taughtBatches->sum('students_count');

            return [
                'id' => $mentorUser->id,
                'name' => $mentorUser->name,
                'photo_url' => $mentorUser->photo_url,
                'job' => $courseMentor->job,
                'about' => $courseMentor->about,
                'classes_taught_count' => $mentorUser->classes_taught_count,
                'total_students_count' => $totalStudents,
            ];
        })->filter()->values();

        $response['mentors'] = $mentorsDetails;

        return $response;
    }

    public function getCourseMateri(string $slug): ?array
    {
        $course = $this->courseRepository->getCourseMateriBySlug($slug);

        if (!$course) {
            return null;
        }

        $courseData = $course->toArray();
        $userId = auth('api')->id();

        if ($userId) {
            $enrollments = \App\Models\CourseStudent::where('user_id', $userId)
                ->where('course_id', $course->id)
                ->with('batch') // Eager load relasi batch
                ->get();

            $bestEnrollment = null;

            foreach ($enrollments as $enrollment) {
                try {
                    // Ambil nilai mentah untuk menghindari error casting otomatis
                    $rawAccessExpiresAt = $enrollment->getRawOriginal('access_expires_at');
                    $rawBatchStartDate = $enrollment->batch ? $enrollment->batch->getRawOriginal('start_date') : null;

                    // --- Check 1: Is access active? ---
                    $isAccessActive = false;
                    if ($rawAccessExpiresAt === null) {
                        $isAccessActive = true; // Permanent access
                    } elseif ($rawAccessExpiresAt) {
                        if (\Carbon\Carbon::parse($rawAccessExpiresAt)->isFuture()) {
                            $isAccessActive = true;
                        }
                    }

                    if (!$isAccessActive) {
                        continue; // Skip to next enrollment if access is not active
                    }

                    // --- Check 2: Is batch running? (only if access is active) ---
                    $isBatchRunning = false;
                    if (!$enrollment->batch) { // On-demand is considered running
                        $isBatchRunning = true;
                    } elseif ($rawBatchStartDate) { // If batch exists and start date exists
                        if (!\Carbon\Carbon::parse($rawBatchStartDate)->isFuture()) { // Batch has started or is today
                            $isBatchRunning = true;
                        }
                    }

                    if ($isBatchRunning) {
                        $bestEnrollment = $enrollment;
                        break; // Found the best option, stop.
                    }

                } catch (\Exception $e) {
                    // Log error if date parsing fails, but don't crash.
                    \Illuminate\Support\Facades\Log::error('Error parsing date in getCourseMateri', [
                        'enrollment_id' => $enrollment->id,
                        'access_expires_at' => $enrollment->access_expires_at, // Use original for logging
                        'batch_start_date' => $enrollment->batch ? $enrollment->batch->start_date : 'N/A', // Use original for logging
                        'error' => $e->getMessage()
                    ]);
                    continue; // Skip this potentially corrupted enrollment
                }
            }
            
            $courseData['active_batch_id'] = $bestEnrollment ? $bestEnrollment->course_batch_id : null;
        }

        return $courseData;
    }

    public function getCourseCatalog(): Collection
    {
        // Logika Bisnis: Di sini Anda bisa menambahkan filtering berdasarkan query string
        // Namun, kita akan memanggil repository untuk mengambil list default
        return $this->courseRepository->getAllCoursesList();
    }

    public function getCoursesByCategorySlug(string $categorySlug): Collection
    {
        // Panggil Repository dengan slug kategori
        $courses = $this->courseRepository->getCoursesByCategorySlug($categorySlug);

        // Logika Bisnis: Misal, bisa melakukan transform data tambahan di sini

        return $courses;
    }

    public function createNewCourse(array $data): Course
    {
        // 1. Logika Bisnis: Hapus thumbnail dari data, karena akan di-upload
        $thumbnailFile = $data['thumbnail'];
        unset($data['thumbnail']);

        // 2. Proses Upload File Thumbnail
        // Asumsi: File di-upload ke disk 'public' di folder 'thumbnails'
        $path = $thumbnailFile->store('thumbnails', 'public');

        // 3. Simpan sebagai URL penuh agar frontend bisa langsung mengaksesnya.
        // Storage::url($path) biasanya mengembalikan '/storage/...' — gunakan url() untuk menambahkan APP_URL
        $data['thumbnail'] = url(\Illuminate\Support\Facades\Storage::url($path));

        // 4. Panggil Repository untuk menyimpan ke database
        return $this->courseRepository->createCourse($data);
    }

    public function updateCourse(int $id, array $data): Course
    {
        $course = $this->courseRepository->find($id);

        if (!$course) {
            throw new \Exception("Course not found.");
        }

        // 1. Handle Thumbnail Update
        if (isset($data['thumbnail'])) {
            // Hapus thumbnail lama
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            // Simpan thumbnail baru
            $path = $data['thumbnail']->store('thumbnails', 'public');
            $data['thumbnail'] = url(\Illuminate\Support\Facades\Storage::url($path));
        }

        // 2. Jika nama diubah, slug akan otomatis diperbarui oleh Mutator di Model Course
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // 3. Simpan perubahan
        return $this->courseRepository->update($course, $data);
    }

    public function deleteCourse(int $id): bool
    {
        $course = $this->courseRepository->find($id);

        if (!$course) {
            throw new \Exception("Course not found.");
        }

        // Hapus thumbnail terkait (opsional, tapi disarankan)
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        // Lakukan penghapusan
        return $this->courseRepository->delete($course);
    }

    public function getMyCourses(): Collection
    {
        // Dapatkan ID pengguna yang terotentikasi
        $userId = auth('api')->id();

        if (!$userId) {
            // Kembalikan koleksi kosong jika tidak ada pengguna yang terotentikasi
            return new Collection();
        }

        // Panggil repository untuk mendapatkan data
        return $this->courseRepository->getMyCourses($userId);
    }

    public function searchCourses(string $query): Collection
    {
        return $this->courseRepository->searchCourses($query);
    }
}
