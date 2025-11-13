<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\CourseStudent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCourseSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        /** @var Course $course */
        $course = $request->route('course');

        // Jika tidak ada user atau course, lanjutkan saja (mungkin route lain)
        if (!$user || !$course) {
            return $next($request);
        }

        $enrollment = CourseStudent::where('user_id', $user->id)
                                   ->where('course_id', $course->id)
                                   ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Anda tidak terdaftar di kursus ini.'], 403);
        }

        // Jika access_expires_at tidak null dan sudah lewat, tolak akses
        if ($enrollment->access_expires_at && now()->gt($enrollment->access_expires_at)) {
            return response()->json(['message' => 'Akses Anda ke kursus ini telah berakhir.'], 403);
        }

        // Jika access_expires_at null (lifetime) atau masih aktif, izinkan akses
        return $next($request);
    }
}
