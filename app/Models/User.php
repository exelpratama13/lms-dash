<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        'google_id',
        'google_token',
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

    public function getFilamentAvatarUrl(): ?string
    {
        Log::info('--- Avatar Check Start for user: ' . $this->email . ' ---');

        $initialPhoto = $this->getRawOriginal('photo');
        Log::info('1. Value from DB (raw): ' . ($initialPhoto ?? 'null'));

        // If it's a full URL (like from Google), return it directly.
        if (filter_var($initialPhoto, FILTER_VALIDATE_URL)) {
            Log::info('2. Is a valid URL. Returning: ' . $initialPhoto);
            Log::info('--- Avatar Check End ---');
            return $initialPhoto;
        }

        Log::info('2. Not a valid URL. Checking local storage...');

        // If it's a local path, check if the file exists and return its URL
        if ($initialPhoto && Storage::disk('public')->exists($initialPhoto)) {
            $url = Storage::disk('public')->url($initialPhoto);
            Log::info('3. File exists in public storage. Returning URL: ' . $url);
            Log::info('--- Avatar Check End ---');
            return $url;
        }
        
        Log::info('3. File does not exist in public storage or path is empty/null.');
        $fallbackUrl = 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
        Log::info('4. Returning fallback URL: ' . $fallbackUrl);
        Log::info('--- Avatar Check End ---');

        return $fallbackUrl;
    }
}
