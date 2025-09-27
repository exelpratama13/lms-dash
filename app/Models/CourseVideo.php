<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_youtube',
        'section_content_id',
    ];

    /**
     * Get the section content that owns the video.
     */
    public function sectionContent(): BelongsTo
    {
        return $this->belongsTo(CourseContent::class);
    }
}
