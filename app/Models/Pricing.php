<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration',
        'price',
    ];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_pricings');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
