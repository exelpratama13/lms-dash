<?php

namespace App\Filament\Resources\CoursePricingResource\Pages;

use App\Filament\Resources\CoursePricingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoursePricing extends EditRecord
{
    protected static string $resource = CoursePricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
