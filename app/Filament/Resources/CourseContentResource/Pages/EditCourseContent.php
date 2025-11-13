<?php

namespace App\Filament\Resources\CourseContentResource\Pages;

use App\Filament\Resources\CourseContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseContent extends EditRecord
{
    protected static string $resource = CourseContentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $content = $this->getRecord();
        if ($content && $content->courseSection) {
            $data['course_id'] = $content->courseSection->course_id;
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
