<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'role',
        'is_active',
    ];


    protected $hidden = [
        'password',
    ];


    protected $casts = [
        'is_active' => 'boolean',
    ];

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
}
