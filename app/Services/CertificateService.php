<?php

namespace App\Services;

use App\Interfaces\CertificateRepositoryInterface;
use App\Interfaces\CertificateServiceInterface;
use App\Models\CourseProgress;
use App\Models\Sertificate;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CertificateService implements CertificateServiceInterface
{
    protected $certificateRepository;

    public function __construct(CertificateRepositoryInterface $certificateRepository)
    {
        $this->certificateRepository = $certificateRepository;
    }

    public function getMyCertificates(int $userId): Collection
    {
        return $this->certificateRepository->getCertificatesByUserId($userId);
    }

    public function createCertificate(array $data): Sertificate
    {
        $userId = Auth::id();
        $courseProgressId = $data['course_progress_id'];

        // 1. Fetch CourseProgress and check ownership
        $progress = CourseProgress::where('id', $courseProgressId)
                                  ->where('user_id', $userId)
                                  ->first();

        if (!$progress) {
            throw new \Exception('The selected course progress is invalid or does not belong to the user.');
        }
        
        $data['user_id'] = $userId;
        $data['course_id'] = $progress->course_id;
        $data['course_batch_id'] = $progress->course_batch_id;


        // 2. Check for uniqueness: Has a certificate already been issued?
        $existingCertificate = Sertificate::where('course_progress_id', $courseProgressId)
                                          ->where('user_id', $userId)
                                          ->exists();

        if ($existingCertificate) {
            throw new \Exception('A certificate has already been issued for this course progress.');
        }

        // 3. Completion Check
        $course = Course::with('contents')->find($progress->course_id);
        if (!$course) {
            throw new \Exception('Associated course not found.');
        }
        $totalContents = $course->contents->count();

        $completedContents = CourseProgress::where('user_id', $userId)
                                           ->where('course_id', $progress->course_id)
                                           ->where('is_completed', true)
                                           ->distinct('course_content_id')
                                           ->count('course_content_id');

        if ($completedContents < $totalContents) {
            throw new \Exception('Course has not been fully completed.');
        }

        return $this->certificateRepository->createCertificate($data);
    }
}

