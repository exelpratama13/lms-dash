<?php

namespace App\Interfaces;

use App\Models\User;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection; 

interface MentorServiceInterface
{
    public function getPublicProfile(int $userId): ?User;

    public function getMentorList(): Collection;

    public function getCoursesTaughtByMentor(int $mentorId): Collection;

    public function getMentorsByCategory(string $categorySlug): Collection; 
}
