<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseProgress extends Model
{
    use HasFactory;

    protected $table = 'course_progresses';

    protected $fillable = [
        'user_id',
        'progress_percentage',
        'course_id',
        'course_batch_id',
        'course_section_id',
        'course_content_id',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function courseBatch()
    {
        return $this->belongsTo(CourseBatch::class);
    }

    public function courseSection()
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function courseContent()
    {
        return $this->belongsTo(CourseContent::class, 'course_content_id');
    }

    public function sertificates()
    {
        return $this->hasMany(Sertificate::class);
    }
}
