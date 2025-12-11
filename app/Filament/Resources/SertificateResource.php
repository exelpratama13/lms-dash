<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SertificateResource\Pages;
use App\Models\Sertificate;
use App\Models\User;
use App\Models\CourseProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Services\CertificateGeneratorService;
use Filament\Notifications\Notification;

class SertificateResource extends Resource
{
        protected static ?string $model = Sertificate::class;
    protected static ?string $navigationGroup = 'Sertifikat & Kemajuan';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->options(
                        User::role('student')
                            ->pluck('name', 'id')
                    )
                    ->required(),

                Forms\Components\TextInput::make('code')
                    ->label('Certificate Code')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('recipient_name')
                    ->label('Recipient Name')
                    ->maxLength(255)
                    ->helperText('Name as it appears on the certificate. If empty, will use user name.'),

                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'name')
                    ->required(),

                Forms\Components\Select::make('course_batch_id')
                    ->label('Course Batch')
                    ->relationship('courseBatch', 'name')
                    ->required(),

                Forms\Components\Select::make('course_progress_id')
                    ->label('Course Progress')
                    ->options(
                        CourseProgress::all()->mapWithKeys(function ($progress) {
                            return [
                                $progress->id => "ID: {$progress->id} - {$progress->progress_percentage}%"
                            ];
                        })
                    )
                    ->required(),

                Forms\Components\ViewField::make('certificate_preview')
                    ->view('filament.sertificates.components.certificate-viewer')
                    ->visible(fn($record) => $record && $record->sertificate_url),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Recipient Name (on Certificate)')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Certificate Code')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseBatch.name')
                    ->label('Course Batch')
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseProgress.progress_percentage')
                    ->label('Progress (%)')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'name'),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('regenerate')
                    ->label('Regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('New Recipient Name')
                            ->default(fn (Sertificate $record) => $record->recipient_name ?? $record->user->name), // Removed ->required()
                    ])
                    ->action(function (Sertificate $record, array $data) {
                        try {
                            $generator = app(CertificateGeneratorService::class);
                            // Pass the new name to the generator service.
                            // If $data['recipient_name'] is empty, it will be passed as an empty string,
                            // which CertificateGeneratorService will correctly fallback from.
                            $file = $generator->generatePdf($record, $data['recipient_name']);

                            if (!$file) {
                                Notification::make()
                                    ->title('Regeneration failed')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Update the recipient name on the certificate record itself ONLY if a new name was provided
                            // or if the provided name is different from the current one.
                            if (!empty($data['recipient_name']) && $record->recipient_name !== $data['recipient_name']) {
                                $record->recipient_name = $data['recipient_name'];
                                $record->save();
                            }
                            $record->refresh();

                            Notification::make()
                                ->title('Certificate regenerated')
                                ->success()
                                ->body('The PDF has been regenerated with the new name.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('downloadCertificate')
                    ->label('Download Certificate')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn(Sertificate $record): ?string => $record->sertificate_url ? url($record->sertificate_url) : null)
                    ->openUrlInNewTab()
                    ->hidden(fn(Sertificate $record): bool => !$record->sertificate_url),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSertificates::route('/'),
            'create' => Pages\CreateSertificate::route('/create'),
            'edit' => Pages\EditSertificate::route('/{record}/edit'),
        ];
    }
}

