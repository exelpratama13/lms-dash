<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseVideoResource\Pages;
use App\Filament\Resources\CourseVideoResource\RelationManagers;
use App\Models\CourseVideo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseVideoResource extends Resource
{
        protected static ?string $model = CourseVideo::class;
        protected static ?string $navigationGroup = 'Manajemen Kursus';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id_youtube')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('course_content_id')
                    ->label('Course Content')
                    ->relationship('courseContent', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_youtube')
                    ->label('YouTube')
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseContent.name')
                    ->label('Course Content')
                    ->sortable(),



            ])
            ->filters([
                Tables\Filters\SelectFilter::make('courseContent')
                    ->label('Content')
                    ->relationship('courseContent', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginationPageOptions([5, 10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseVideos::route('/'),
            'create' => Pages\CreateCourseVideo::route('/create'),
            'edit' => Pages\EditCourseVideo::route('/{record}/edit'),
        ];
    }
}

