<?php

namespace App\Filament\Resources\CourseBenefitResource\Pages;

use App\Filament\Resources\CourseBenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseBenefits extends ListRecords
{
    protected static string $resource = CourseBenefitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
