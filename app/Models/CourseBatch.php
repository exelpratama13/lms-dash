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
        'quota',
        'start_date',
        'end_date',
        'course_batch_id',
    ];


    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status'];


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

    /**
     * Get the students for the course batch.
     */
    public function students(): HasMany
    {
        return $this->hasMany(CourseStudent::class);
    }

    /**
     * Get the status of the batch.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        // students_count akan tersedia jika kita menggunakan withCount('students')
        if (!isset($this->attributes['students_count'])) {
            return 'N/A'; // Fallback jika students_count tidak di-load
        }

        return $this->students_count >= $this->quota ? 'Penuh' : 'Tersedia';
    }
}
