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
                Forms\Components\Select::make('user_id')
                    ->label('Student Name')
                    ->relationship('quizAttempt.user', 'name')
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('quiz_attempt_id', null))
                    ->required(),

                Forms\Components\Select::make('quiz_attempt_id')
                    ->label('Quiz Attempt')
                    ->options(function (callable $get) {
                        $user = \App\Models\User::find($get('user_id'));
                        if (!$user) {
                            return [];
                        }
                        return $user->quizAttempts->pluck('id', 'id')->toArray();
                    })
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('question_id', null))
                    ->required(),

                Forms\Components\Select::make('question_id')
                    ->label('Question')
                    ->options(function (callable $get) {
                        $quizAttempt = \App\Models\QuizAttempt::find($get('quiz_attempt_id'));
                        if (!$quizAttempt) {
                            return [];
                        }
                        return $quizAttempt->quiz->questions->pluck('question_text', 'id')->toArray();
                    })
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('question_option_id', null))
                    ->required(),

                Forms\Components\Select::make('question_option_id')
                    ->label('Chosen Option')
                    ->options(function (callable $get) {
                        $question = \App\Models\Question::find($get('question_id'));
                        if (!$question) {
                            return [];
                        }
                        return $question->options->pluck('option_text', 'id')->toArray();
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quizAttempt.user.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quizAttempt.id')
                    ->label('Quiz Attempt'),

                Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Question')
                    ->limit(50),

                Tables\Columns\TextColumn::make('chosenOption.option_text')
                    ->label('Answer Option')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('quizAttempt.user', 'name')
                    ->label('Student Name'),
            ])
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
