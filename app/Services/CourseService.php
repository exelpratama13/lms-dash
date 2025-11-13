<?php

namespace App\Services;

use App\Interfaces\CourseRepositoryInterface;
use App\Interfaces\CourseServiceInterface;
use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
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

    public function getCourseDetail(string $slug): ?Course
    {
        $course = $this->courseRepository->getCourseDetailsBySlug($slug);

        if (!$course) {
            return null; // Course tidak ditemukan
        }

        // Logika Bisnis: Misal, tambahkan log bahwa course ini dilihat
        // Log::info("Course viewed: {$course->slug}");

        return $course;
    }

    public function getCourseMateri(string $slug): ?Course
    {
        return $this->courseRepository->getCourseMateriBySlug($slug);
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
}
