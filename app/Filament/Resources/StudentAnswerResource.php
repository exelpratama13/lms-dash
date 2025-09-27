<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentAnswerResource\Pages;
use App\Models\StudentAnswer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentAnswerResource extends Resource
{
    protected static ?string $model = StudentAnswer::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Quizzes';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('quiz_attempt_id')
                    ->relationship('quizAttempt', 'id')
                    ->required(),

                Forms\Components\Select::make('question_id')
                    ->relationship('question', 'question_text')
                    ->required(),

                Forms\Components\Select::make('question_option_id')
                    ->relationship('chosenOption', 'option_text')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quizAttempt.id')
                    ->label('Quiz Attempt'),

                Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Question')
                    ->limit(50),

                Tables\Columns\TextColumn::make('questionOption.option_text')
                    ->label('Answer Option')
                    ->limit(50),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListStudentAnswers::route('/'),
            'create' => Pages\CreateStudentAnswer::route('/create'),
            'edit' => Pages\EditStudentAnswer::route('/{record}/edit'),
        ];
    }
}
