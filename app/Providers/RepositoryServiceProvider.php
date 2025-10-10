<?php

namespace App\Providers;

use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\CategoryServiceInterface;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\CourseRepositoryInterface;
use App\Repositories\CourseRepository;
use App\Interfaces\CourseServiceInterface;
use App\Interfaces\MentorRepositoryInterface;
use App\Interfaces\MentorServiceInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\MentorRepository;
use App\Services\CategoryService;
use App\Services\CourseService;
use App\Services\MentorService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Course
        $this->app->bind(
            CourseRepositoryInterface::class,
            CourseRepository::class
        );
        $this->app->bind(
            CourseServiceInterface::class,
            CourseService::class
        );

        // Bind Category
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );
        $this->app->bind(
            CategoryServiceInterface::class,
            CategoryService::class
        );

        // Bind Mentor
        $this->app->bind(
            MentorRepositoryInterface::class, 
            MentorRepository::class
        );
        $this->app->bind(
            MentorServiceInterface::class,
            MentorService::class
        );
        
    }

    public function boot(): void
    {
        //
    }
}