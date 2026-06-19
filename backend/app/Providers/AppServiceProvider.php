<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\MeasurementService::class, function ($app) {
            return new \App\Services\MeasurementService(
                $app->make(\App\Services\MetricsCalculatorService::class),
                $app->make(\App\Services\AnomalyDetector::class),
                $app->make(\App\Services\MapRendererService::class),
            );
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
