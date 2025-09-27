<?php

namespace App\Filament\Resources\CourseBenefitResource\Pages;

use App\Filament\Resources\CourseBenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseBenefit extends EditRecord
{
    protected static string $resource = CourseBenefitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
