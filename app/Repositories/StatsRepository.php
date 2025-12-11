<?php

namespace App\Repositories;

use App\Interfaces\StatsRepositoryInterface;
use App\Models\Course;
use App\Models\Category;
use App\Models\CourseMentor;
use App\Models\CourseStudent;
use App\Models\CourseBenefit;
use App\Models\User;
use App\Models\Transaction;
use App\Models\CourseProgress;
use App\Models\CourseSection;
use App\Models\CourseContent; // Added this line
use App\Models\Sertificate; // Added by Gemini
use App\Models\Quiz; // Added for quiz stats
use App\Models\QuizAttempt; // Added for quiz stats
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsRepository implements StatsRepositoryInterface
{
    public function getCounts(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('stats.counts', 60, function () {
            return [
                'courses' => Course::count(),
                'categories' => Category::count(),
                'students' => CourseStudent::distinct('user_id')->count('user_id'),
                'mentors' => CourseMentor::distinct('user_id')->count('user_id'),
                'benefits' => CourseBenefit::count(),
            ];
        });
    }

    public function getMentorCounts(User $mentor): array
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        $studentCount = CourseStudent::whereIn('course_id', $mentorCourseIds)
            ->distinct('user_id')
            ->count('user_id');

        return [
            'courses' => $mentorCourseIds->count(),
            'students' => $studentCount,
        ];
    }

    public function getFinancialStats(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('stats.financial', 60, function () {
            return [
                'total_revenue' => Transaction::where('is_paid', true)->sum('grand_total_amount'),
                'monthly_revenue' => Transaction::where('is_paid', true)
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->sum('grand_total_amount'),
                'todays_transactions' => Transaction::where('is_paid', true)
                                            ->whereDate('created_at', today())
                                            ->count(),
                'total_transactions' => Transaction::where('is_paid', true)->count(),
            ];
        });
    }

    public function getUserGrowthStats(int $days = 30): array
    {
        return \Illuminate\Support\Facades\Cache::remember("stats.user_growth.{$days}", 60, function () use ($days) {
            $dates = collect(range($days - 1, 0))->map(fn ($day) => Carbon::today()->subDays($day)->format('Y-m-d'));

            $userCounts = \App\Models\User::query()
                ->where('created_at', '>=', Carbon::today()->subDays($days - 1))
                ->groupByRaw('DATE(created_at)')
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->pluck('count', 'date');

            $data = $dates->map(fn ($date) => $userCounts->get($date, 0));
            $labels = $dates->map(fn ($date) => Carbon::parse($date)->format('M d'));

            return [
                'labels' => $labels->toArray(),
                'data' => $data->toArray(),
            ];
        });
    }

    public function getCoursePopularityStats(int $limit = 5): array
    {
        return \Illuminate\Support\Facades\Cache::remember("stats.course_popularity.{$limit}", 60, function () use ($limit) {
            $popularity = CourseStudent::query()
                ->select('course_id', DB::raw('count(distinct user_id) as students_count'))
                ->groupBy('course_id')
                ->orderBy('students_count', 'desc')
                ->limit($limit)
                ->with('course:id,name') // Eager load course name
                ->get();

            $labels = $popularity->pluck('course.name');
            $data = $popularity->pluck('students_count');

            return [
                'labels' => $labels->toArray(),
                'data' => $data->toArray(),
            ];
        });
    }

    public function getMentorPerformanceStats(User $mentor): array
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        $totalProgress = CourseProgress::whereIn('course_id', $mentorCourseIds)
            ->count();

        $completedProgress = CourseProgress::whereIn('course_id', $mentorCourseIds)
            ->where('is_completed', true)
            ->count();

        $averageCompletionRate = ($totalProgress > 0) ? round(($completedProgress / $totalProgress) * 100, 2) : 0;

        return [
            'average_completion_rate' => $averageCompletionRate,
        ];
    }

    public function getStudentProgressDistribution(User $mentor, ?int $courseId = null): array
    {
        $targetCourse = null;
        if ($courseId) {
            $targetCourse = Course::find($courseId);
        }

        if (!$targetCourse) {
            return [
                'labels' => ['0%', '1-25%', '26-50%', '51-75%', '76-99%', '100%'],
                'data' => [0, 0, 0, 0, 0, 0],
            ];
        }

        return \Illuminate\Support\Facades\Cache::remember("stats.student_progress.{$mentor->id}.{$targetCourse->id}", 60, function () use ($targetCourse) {
            // Get total content items for the course
            $totalCourseContents = $targetCourse->sections()->withCount('contents')->get()->sum('contents_count');

            if ($totalCourseContents === 0) {
                return [
                    'labels' => ['0%', '1-25%', '26-50%', '51-75%', '76-99%', '100%'],
                    'data' => [0, 0, 0, 0, 0, 0],
                ];
            }

            $studentsInCourse = CourseStudent::where('course_id', $targetCourse->id)->pluck('user_id');

            $progressDistribution = [
                '0%' => 0,
                '1-25%' => 0,
                '26-50%' => 0,
                '51-75%' => 0,
                '76-99%' => 0,
                '100%' => 0,
            ];

            foreach ($studentsInCourse as $studentId) {
                $completedContents = CourseProgress::where('user_id', $studentId)
                    ->where('course_id', $targetCourse->id)
                    ->where('is_completed', true)
                    ->count();

                $percentage = ($completedContents / $totalCourseContents) * 100;

                if ($percentage == 0) {
                    $progressDistribution['0%']++;
                } elseif ($percentage > 0 && $percentage <= 25) {
                    $progressDistribution['1-25%']++;
                } elseif ($percentage > 25 && $percentage <= 50) {
                    $progressDistribution['26-50%']++;
                } elseif ($percentage > 50 && $percentage <= 75) {
                    $progressDistribution['51-75%']++;
                } elseif ($percentage > 75 && $percentage < 100) {
                    $progressDistribution['76-99%']++;
                } elseif ($percentage == 100) {
                    $progressDistribution['100%']++;
                }
            }

            return [
                'labels' => array_keys($progressDistribution),
                'data' => array_values($progressDistribution),
            ];
        });
    }

    public function getTotalStudentsCompletedCourses(User $mentor): int
    {
        // Get course IDs taught by the mentor
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return 0;
        }

        // Count unique students who have received a certificate for any of these courses
        $completedStudentsCount = Sertificate::whereIn('course_id', $mentorCourseIds)
            ->distinct('user_id')
            ->count('user_id');

        return $completedStudentsCount;
    }

    public function getMostPopularCourse(User $mentor): ?array
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return null;
        }

        $mostPopularCourse = CourseStudent::whereIn('course_id', $mentorCourseIds)
            ->select('course_id', DB::raw('count(distinct user_id) as students_count'))
            ->groupBy('course_id')
            ->orderByDesc('students_count')
            ->with('course:id,name')
            ->first();

        if ($mostPopularCourse) {
            return [
                'name' => $mostPopularCourse->course->name,
                'students_count' => $mostPopularCourse->students_count,
            ];
        }

        return null;
    }

    public function getCurrentlyActiveStudents(User $mentor): int
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return 0;
        }

        // Define "active" as having made any progress in a mentor's course in the last 7 days.
        $activeStudentsCount = CourseProgress::whereIn('course_id', $mentorCourseIds)
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->distinct('user_id')
            ->count('user_id');

        return $activeStudentsCount;
    }

    public function getAverageQuizPassRate(User $mentor): float
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return 0.0;
        }

        // Get course section IDs for the mentor's courses
        $courseSectionIds = CourseSection::whereIn('course_id', $mentorCourseIds)->pluck('id');

        if ($courseSectionIds->isEmpty()) {
            return 0.0;
        }

        // Get course content IDs for those course sections
        $courseContentIds = CourseContent::whereIn('course_section_id', $courseSectionIds)->pluck('id');

        if ($courseContentIds->isEmpty()) {
            return 0.0;
        }

        $quizzes = Quiz::whereIn('course_content_id', $courseContentIds)->get();

        if ($quizzes->isEmpty()) {
            return 0.0;
        }

        $totalPassRates = 0;
        $quizCount = 0;

        foreach ($quizzes as $quiz) {
            $totalAttempts = QuizAttempt::where('quiz_id', $quiz->id)->count();
            $passedAttempts = QuizAttempt::where('quiz_id', $quiz->id)
                                        ->where('passed', true)
                                        ->count();

            if ($totalAttempts > 0) {
                $quizPassRate = ($passedAttempts / $totalAttempts) * 100;
                $totalPassRates += $quizPassRate;
                $quizCount++;
            }
        }

        return ($quizCount > 0) ? round($totalPassRates / $quizCount, 2) : 0.0;
    }

    public function getMentorCoursePopularityStats(User $mentor, int $limit = 5): array
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return [
                'labels' => [],
                'data' => [],
            ];
        }

        $popularity = CourseStudent::query()
            ->whereIn('course_id', $mentorCourseIds) // Filter by mentor's courses
            ->select('course_id', DB::raw('count(distinct user_id) as students_count'))
            ->groupBy('course_id')
            ->orderBy('students_count', 'desc')
            ->limit($limit)
            ->with('course:id,name') // Eager load course name
            ->get();

        $labels = $popularity->pluck('course.name');
        $data = $popularity->pluck('students_count');

        return [
            'labels' => $labels->toArray(),
            'data' => $data->toArray(),
        ];
    }

    public function getDailyActiveStudents(User $mentor, int $days = 7): array
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return [
                'labels' => [],
                'data' => [],
            ];
        }

        $dates = collect(range($days - 1, 0))->map(fn ($day) => Carbon::today()->subDays($day)->format('Y-m-d'));

        $activeStudentsPerDay = CourseProgress::query()
            ->whereIn('course_id', $mentorCourseIds)
            ->where('updated_at', '>=', Carbon::today()->subDays($days - 1))
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('count(distinct user_id) as active_students_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('active_students_count', 'date');

        $data = $dates->map(fn ($date) => $activeStudentsPerDay->get($date, 0));
        $labels = $dates->map(fn ($date) => Carbon::parse($date)->format('M d'));

        return [
            'labels' => $labels->toArray(),
            'data' => $data->toArray(),
        ];
    }

    public function getMentorIssuedCertificatesCount(User $mentor): int
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return 0;
        }

        // Count certificates where the course_id is one of the mentor's courses
        $certificateCount = Sertificate::whereIn('course_id', $mentorCourseIds)
                                    ->count();

        return $certificateCount;
    }

    public function getMentorEnrollmentTrend(User $mentor, int $days = 30): array
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return [
                'labels' => [],
                'data' => [],
            ];
        }

        $dates = collect(range($days - 1, 0))->map(fn ($day) => Carbon::today()->subDays($day)->format('Y-m-d'));

        $enrollmentsPerDay = CourseStudent::query()
            ->whereIn('course_id', $mentorCourseIds)
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(id) as enrollments_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('enrollments_count', 'date');

        $data = $dates->map(fn ($date) => $enrollmentsPerDay->get($date, 0));
        $labels = $dates->map(fn ($date) => Carbon::parse($date)->format('M d'));

        return [
            'labels' => $labels->toArray(),
            'data' => $data->toArray(),
        ];
    }

    public function getTopPerformingStudents(User $mentor, int $limit = 5): \Illuminate\Database\Eloquent\Builder
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return User::query()->whereRaw('1 = 0'); // Return an empty builder if no courses
        }

        $courseSectionIds = CourseSection::whereIn('course_id', $mentorCourseIds)->pluck('id');
        if ($courseSectionIds->isEmpty()) { return User::query()->whereRaw('1 = 0'); }

        $courseContentIds = CourseContent::whereIn('course_section_id', $courseSectionIds)->pluck('id');
        if ($courseContentIds->isEmpty()) { return User::query()->whereRaw('1 = 0'); }

        $quizIds = Quiz::whereIn('course_content_id', $courseContentIds)->pluck('id');
        if ($quizIds->isEmpty()) { return User::query()->whereRaw('1 = 0'); }


        return User::query()
            ->select('users.*')
            ->selectSub(function ($query) use ($quizIds) {
                $query->from('quiz_attempts')
                      ->selectRaw('AVG(score)')
                      ->whereColumn('user_id', 'users.id')
                      ->whereIn('quiz_id', $quizIds);
            }, 'average_score')
            ->whereIn('users.id', function ($query) use ($quizIds) {
                $query->select('user_id')
                      ->from('quiz_attempts')
                      ->whereIn('quiz_id', $quizIds)
                      ->groupBy('user_id')
                      ->havingRaw('AVG(score) IS NOT NULL'); // Ensure there are scores
            })
            ->orderByDesc('average_score')
            ->limit($limit);
    }

    public function getStudentProgressInCourse(User $mentor, int $courseId): \Illuminate\Database\Eloquent\Builder
    {
        // First, check if the mentor actually teaches this course
        $isMentorOfCourse = CourseMentor::where('user_id', $mentor->id)
                                        ->where('course_id', $courseId)
                                        ->exists();

        if (!$isMentorOfCourse) {
            return User::query()->whereRaw('1 = 0'); // Return an empty builder
        }

        // Get total content items for the course
        $totalCourseContents = CourseSection::where('course_id', $courseId)
                                            ->withCount('contents')
                                            ->get()
                                            ->sum('contents_count');

        if ($totalCourseContents === 0) {
            return User::query()->whereRaw('1 = 0'); // Return empty if no content
        }

        $studentsInCourseCount = \App\Models\CourseStudent::where('course_id', $courseId)->count();
        \Illuminate\Support\Facades\Log::info('StatsRepository: studentsInCourseCount', ['course_id' => $courseId, 'student_count' => $studentsInCourseCount]);

        return User::query()
            ->select('users.*')
            ->join('course_students', 'users.id', '=', 'course_students.user_id')
            ->where('course_students.course_id', $courseId)
            ->withCasts(['completion_percentage' => 'float', 'average_quiz_score' => 'float']) // Cast for display
            ->selectSub(function ($query) use ($courseId, $totalCourseContents) {
                $query->from('course_progresses')
                      ->selectRaw('COALESCE((COUNT(CASE WHEN is_completed = 1 THEN 1 END) / ?) * 100, 0)', [$totalCourseContents])
                      ->whereColumn('user_id', 'users.id')
                      ->where('course_id', $courseId);
            }, 'completion_percentage')
            ->selectSub(function ($query) use ($courseId) {
                $q = $query->from('quiz_attempts')
                      ->selectRaw('AVG(score)')
                      ->whereColumn('user_id', 'users.id')
                      ->whereIn('quiz_id', function($subquery) use ($courseId) {
                            $subquery->select('quizzes.id')
                              ->from('quizzes')
                              ->join('course_contents', 'quizzes.course_content_id', '=', 'course_contents.id')
                              ->join('course_sections', 'course_contents.course_section_id', '=', 'course_sections.id')
                              ->where('course_sections.course_id', $courseId);
                      });
                return $q;
            }, 'average_quiz_score')
            ->orderByDesc('completion_percentage');
    }

    public function getCourseContentAccessStats(User $mentor, int $limit = 5, string $orderBy = 'most_accessed'): \Illuminate\Database\Eloquent\Builder
    {
        $mentorCourseIds = CourseMentor::where('user_id', $mentor->id)->pluck('course_id');

        if ($mentorCourseIds->isEmpty()) {
            return CourseContent::query()->whereRaw('1 = 0'); // Return an empty builder
        }

        $courseContentQuery = CourseContent::query()
            ->select('course_contents.id', 'course_contents.name', 'course_contents.course_section_id')
            ->selectSub(function ($query) {
                $query->from('course_progresses') // Corrected table name
                      ->selectRaw('COUNT(*)')
                      ->whereColumn('course_content_id', 'course_contents.id');
            }, 'access_count')
            ->whereIn('course_contents.course_section_id', function ($query) use ($mentorCourseIds) {
                $query->select('id')
                      ->from('course_sections')
                      ->whereIn('course_id', $mentorCourseIds);
            })
            ->with(['courseSection.course:id,name']) // Eager load course name for display
            ->orderBy('access_count', $orderBy === 'most_accessed' ? 'desc' : 'asc')
            ->limit($limit);

        return $courseContentQuery;
    }

    public function getMentorCourseCompletionRates(User $mentor): array
    {
        $mentorCourses = Course::whereHas('mentors', function ($query) use ($mentor) {
            $query->where('user_id', $mentor->id);
        })->get();

        if ($mentorCourses->isEmpty()) {
            return [
                'labels' => [],
                'data' => [],
            ];
        }

        $labels = [];
        $data = [];

        foreach ($mentorCourses as $course) {
            $totalCourseContents = CourseSection::where('course_id', $course->id)
                                                ->withCount('contents')
                                                ->get()
                                                ->sum('contents_count');

            if ($totalCourseContents === 0) {
                $completionRate = 0;
            } else {
                $totalStudentsEnrolled = CourseStudent::where('course_id', $course->id)->count();

                if ($totalStudentsEnrolled === 0) {
                    $completionRate = 0;
                } else {
                    $completedStudentsCount = 0;
                    $studentsInCourse = CourseStudent::where('course_id', $course->id)->pluck('user_id');

                    foreach ($studentsInCourse as $studentId) {
                        $completedContents = CourseProgress::where('user_id', $studentId)
                            ->where('course_id', $course->id)
                            ->where('is_completed', true)
                            ->count();

                        if ($completedContents >= $totalCourseContents) { // Student completed the course
                            $completedStudentsCount++;
                        }
                    }
                    $completionRate = ($completedStudentsCount / $totalStudentsEnrolled) * 100;
                }
            }

            $labels[] = $course->name;
            $data[] = round($completionRate, 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
