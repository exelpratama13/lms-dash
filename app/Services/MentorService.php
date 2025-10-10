<?php

namespace App\Services;

use App\Interfaces\MentorRepositoryInterface;
use App\Interfaces\MentorServiceInterface;
use App\Models\User;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection; 

class MentorService implements MentorServiceInterface
{
    protected $mentorRepository;

    public function __construct(MentorRepositoryInterface $mentorRepository)
    {
        $this->mentorRepository = $mentorRepository;
    }

    public function getPublicProfile(int $userId): ?User
    {
        $mentor = $this->mentorRepository->getMentorProfile($userId);

        if (!$mentor) {
            return null;
        }

        return $mentor;
    }

    public function getMentorList(): Collection
    {
        $mentors = $this->mentorRepository->getAllMentors();

        return $mentors;
    }

    public function getCoursesTaughtByMentor(int $mentorId): Collection
    {
        $courseMentors = $this->mentorRepository->getCoursesByMentor($mentorId);

        if ($courseMentors->isEmpty()) {
            return collect();
        }

        // Logika Bisnis: Ambil hanya objek Course dari koleksi CourseMentor
        $courses = $courseMentors->pluck('course')->filter(); 

        return $courses;
    }

    public function getMentorsByCategory(string $categorySlug): Collection
    {
        // Panggil Repository untuk melakukan filtering
        $mentors = $this->mentorRepository->getMentorsByCategorySlug($categorySlug);
        
        // Logika Bisnis: (Opsional, misalnya memastikan hanya mentor aktif)

        return $mentors;
    }
}