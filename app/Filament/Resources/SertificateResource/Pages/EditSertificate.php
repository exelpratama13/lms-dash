<?php

namespace App\Filament\Resources\SertificateResource\Pages;

use App\Filament\Resources\SertificateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSertificate extends EditRecord
{
    protected static string $resource = SertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
