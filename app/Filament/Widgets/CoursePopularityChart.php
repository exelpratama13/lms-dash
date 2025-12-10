<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CoursePopularityChart extends ChartWidget
{
    protected static ?string $heading = '5 Kursus Terpopuler (Berdasarkan Jumlah Siswa)';

    public static function canView(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    protected function getData(): array
    {
        $statsRepository = app(StatsRepositoryInterface::class);
        $coursePopularity = $statsRepository->getCoursePopularityStats(5);

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa',
                    'data' => $coursePopularity['data'],
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $coursePopularity['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
