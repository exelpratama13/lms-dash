<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionOptionResource\Pages;
use App\Models\QuestionOption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionOptionResource extends Resource
{
    protected static ?string $model = QuestionOption::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Quizzes';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('question_id')
                    ->relationship('question', 'question_text')
                    ->required(),

                Forms\Components\TextInput::make('option_text')
                    ->label('Option Text')
                    ->required(),

                Forms\Components\Toggle::make('is_correct')
                    ->label('Correct Answer')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Question')
                    ->limit(50),

                Tables\Columns\TextColumn::make('option_text')
                    ->label('Option'),

                Tables\Columns\IconColumn::make('is_correct')
                    ->boolean()
                    ->label('Correct?'),
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
            'index' => Pages\ListQuestionOptions::route('/'),
            'create' => Pages\CreateQuestionOption::route('/create'),
            'edit' => Pages\EditQuestionOption::route('/{record}/edit'),
        ];
    }
}
