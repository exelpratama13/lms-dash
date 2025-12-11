<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class StudentEnrollmentTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Pendaftaran Siswa Anda';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = '1/2';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        /** @var User $user */
        $user = Auth::user();
        $statsRepository = app(StatsRepositoryInterface::class);

        $enrollmentTrendData = $statsRepository->getMentorEnrollmentTrend($user, 30); // Last 30 days

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pendaftaran',
                    'data' => $enrollmentTrendData['data'],
                    'borderColor' => '#FFCE56',
                    'tension' => 0.4,
                    'fill' => false,
                ],
            ],
            'labels' => $enrollmentTrendData['labels'],
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('mentor');
    }
}