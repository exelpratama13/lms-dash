<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'question_option_id',
    ];


    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function chosenOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
