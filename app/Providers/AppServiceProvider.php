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
        // TravelPlan domain services
        $this->app->bind(
            \TripQuota\TravelPlan\TravelPlanRepositoryInterface::class,
            \TripQuota\TravelPlan\TravelPlanRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
