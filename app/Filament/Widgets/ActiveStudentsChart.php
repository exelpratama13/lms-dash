<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ActiveStudentsChart extends ChartWidget
{
    protected static ?string $heading = 'Siswa Aktif Harian Anda';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 4;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        /** @var User $user */
        $user = Auth::user();
        $statsRepository = app(StatsRepositoryInterface::class);

        $activeStudentsData = $statsRepository->getDailyActiveStudents($user, 7); // Last 7 days

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa Aktif',
                    'data' => $activeStudentsData['data'],
                    'borderColor' => '#36A2EB',
                    'tension' => 0.4,
                    'fill' => false,
                ],
            ],
            'labels' => $activeStudentsData['labels'],
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('mentor');
    }
}