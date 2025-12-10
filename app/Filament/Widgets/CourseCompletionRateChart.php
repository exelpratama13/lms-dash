<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CourseCompletionRateChart extends ChartWidget
{
    protected static ?string $heading = 'Perbandingan Tingkat Penyelesaian Kursus Anda';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 9;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        /** @var User $user */
        $user = Auth::user();
        $statsRepository = app(StatsRepositoryInterface::class);

        $completionRatesData = $statsRepository->getMentorCourseCompletionRates($user);

        return [
            'datasets' => [
                [
                    'label' => 'Persentase Penyelesaian (%)',
                    'data' => $completionRatesData['data'],
                    'backgroundColor' => [
                        '#63FF84', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ],
                ],
            ],
            'labels' => $completionRatesData['labels'],
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('mentor');
    }
}
