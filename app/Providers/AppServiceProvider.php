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

        // Group domain services
        $this->app->bind(
            \TripQuota\Group\GroupRepositoryInterface::class,
            \TripQuota\Group\GroupRepository::class
        );

        // Member domain services
        $this->app->bind(
            \TripQuota\Member\MemberRepositoryInterface::class,
            \TripQuota\Member\MemberRepository::class
        );

        // Invitation domain services
        $this->app->bind(
            \TripQuota\Invitation\InvitationRepositoryInterface::class,
            \TripQuota\Invitation\InvitationRepository::class
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
