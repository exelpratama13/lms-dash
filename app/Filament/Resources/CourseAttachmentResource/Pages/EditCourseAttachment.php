<?php

namespace App\Filament\Resources\CourseAttachmentResource\Pages;

use App\Filament\Resources\CourseAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseAttachment extends EditRecord
{
    protected static string $resource = CourseAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
