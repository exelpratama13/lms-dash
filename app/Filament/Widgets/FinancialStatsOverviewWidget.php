<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class FinancialStatsOverviewWidget extends BaseWidget
{
    // This widget should only be visible to admins
    public static function canView(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    protected function getStats(): array
    {
        $statsRepository = app(StatsRepositoryInterface::class);
        $financialStats = $statsRepository->getFinancialStats();

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($financialStats['total_revenue'], 0, ',', '.'))
                ->description('Pendapatan keseluruhan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($financialStats['monthly_revenue'], 0, ',', '.'))
                ->description('Pendapatan di bulan berjalan')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            Stat::make('Total Transaksi', $financialStats['total_transactions'])
                ->description('Jumlah total transaksi lunas')
                ->descriptionIcon('heroicon-m-receipt-refund')
                ->color('warning'),
            Stat::make('Transaksi Hari Ini', $financialStats['todays_transactions'] . 'x')
                ->description('Jumlah transaksi yang lunas hari ini')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('primary'),
        ];
    }
}
