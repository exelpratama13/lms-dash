<?php

namespace App\Filament\Widgets;

use App\Models\CourseStudent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentEnrollmentsTable extends BaseWidget
{
    protected static ?string $heading = 'Pendaftaran Kursus Terbaru';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3; // To position it lower on the dashboard

    public static function canView(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    protected function getTableQuery(): Builder
    {
        return CourseStudent::query()
            ->latest()
            ->limit(5)
            ->with(['user', 'course']);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.name')
                ->label('Nama Siswa')
                ->searchable(),
            Tables\Columns\TextColumn::make('course.name')
                ->label('Nama Kursus')
                ->searchable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Tanggal Daftar')
                ->dateTime()
                ->sortable(),
        ];
    }
}
