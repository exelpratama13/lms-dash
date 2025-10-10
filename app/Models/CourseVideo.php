<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseVideo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_youtube',
        'course_content_id',
    ];

    /**
     * Get the section content that owns the video.
     */
    public function courseContent(): BelongsTo
    {
        return $this->belongsTo(CourseContent::class);
    }
}
