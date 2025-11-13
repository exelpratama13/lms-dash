<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Sertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'course_id',
        'course_batch_id',
        'course_progress_id',
        'sertificate_url', // Added
    ];

    protected $appends = [
        'sertificate_url', // Added
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function courseBatch(): BelongsTo
    {
        return $this->belongsTo(CourseBatch::class);
    }

    public function courseProgress(): BelongsTo
    {
        return $this->belongsTo(CourseProgress::class);
    }

    /**
     * Get the full URL for the certificate file.
     */
    public function getSertificateUrlAttribute(): ?string
    {
        if ($this->attributes['sertificate_url']) {
            return Storage::url($this->attributes['sertificate_url']);
        }
        return null;
    }
}
