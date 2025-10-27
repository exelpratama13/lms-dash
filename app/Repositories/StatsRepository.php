<?php

namespace App\Repositories;

use App\Interfaces\StatsRepositoryInterface;
use App\Models\Course;
use App\Models\Category;
use App\Models\CourseMentor;
use App\Models\CourseStudent;
use App\Models\CourseBenefit;

class StatsRepository implements StatsRepositoryInterface
{
    public function getCounts(): array
    {
        return [
            'courses' => Course::count(),
            'categories' => Category::count(),
            'students' => CourseStudent::distinct('user_id')->count('user_id'),
            'mentors' => CourseMentor::distinct('user_id')->count('user_id'),
            'benefits' => CourseBenefit::count(),
        ];
    }
}
