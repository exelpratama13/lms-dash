<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'thumbnail',
        'about',
        'category_id',
        'is_popular',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Menggunakan relasi BelongsToMany untuk mentor
    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_mentors', 'course_id', 'user_id');
    }

    public function course_benefits(): HasMany
    {
        return $this->hasMany(CourseBenefit::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    public function course_batches(): HasMany
    {
        return $this->hasMany(CourseBatch::class);
    }

    public function course_students(): HasMany
    {
        return $this->hasMany(CourseStudent::class);
    }

    public function course_pricings(): HasMany
    {
        return $this->hasMany(CoursePricing::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
