<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\StatsRepositoryInterface;
use App\Repositories\StatsRepository;
use App\Interfaces\StatsServiceInterface;
use App\Services\StatsService;
use App\Interfaces\CertificateServiceInterface;
use App\Services\CertificateService;
use App\Interfaces\CertificateRepositoryInterface;
use App\Repositories\CertificateRepository;
use App\Models\Sertificate; // Added
use App\Observers\SertificateObserver; // Added

class AppServiceProvider extends ServiceProvider
{


    /**
     * Bootstrap any application services.
     */

    public function register(): void
    {
        $this->app->bind(StatsRepositoryInterface::class, StatsRepository::class);
        $this->app->bind(StatsServiceInterface::class, StatsService::class);
        $this->app->bind(CertificateServiceInterface::class, CertificateService::class);
        $this->app->bind(CertificateRepositoryInterface::class, CertificateRepository::class);
    }
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        Route::middleware('api')
            ->prefix('api') // Menambahkan awalan '/api' secara otomatis
            ->group(base_path('routes/api.php'));

        Sertificate::observe(SertificateObserver::class); // Added
    }
}
