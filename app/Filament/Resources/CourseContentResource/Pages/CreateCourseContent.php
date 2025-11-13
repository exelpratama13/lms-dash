<?php

namespace App\Filament\Resources\CourseContentResource\Pages;

use App\Filament\Resources\CourseContentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\CourseVideo;
use App\Models\CourseAttachment;

class CreateCourseContent extends CreateRecord
{
    protected static string $resource = CourseContentResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $courseContent = $this->record;

        // Handle Video
        if (!empty($data['video_url'])) {
            $youtubeId = $this->getYoutubeId($data['video_url']);
            if ($youtubeId) {
                CourseVideo::create([
                    'course_content_id' => $courseContent->id,
                    'id_youtube' => $youtubeId,
                ]);
            }
        }

        // Handle Attachment
        if (!empty($data['attachment_file'])) {
            CourseAttachment::create([
                'course_content_id' => $courseContent->id,
                'file' => $data['attachment_file'],
            ]);
        }
    }

    private function getYoutubeId(string $url): ?string
    {
        $parts = parse_url($url);
        if (isset($parts['query'])) {
            parse_str($parts['query'], $qs);
            if (isset($qs['v'])) {
                return $qs['v'];
            }
        }
        if (isset($parts['path'])) {
            $path = explode('/', trim($parts['path'], '/'));
            if (in_array($path[0], ['v', 'embed'])) {
                return $path[1];
            }
        }
        return null;
    }
}
