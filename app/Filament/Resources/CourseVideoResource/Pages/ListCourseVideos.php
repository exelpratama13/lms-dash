<?php

namespace App\Filament\Resources\CourseVideoResource\Pages;

use App\Filament\Resources\CourseVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseVideos extends ListRecords
{
    protected static string $resource = CourseVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
