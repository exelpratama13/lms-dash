<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'course_content_id',
    ];


    public function courseContent(): BelongsTo
    {
        return $this->belongsTo(CourseContent::class);
    }


    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }


    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
