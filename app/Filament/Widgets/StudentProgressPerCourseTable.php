<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\User;
use App\Models\CourseStudent;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StudentProgressPerCourseTable extends BaseWidget
{
    protected static ?string $heading = 'Progres Siswa per Kursus';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        /** @var User $mentor */
        $mentor = Auth::user();

        // Get IDs of courses this mentor teaches
        $mentorCourseIds = $mentor->courseMentors()->pluck('course_id');

        // Prepare filter options (only courses taught by this mentor)
        $filterCourseOptions = Course::whereIn('id', $mentorCourseIds)->pluck('name', 'id');

        return $table
            ->query(function (Builder $query) use ($mentorCourseIds) {
                return CourseStudent::query()
                    ->select(
                        'course_students.id',
                        'course_students.user_id',
                        'course_students.course_id',
                        DB::raw('COUNT(DISTINCT cp.course_content_id) as completed_contents_count'),
                        DB::raw('COUNT(DISTINCT cc.id) as total_contents_count'),
                        DB::raw('AVG(qa.score) as average_quiz_score')
                    )
                    ->join('users', 'users.id', '=', 'course_students.user_id')
                    ->join('courses', 'courses.id', '=', 'course_students.course_id')
                    // Join for total content count first
                    ->leftJoin('course_sections as cs', 'cs.course_id', '=', 'course_students.course_id')
                    ->leftJoin('course_contents as cc', 'cc.course_section_id', '=', 'cs.id')
                    // Now, join for completed content count using the content table (cc)
                    ->leftJoin('course_progresses as cp', function ($join) {
                        $join->on('cp.course_content_id', '=', 'cc.id')
                             ->on('cp.user_id', '=', 'course_students.user_id')
                             ->where('cp.is_completed', '=', true);
                    })
                    // Join for quiz scores
                    ->leftJoin('quizzes as q', 'q.course_content_id', '=', 'cc.id')
                    ->leftJoin('quiz_attempts as qa', function ($join) {
                        $join->on('qa.quiz_id', '=', 'q.id')
                             ->on('qa.user_id', '=', 'course_students.user_id');
                    })
                    ->whereIn('course_students.course_id', $mentorCourseIds)
                    ->groupBy('course_students.id', 'course_students.user_id', 'course_students.course_id')
                    ->with(['user', 'course']); // Eager load user and course relationship
            })
            ->columns([
                ImageColumn::make('user.photo') // Assuming 'user' relation exists and has 'photo'
                    ->label('Foto Siswa')
                    ->circular(),
                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('course.name')
                    ->label('Kursus')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('course', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('progress_percentage')
                    ->label('Progres')
                    ->getStateUsing(function ($record) {
                        if ($record->total_contents_count == 0) {
                            return '0.00%';
                        }
                        $percentage = ($record->completed_contents_count / $record->total_contents_count) * 100;
                        return number_format($percentage, 2) . '%';
                    })
                    ->sortable(),
                TextColumn::make('quiz_score')
                    ->label('Nilai Rata-rata Quiz')
                    ->getStateUsing(fn($record) => number_format($record->average_quiz_score ?? 0, 2))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('course_id')
                    ->label('Filter Kursus')
                    ->options($filterCourseOptions)
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && $data['value']) {
                            $query->where('course_students.course_id', $data['value']);
                        }
                        return $query;
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated([5, 10, 15, 20, 25]);
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('mentor');
    }
}