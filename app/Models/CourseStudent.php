<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'course_batch_id',
        'pricing_id',
        'access_starts_at',
        'access_expires_at',
        'enrollment_type',
        'is_active',
    ];

    protected $casts = [
        'access_starts_at' => 'datetime',
        'access_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CourseBatch::class, 'course_batch_id');
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class);
    }

    /**
     * Check if student still has active access
     */
    public function hasActiveAccess(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Lifetime/no expiration
        if ($this->access_expires_at === null) {
            return true;
        }

        // Check if not expired
        return now()->isBefore($this->access_expires_at);
    }
}