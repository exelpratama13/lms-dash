<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mentor_id',
        'course_id',
        'start_date',
        'end_date',
    ];


    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];


    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function sertificates()
    {
        return $this->hasMany(Sertificate::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(CourseProgress::class, 'course_batch_id');
    }
}
