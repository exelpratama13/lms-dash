<?php

namespace App\Filament\Resources\CourseVideoResource\Pages;

use App\Filament\Resources\CourseVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseVideo extends EditRecord
{
    protected static string $resource = CourseVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
