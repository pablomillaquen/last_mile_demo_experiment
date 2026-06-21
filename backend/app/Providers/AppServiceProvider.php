<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\DistanceService::class);

        $this->app->bind(\App\Services\OsrmClient::class, function ($app) {
            return new \App\Services\OsrmClient(
                baseUrl: env('OSRM_BASE_URL', 'http://osrm:5000'),
            );
        });

        $this->app->bind(\App\Services\MeasurementService::class, function ($app) {
            return new \App\Services\MeasurementService(
                $app->make(\App\Services\MetricsCalculatorService::class),
                $app->make(\App\Services\DistanceService::class),
                $app->make(\App\Services\AnomalyDetector::class),
                $app->make(\App\Services\MapRendererService::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
