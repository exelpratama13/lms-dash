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
        return $this->hasRole('admin');
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'is_active',
    ];


    protected $hidden = [
        'password',
        'remember_token',

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

    public function courseMentors(): HasMany
    {
        return $this->hasMany(CourseMentor::class, 'user_id');
    }
}
