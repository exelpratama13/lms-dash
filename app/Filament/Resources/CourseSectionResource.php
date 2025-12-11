<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseSectionResource\Pages;
use App\Models\CourseSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseSectionResource extends Resource
{
        protected static ?string $model = CourseSection::class;
        protected static ?string $navigationGroup = 'Manajemen Kursus';
    // protected static ?string $navigationGroup = 'Courses';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Section Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'name')
                    ->label('Course')
                    ->required(),

                Forms\Components\TextInput::make('position')
                    ->label('Order Position')
                    ->numeric()
                    // ->default(1)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Section')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
->paginationPageOptions([5, 10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            // nanti bisa ditambahkan RelationManager untuk SectionContents
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseSections::route('/'),
            'create' => Pages\CreateCourseSection::route('/create'),
            'edit' => Pages\EditCourseSection::route('/{record}/edit'),
        ];
    }
}

