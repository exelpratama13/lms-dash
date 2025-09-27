<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'course_id',
        'position',
    ];

    /**
     * Get the course that owns the course section.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the section contents for the course section.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(CourseContent::class);
    }

    public function courseProgress()
    {
        return $this->hasMany(CourseProgress::class);
    }
    
}
