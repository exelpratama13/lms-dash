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

    protected $casts = [
        'is_popular' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function mentors(): HasMany
    {
        return $this->hasMany(CourseMentor::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(CourseStudent::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(CourseBenefit::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    public function pricings(): BelongsToMany
    {
        return $this->belongsToMany(Pricing::class, 'course_pricings');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(CourseBatch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
