<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseProgressResource\Pages;
use App\Models\CourseProgress;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseProgressResource extends Resource
{
    protected static ?string $model = CourseProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Course Progress';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->options(
                        User::role('student')
                            ->pluck('name', 'id')
                    )
                    ->required(),

                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'name')
                    ->required(),

                Forms\Components\Select::make('course_batch_id')
                    ->relationship('courseBatch', 'name')
                    ->required(),

                Forms\Components\Select::make('course_section_id')
                    ->relationship('courseSection', 'name')
                    ->required(),

                Forms\Components\Select::make('course_content_id')
                    ->relationship('courseContent', 'name')
                    ->required(),

                Forms\Components\TextInput::make('progress_percentage')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),


                Forms\Components\Toggle::make('is_completed')
                    ->label('Completed')
                    // ->onColor('success')
                    // ->offColor('danger')
                    ->default(false),

                Forms\Components\DateTimePicker::make('completed_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseBatch.name')
                    ->label('Batch'),

                Tables\Columns\TextColumn::make('courseSection.name')
                    ->label('Section'),

                Tables\Columns\TextColumn::make('courseContent.name')
                    ->label('Content'),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress (%)')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_completed')
                    ->boolean()
                    ->sortable()
                    ->label('Completed'),

                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->label('Completed At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')->relationship('course', 'name'),  
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseProgress::route('/'),
            'create' => Pages\CreateCourseProgress::route('/create'),
            'edit' => Pages\EditCourseProgress::route('/{record}/edit'),
        ];
    }
}
