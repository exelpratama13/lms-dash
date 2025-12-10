<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class TopPerformingStudentsTable extends BaseWidget
{
    protected static ?string $heading = 'Top 5 Siswa Berkinerja Terbaik';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Nama Siswa'),
                TextColumn::make('average_score')
                    ->label('Rata-rata Nilai Quiz')
                    ->sortable()
                    ->formatStateUsing(fn (mixed $state) => number_format($state, 2)),
            ])
            ->paginated(false);
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        /** @var User $user */
        $user = Auth::user();
        $statsRepository = app(StatsRepositoryInterface::class);

        return $statsRepository->getTopPerformingStudents($user, 5);
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('mentor');
    }
}
