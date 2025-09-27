<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoursePricingResource\Pages;
use App\Models\CoursePricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoursePricingResource extends Resource
{
    protected static ?string $model = CoursePricing::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup = 'Payment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'name')
                    ->required(),

                Forms\Components\Select::make('pricing_id')
                    ->label('Pricing')
                    ->relationship('pricing', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pricing.name')
                    ->label('Pricing Name')
                    ->sortable(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'name'),

                Tables\Filters\SelectFilter::make('pricing')
                    ->relationship('pricing', 'name'),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCoursePricings::route('/'),
            'create' => Pages\CreateCoursePricing::route('/create'),
            'edit' => Pages\EditCoursePricing::route('/{record}/edit'),
        ];
    }
}
