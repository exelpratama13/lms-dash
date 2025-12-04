<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseMentorResource\Pages;
use App\Filament\Resources\CourseMentorResource\RelationManagers;
use App\Models\CourseMentor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use App\Models\Course;

class CourseMentorResource extends Resource
{
    protected static ?string $model = CourseMentor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Users';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Mentor')
                    ->options(
                        User::role(['mentor', 'admin'], 'web')
                            ->pluck('name', 'id')
                    )
                    // ->searchable()
                    ->required(),
                Select::make('course_id')
                    ->label('Course')
                    ->options(Course::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('job')
                    ->label('Job')
                    ->required()
                    ->maxLength(255),
                Textarea::make('about')
                    ->label('About')
                    ->maxLength(65535)
                    ->columnSpan('full'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Mentor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.name')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                // TextColumn::make('job')
                //     ->label('Job')
                //     ->searchable()
                //     ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCourseMentors::route('/'),
            'create' => Pages\CreateCourseMentor::route('/create'),
            'edit' => Pages\EditCourseMentor::route('/{record}/edit'),
        ];
    }
}
