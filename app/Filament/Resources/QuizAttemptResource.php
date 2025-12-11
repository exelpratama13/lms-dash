<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizAttemptResource\Pages;
use App\Models\QuizAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class QuizAttemptResource extends Resource
{
        protected static ?string $model = QuizAttempt::class;

    protected static ?string $navigationGroup = 'Kuis & Penilaian';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'mentor']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'mentor']);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'mentor']);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'mentor']);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'mentor']);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),

                Forms\Components\Select::make('quiz_id')
                    ->relationship('quiz', 'title')
                    ->required(),

                Forms\Components\DateTimePicker::make('start_time')
                    ->required(),

                Forms\Components\DateTimePicker::make('end_time'),

                Forms\Components\TextInput::make('score')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('passed')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User'),

                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('Quiz'),

                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('score'),

                Tables\Columns\IconColumn::make('passed')
                    ->boolean()
                    ->label('Passed?'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('quiz_id')->relationship('quiz', 'title'),
                Tables\Filters\SelectFilter::make('user_id')->relationship('user', 'name'),
            ])
            ->filtersFormColumns(2)
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
            // QuizAttemptResource\RelationManagers\StudentAnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizAttempts::route('/'),
            'create' => Pages\CreateQuizAttempt::route('/create'),
            'edit' => Pages\EditQuizAttempt::route('/{record}/edit'),
        ];
    }
}

