<?php

namespace App\Providers;

use App\Contracts\ImageLabelAnalyzer;
use App\Contracts\ImageSafeSearchAnalyzer;
use App\Contracts\ImageWatermarker;
use App\Models\Category;
use App\Services\GoogleVision\GoogleVisionImageLabelAnalyzer;
use App\Services\GoogleVision\GoogleVisionSafeSearchAnalyzer;
use App\Services\Images\GdLogoImageWatermarker;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ImageLabelAnalyzer::class, GoogleVisionImageLabelAnalyzer::class);
        $this->app->bind(ImageSafeSearchAnalyzer::class, GoogleVisionSafeSearchAnalyzer::class);
        $this->app->bind(ImageWatermarker::class, GdLogoImageWatermarker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        RateLimiter::for(
            'google-vision',
            fn (): Limit => Limit::perMinute(
                (int) config('services.google_vision.requests_per_minute', 60),
            )->by('google-vision'),
        );

        if (Schema::hasTable('categories')) {
            View::share('categories', Category::orderBy('name')->get());
        }
    }
}
