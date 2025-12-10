<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class UserRegistrationChart extends ChartWidget
{
    protected static ?string $heading = 'Pertumbuhan Pengguna (30 Hari Terakhir)';
    
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    protected function getData(): array
    {
        $statsRepository = app(StatsRepositoryInterface::class);
        $userGrowth = $statsRepository->getUserGrowthStats(30);

        return [
            'datasets' => [
                [
                    'label' => 'Pengguna Baru',
                    'data' => $userGrowth['data'],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                ],
            ],
            'labels' => $userGrowth['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
