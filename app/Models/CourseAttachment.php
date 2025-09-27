<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file',
        'course_content_id',
    ];


    public function courseContent(): BelongsTo
    {
        return $this->belongsTo(CourseContent::class);
    }
}
