<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseBatchResource\Pages;
use App\Filament\Resources\CourseBatchResource\RelationManagers;
use App\Models\CourseBatch;
use App\Models\Course;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseBatchResource extends Resource
{
    protected static ?string $model = CourseBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Batch')
                    ->required()
                    ->maxLength(255),
                Select::make('mentor_id')
                    ->label('Mentor')
                    ->options(
                        User::role(['mentor', 'admin'], 'web')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),
                Select::make('course_id')
                    ->label('Kursus')
                    ->options(
                        Course::all()->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Batch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mentor.name')
                    ->label('Mentor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.name')
                    ->label('Kursus')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListCourseBatches::route('/'),
            'create' => Pages\CreateCourseBatch::route('/create'),
            'edit' => Pages\EditCourseBatch::route('/{record}/edit'),
        ];
    }
}
