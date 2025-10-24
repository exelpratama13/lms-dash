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
use App\Interfaces\CourseSectionRepositoryInterface;
use App\Repositories\CourseSectionRepository;
use App\Interfaces\CourseSectionServiceInterface;
use App\Interfaces\PricingRepositoryInterface;
use App\Interfaces\PricingServiceInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Repositories\PricingRepository;
use App\Repositories\TransactionRepository;
use App\Services\CourseSectionService;
use App\Services\PricingService;
use App\Services\TransactionService;

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

        // Bind Course Section & Content
        $this->app->bind(
            CourseSectionRepositoryInterface::class,
            CourseSectionRepository::class
        );
        $this->app->bind(
            CourseSectionServiceInterface::class,
            CourseSectionService::class
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

        //Bind Pricing
        $this->app->bind(
            PricingServiceInterface::class,
            PricingService::class
        );
        $this->app->bind(
            PricingRepositoryInterface::class,
            PricingRepository::class
        );

        //Bind Transaction
        $this->app->bind(
            TransactionServiceInterface::class,
            TransactionService::class
        );
        $this->app->bind(
            TransactionRepositoryInterface::class,
            TransactionRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
