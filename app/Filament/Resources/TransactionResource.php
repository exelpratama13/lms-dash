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
use Filament\Tables\Columns\ImageColumn;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Payment';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

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
                        User::role(['mentor', 'student'])
                            ->pluck('name', 'id')
                    )
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
                Select::make('pricing_id')
                    ->label('Pricing Plan')
                    ->prefix('Rp ')
                    ->relationship('pricing', 'name')
                    // ->searchable()
                    ->preload()
                    ->required()
                    // 1. Membuat field ini reaktif
                    ->live()
                    // 2. Mengisi Sub Total Amount ketika Pricing Plan berubah
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $pricing = Pricing::find($state);
                            $price = $pricing ? $pricing->price : 0;

                            // Asumsi Tax Rate 10%
                            $taxRate = 0.12;
                            $taxAmount = $price * $taxRate;
                            $grandTotal = $price + $taxAmount;

                            // Mengisi field Sub Total Amount
                            $set('sub_total_amount', $price);
                            // Mengisi field Tax Amount
                            $set('total_tax_amount', $taxAmount);
                            // Mengisi field Grand Total Amount
                            $set('grand_total_amount', $grandTotal);
                        } else {
                            // Reset jika tidak ada yang dipilih
                            $set('sub_total_amount', 0);
                            $set('total_tax_amount', 0);
                            $set('grand_total_amount', 0);
                        }
                    }),
                TextInput::make('sub_total_amount')
                    ->label('Sub Total Amount')
                    ->prefix('Rp ')
                    ->numeric()
                    ->required(),
                TextInput::make('total_tax_amount')
                    ->label('Total Tax Amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->required()
                    ->readOnly(),
                TextInput::make('grand_total_amount')
                    ->label('Grand Total Amount')
                    ->prefix('Rp ')
                    ->numeric()
                    ->required(),
                TextInput::make('payment_type')
                    ->label('Payment Type')
                    ->required(),
                TextInput::make('proof')
                    ->label('Proof')
                    ->nullable(),
                Toggle::make('is_paid')
                    ->label('Is Paid')
                    ->onColor('success')
                    ->offColor('danger')
                    ->required(),

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
                ImageColumn::make('proof_url')
                    ->label('Proof of Payment')
                    ->square(),
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
                Tables\Actions\Action::make('view_proof')
                    ->label('View Proof')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Transaction $record): ?string => $record->proof_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Transaction $record): bool => !empty($record->proof)),
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
