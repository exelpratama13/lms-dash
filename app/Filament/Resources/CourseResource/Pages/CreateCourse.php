<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['thumbnail'])) {
            // Convert relative thumbnail path to full URL for storage
            $data['thumbnail'] = url('storage/' . $data['thumbnail']);
        }

        return $data;
    }
}
