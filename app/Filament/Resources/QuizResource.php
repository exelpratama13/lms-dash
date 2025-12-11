<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuizResource extends Resource
{
        protected static ?string $model = Quiz::class;
    protected static ?string $navigationGroup = 'Kuis & Penilaian';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Quiz Title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->options(\App\Models\Course::all()->pluck('name', 'id')->toArray())
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('course_section_id', null)),

                Forms\Components\Select::make('course_section_id')
                    ->label('Section')
                    ->options(function (callable $get) {
                        $course = \App\Models\Course::find($get('course_id'));
                        if (!$course) {
                            return [];
                        }
                        return $course->sections->pluck('name', 'id')->toArray();
                    })
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('course_content_id', null)),

                Forms\Components\Select::make('course_content_id')
                    ->label('Course Content')
                    ->options(function (callable $get) {
                        $section = \App\Models\CourseSection::find($get('course_section_id'));
                        if (!$section) {
                            return [];
                        }
                        return $section->contents->pluck('name', 'id')->toArray();
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Quiz Title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseContent.name')
                    ->label('Course Content')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('courseContent')
                    ->relationship('courseContent', 'name')
                    ->label('Course Content'),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
->paginationPageOptions([5, 10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}

