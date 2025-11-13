<?php

namespace App\Filament\Resources\SertificateResource\Pages;

use App\Filament\Resources\SertificateResource;
use Filament\Actions;
use Filament\Actions\Action; // Added
use Filament\Resources\Pages\EditRecord;

class EditSertificate extends EditRecord
{
    protected static string $resource = SertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadCertificate') // Added
                ->label('Download Certificate')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn ($record): ?string => $record->sertificate_url ? url($record->sertificate_url) : null)
                ->openUrlInNewTab()
                ->hidden(fn ($record): bool => !$record->sertificate_url),
            Actions\DeleteAction::make(),
        ];
    }
}
