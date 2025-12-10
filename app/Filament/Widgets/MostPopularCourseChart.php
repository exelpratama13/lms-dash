<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class MostPopularCourseChart extends ChartWidget
{
    protected static ?string $heading = 'Kursus Paling Populer Anda';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 3; // To position it on the dashboard

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        /** @var User $user */
        $user = Auth::user();
        $statsRepository = app(StatsRepositoryInterface::class);

        $popularityData = $statsRepository->getMentorCoursePopularityStats($user, 5);

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa',
                    'data' => $popularityData['data'],
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ],
                ],
            ],
            'labels' => $popularityData['labels'],
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('mentor');
    }
}