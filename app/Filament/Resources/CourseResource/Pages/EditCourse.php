<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['thumbnail'])) {
            // Convert relative thumbnail path to full URL for storage
            $data['thumbnail'] = url('storage/' . $data['thumbnail']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
