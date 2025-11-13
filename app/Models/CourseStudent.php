<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseStudent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'course_batch_id', // Tambahkan ini
        'access_expires_at',
    ];

    /**
     * Get the user that is the student.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that the student belongs to.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the batch that the student belongs to.
     */
    public function batch(): BelongsTo
    {
        // Secara eksplisit memberitahu Laravel nama foreign key yang benar
        return $this->belongsTo(CourseBatch::class, 'course_batch_id');
    }
}
