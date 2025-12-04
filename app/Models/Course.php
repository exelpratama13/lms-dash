<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

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

    protected $hidden = [
        'pricings',
        'batches',
    ];

    protected $appends = [
        'thumbnail_url',
        'creation_year',
        'price',
    ];

    public function getPriceAttribute()
    {
        // Eager load relationships if they are not already loaded to prevent N+1 issues.
        $this->loadMissing(['batches.pricing', 'pricings']);

        // Filter for active batches (end_date is in the future).
        $activeBatches = $this->batches->where('end_date', '>=', now());

        if ($activeBatches->isNotEmpty()) {
            // Find the minimum price from the pricings of all active batches.
            $minPrice = $activeBatches->map(function ($batch) {
                return $batch->pricing ? (float) $batch->pricing->price : null;
            })->filter()->min(); // filter() removes nulls before finding the min.

            if ($minPrice !== null) {
                return $minPrice;
            }
        }

        // Fallback for on-demand courses (no active batches or batches without price).
        $cheapestOnDemand = $this->pricings->sortBy('price')->first();
        return $cheapestOnDemand ? (float) $cheapestOnDemand->price : null;
    }


    /**
     * Get the year the course was created.
     */
    public function getCreationYearAttribute(): ?int
    {
        if (isset($this->attributes['created_at'])) {
            return date('Y', strtotime($this->attributes['created_at']));
        }
        return null;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

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

    public function contents(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(CourseContent::class, CourseSection::class);
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

    public function progress(): HasMany
    {
        return $this->hasMany(CourseProgress::class);
    }

    /**
     * Get full URL for thumbnail (includes storage prefix and app URL).
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $thumbnail = $this->attributes['thumbnail'] ?? null;

        if (empty($thumbnail)) {
            return null;
        }

        // If it's already a full URL, return as-is
        if (preg_match('#^https?://#i', $thumbnail)) {
            return $thumbnail;
        }

        // If it already contains the /storage/ prefix (absolute path), ensure APP_URL is prepended
        if (str_starts_with($thumbnail, '/storage/')) {
            return url(ltrim($thumbnail, '/'));
        }

        // Otherwise, assume it's a relative storage path like 'thumbnails/xxx.png'
        $path = ltrim($thumbnail, '/');
        return url('storage/' . $path);
    }
}

