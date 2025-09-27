<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Pricing;
use App\Models\Course;
use App\Models\CourseBatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Payment';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('booking_trx_id')
                    ->label('Booking ID')
                    ->required()
                    ->maxLength(255),
                Select::make('user_id')
                    ->label('User')
                    ->options(
                        User::whereIn('role', ['mentor', 'student'])
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),
                Select::make('pricing_id')
                    ->label('Pricing Plan')
                    ->options(Pricing::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('course_id')
                    ->label('Course')
                    ->options(Course::all()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Select::make('course_batch_id')
                    ->label('Course Batch')
                    ->options(CourseBatch::all()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                TextInput::make('sub_total_amount')
                    ->label('Sub Total Amount')
                    ->numeric()
                    ->required(),
                TextInput::make('grand_total_amount')
                    ->label('Grand Total Amount')
                    ->numeric()
                    ->required(),
                TextInput::make('total_tax_amount')
                    ->label('Total Tax Amount')
                    ->numeric()
                    ->required(),
                Toggle::make('is_paid')
                    ->label('Is Paid')
                    ->onColor('success')
                    ->offColor('danger')
                    ->required(),
                TextInput::make('payment_type')
                    ->label('Payment Type')
                    ->required(),
                TextInput::make('proof')
                    ->label('Proof')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_trx_id')
                    ->label('Booking ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pricing.name')
                    ->label('Pricing Plan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.name')
                    ->label('Course Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('courseBatch.name')
                    ->label('Course Batch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('grand_total_amount')
                    ->label('Grand Total')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_type')
                    ->label('Payment Type')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->label('Paid Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
