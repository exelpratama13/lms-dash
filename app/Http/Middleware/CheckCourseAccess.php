<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\CourseStudent;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCourseAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return $next($request);
        }

        $course = $this->getCourseFromRequest($request);
        if (!$course) {
            return $next($request);
        }

        // Ambil SEMUA pendaftaran untuk kursus ini
        $enrollments = \App\Models\CourseStudent::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->get();

        // Jika tidak terdaftar sama sekali, biarkan controller yang menangani
        if ($enrollments->isEmpty()) {
            return $next($request);
        }

        // Cek apakah ada SATU SAJA pendaftaran yang valid
        $hasValidAccess = $enrollments->contains(function ($enrollment) {
            try {
                // Akses valid jika tidak ada tanggal kedaluwarsa (permanen), ATAU tanggalnya masih di masa depan
                return $enrollment->access_expires_at === null || \Carbon\Carbon::parse($enrollment->access_expires_at)->isFuture();
            } catch (\Exception $e) {
                // Jika ada error parsing, anggap saja tanggal itu tidak valid dan catat di log
                \Illuminate\Support\Facades\Log::error('Error parsing access_expires_at in CheckCourseAccess middleware', [
                    'enrollment_id' => $enrollment->id,
                    'access_expires_at' => $enrollment->access_expires_at,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        });

        // JIKA TIDAK ADA AKSES YANG VALID SAMA SEKALI, baru tolak
        if (!$hasValidAccess) {
            return response()->json(['message' => 'Akses Anda ke kursus ini telah kedaluwarsa.'], 403);
        }

        // Jika sampai di sini, artinya pengguna punya akses. Lanjutkan.
        return $next($request);
    }

    /**
     * Get the course object from the request parameters.
     *
     * @param Request $request
     * @return \App\Models\Course|null
     */
    private function getCourseFromRequest(Request $request): ?Course
    {
        // Priority 1: Route model binding
        if ($request->route('course') instanceof Course) {
            return $request->route('course');
        }

        // Priority 2: Slug parameter
        if ($request->route('slug')) {
            return Course::where('slug', $request->route('slug'))->first();
        }

        // Priority 3: courseId parameter
        if ($request->route('courseId')) {
            return Course::find($request->route('courseId'));
        }

        return null;
    }
}
