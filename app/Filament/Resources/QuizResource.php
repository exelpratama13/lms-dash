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

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Quizzes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Quiz Title')
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
            ]);
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
