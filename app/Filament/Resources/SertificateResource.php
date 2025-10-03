<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SertificateResource\Pages;
use App\Models\Sertificate;
use App\Models\CourseProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SertificateResource extends Resource
{
    protected static ?string $model = Sertificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    // protected static ?string $navigationGroup = 'Courses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required(),

                Forms\Components\TextInput::make('code')
                    ->label('Certificate Code')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'name')
                    ->required(),

                Forms\Components\Select::make('course_batch_id')
                    ->label('Course Batch')
                    ->relationship('courseBatch', 'name')
                    ->required(),

                Forms\Components\Select::make('course_progress_id')
                    ->label('Course Progress')
                    ->options(
                        CourseProgress::all()->mapWithKeys(function ($progress) {
                            return [
                                $progress->id => "ID: {$progress->id} - {$progress->progress_percentage}%"
                            ];
                        })
                    )
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Certificate Code')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseBatch.name')
                    ->label('Course Batch')
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseProgress.progress_percentage')
                    ->label('Progress (%)')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'name'),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSertificates::route('/'),
            'create' => Pages\CreateSertificate::route('/create'),
            'edit' => Pages\EditSertificate::route('/{record}/edit'),
        ];
    }
}
