<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements FilamentUser, JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['admin', 'mentor']);
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'is_active',
        'refresh_token',
        'refresh_token_expires_at',
    ];


    protected $hidden = [
        'password',
        'remember_token',
        'refresh_token',
        'refresh_token_expires_at',

    ];


    protected $casts = [
        'is_active' => 'boolean',
    ];

    // public function getRoleName(): string
    // {
    //     return $this->roles->first()->name ?? 'user';
    // }

    public function mentoredCourses(): HasMany
    {
        return $this->hasMany(CourseMentor::class);
    }

    public function enrolledCourses(): HasMany
    {
        return $this->hasMany(CourseStudent::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function courseProgresses(): HasMany
    {
        return $this->hasMany(CourseProgress::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function sertificates(): HasMany
    {
        return $this->hasMany(Sertificate::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_students');
    }

    public function courseMentors(): HasMany
    {
        return $this->hasMany(CourseMentor::class, 'user_id');
    }

    public function taughtBatches(): HasMany
    {
        return $this->hasMany(CourseBatch::class, 'mentor_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $photo = $this->attributes['photo'] ?? null;

        if (empty($photo)) {
            return null;
        }

        if (preg_match('#^https?://#i', $photo)) {
            return $photo;
        }

        return url('storage/' . ltrim($photo, '/'));
    }
}
