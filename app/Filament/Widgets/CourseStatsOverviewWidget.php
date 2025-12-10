<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CourseStatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        /** @var User $user */
        $user = Auth::user();
        $statsRepository = app(StatsRepositoryInterface::class);

        if ($user->hasRole('admin')) {
            $stats = $statsRepository->getCounts();
            return [
                Stat::make('Total Kursus', $stats['courses'])
                    ->description('Jumlah semua kursus yang ada')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('success'),
                Stat::make('Total Siswa', $stats['students'])
                    ->description('Jumlah semua siswa yang terdaftar')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('info'),
                Stat::make('Total Mentor', $stats['mentors'])
                    ->description('Jumlah semua mentor yang ada')
                    ->descriptionIcon('heroicon-m-user-circle')
                    ->color('primary'),
            ];
        }

        if ($user->hasRole('mentor')) {
            $stats = $statsRepository->getMentorCounts($user);
            $performanceStats = $statsRepository->getMentorPerformanceStats($user);
            $averageQuizPassRate = $statsRepository->getAverageQuizPassRate($user);
            $mentorIssuedCertificatesCount = $statsRepository->getMentorIssuedCertificatesCount($user);

            return [
                Stat::make('Total Kursus Anda', $stats['courses'])
                    ->description('Jumlah kursus yang Anda ajar')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('success'),
                Stat::make('Total Siswa Anda', $stats['students'])
                    ->description('Jumlah siswa di semua kursus Anda')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('info'),
                Stat::make('Tingkat Penyelesaian Rata-rata', $performanceStats['average_completion_rate'] . '%')
                    ->description('Rata-rata penyelesaian kursus Anda')
                    ->descriptionIcon('heroicon-m-chart-bar-square')
                    ->color('primary'),
                Stat::make('Jumlah Siswa Lulus', $statsRepository->getTotalStudentsCompletedCourses($user))
                    ->description('Jumlah siswa unik yang telah menyelesaikan kursus Anda')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),
                Stat::make('Tingkat Kelulusan Quiz Rata-rata', $averageQuizPassRate . '%')
                    ->description('Rata-rata kelulusan quiz di kursus Anda')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('info'),
                Stat::make('Jumlah Sertifikat Dikeluarkan', $mentorIssuedCertificatesCount)
                    ->description('Total sertifikat yang telah Anda keluarkan')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('warning'),
            ];
        }

        return [];
    }
}
