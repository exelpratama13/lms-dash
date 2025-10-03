<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseContentResource\Pages;
use App\Models\CourseContent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseContentResource extends Resource
{
    protected static ?string $model = CourseContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    // protected static ?string $navigationGroup = 'Courses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Content Title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('course_section_id')
                    ->label('Course Section')
                    ->relationship('CourseSection', 'name')
                    ->required(),

                Forms\Components\RichEditor::make('content')
                    ->label('Content Body')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseSection.name')
                    ->label('Section')
                    ->sortable(),

                Tables\Columns\TextColumn::make('content')
                    ->limit(50)
                    ->toggleable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('courseSection')
                    ->label('Section')
                    ->relationship('courseSection', 'name'),
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
        return [
            // nanti bisa ditambahkan RelationManager:
            // CourseAttachmentsRelationManager, VideosRelationManager, QuizzesRelationManager
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseContents::route('/'),
            'create' => Pages\CreateCourseContent::route('/create'),
            'edit' => Pages\EditCourseContent::route('/{record}/edit'),
        ];
    }
}
//baru yee