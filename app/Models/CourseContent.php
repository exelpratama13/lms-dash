<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'course_section_id',
        'content',
    ];


    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(CourseVideo::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function attachment(): HasOne
    {
        return $this->hasOne(CourseAttachment::class);
    }

    public function courseProgress()
{
    return $this->hasMany(CourseProgress::class);
}
}
