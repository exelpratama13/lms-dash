<?php

namespace App\Filament\Resources\SertificateResource\Pages;

use App\Filament\Resources\SertificateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSertificates extends ListRecords
{
    protected static string $resource = SertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
