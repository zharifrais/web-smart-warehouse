<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AiAnalysisService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiAnalysisService::class, function () {
        return new AiAnalysisService();
    });
    }
    

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
