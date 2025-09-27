<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseBenefitResource\Pages;
use App\Models\CourseBenefit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseBenefitResource extends Resource
{
    protected static ?string $model = CourseBenefit::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    // protected static ?string $navigationGroup = 'Courses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Benefit Title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->required(),

                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'name')
                    ->label('Course')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->sortable(),

                // Tables\Columns\TextColumn::make('created_at')->dateTime(),
                // Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'name'),
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
            'index' => Pages\ListCourseBenefits::route('/'),
            'create' => Pages\CreateCourseBenefit::route('/create'),
            'edit' => Pages\EditCourseBenefit::route('/{record}/edit'),
        ];
    }
}
