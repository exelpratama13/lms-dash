<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        if ($record->batches->isNotEmpty()) {
            $data['course_type'] = 'batch';
        } else {
            $data['course_type'] = 'on_demand';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If no new thumbnail is uploaded, the field will be null.
        // We unset it from the data array so that the existing value in the database is not overwritten.
        if ($data['thumbnail'] === null) {
            unset($data['thumbnail']);
        }

        return $data;
    }
}
