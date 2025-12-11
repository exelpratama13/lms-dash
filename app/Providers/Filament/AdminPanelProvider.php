<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Assets\Css;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString; // Added this line
use App\Models\User;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->brandLogo(
                fn () => new HtmlString(
                    '<div style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none; color: inherit;">' .
                    '<img src="' . asset('images/logo.png') . '" alt="Inovindo Academy Logo" style="height: 2.5rem; width: auto;" />' .
                    '<span style="font-weight: bold; font-size: 1.25rem; color: #1f2937;">Inovindo Academy</span>' .
                    '</div>'
                )
            )
            ->colors([
                'primary' => Color::Blue,
            ])
            ->assets([
                Css::make('custom-stylesheet', resource_path('css/filament/admin/theme.css')),
            ])
            ->navigationGroups([
                NavigationGroup::make('Manajemen Kursus')
                    ->icon('heroicon-o-book-open'),
                NavigationGroup::make('Manajemen Pengguna')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make('Transaksi & Keuangan')
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make('Sertifikat & Kemajuan')
                    ->icon('heroicon-o-trophy'),
                NavigationGroup::make('Kuis & Penilaian')
                    ->icon('heroicon-o-clipboard-document-list'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\CustomAccountWidget::class,
                \App\Filament\Widgets\CourseStatsOverviewWidget::class,
                \App\Filament\Widgets\MostPopularCourseChart::class,
                \App\Filament\Widgets\ActiveStudentsChart::class,
                \App\Filament\Widgets\StudentEnrollmentTrendChart::class,
                \App\Filament\Widgets\CourseCompletionRateChart::class,
                \App\Filament\Widgets\TopPerformingStudentsTable::class,
                \App\Filament\Widgets\StudentProgressPerCourseTable::class,
                \App\Filament\Widgets\CourseContentAccessTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
