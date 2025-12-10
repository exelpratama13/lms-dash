<?php

namespace App\Filament\Widgets;

use App\Interfaces\StatsRepositoryInterface;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;

class CourseContentAccessTable extends BaseWidget
{
    protected static ?string $heading = 'Konten Kursus Paling Sering Diakses';
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Konten'),
                TextColumn::make('courseSection.course.name')
                    ->label('Nama Kursus'),
                TextColumn::make('access_count')
                    ->label('Jumlah Diakses')
                    ->sortable()
                    ->formatStateUsing(fn (mixed $state) => number_format($state)),
            ])
            ->paginated(false);
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        /** @var User $user */
        $user = Auth::user();
        $statsRepository = app(StatsRepositoryInterface::class);

        return $statsRepository->getCourseContentAccessStats($user, 5, 'most_accessed');
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('mentor');
    }
}
