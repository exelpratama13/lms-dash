<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface MentorRepositoryInterface
{

    public function getMentorProfile(int $userId): ?User;

    public function getAllMentors(): Collection; 

    public function getCoursesByMentor(int $mentorId): Collection;

    public function getMentorsByCategorySlug(string $categorySlug): Collection;

}