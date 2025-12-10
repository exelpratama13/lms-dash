<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseAttachmentResource\Pages;
use App\Models\CourseAttachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseAttachmentResource extends Resource
{
    protected static ?string $model = CourseAttachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_content_id')
                    ->relationship('courseContent', 'name')
                    ->label('Section Content')
                    ->required(),

                Forms\Components\FileUpload::make('file')
                    ->label('Attachment File')
                    ->directory('course-attachments')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('courseContent.name')
                    ->label('Section Content'),

                Tables\Columns\TextColumn::make('file')
                    ->label('File')
                    ->url(fn ($record) => $record->file ? asset('storage/' . $record->file) : null, true)
                    ->openUrlInNewTab(),
            ])
            ->filters([])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->paginationPageOptions([5, 10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseAttachments::route('/'),
            'create' => Pages\CreateCourseAttachment::route('/create'),
            'edit' => Pages\EditCourseAttachment::route('/{record}/edit'),
        ];
    }
}

