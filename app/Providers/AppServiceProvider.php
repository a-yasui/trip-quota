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

        // Itinerary domain services
        $this->app->bind(
            \TripQuota\Itinerary\ItineraryRepositoryInterface::class,
            \TripQuota\Itinerary\ItineraryRepository::class
        );

        // Accommodation domain services
        $this->app->bind(
            \TripQuota\Accommodation\AccommodationRepositoryInterface::class,
            \TripQuota\Accommodation\AccommodationRepository::class
        );

        // Expense domain services
        $this->app->bind(
            \TripQuota\Expense\ExpenseRepositoryInterface::class,
            \TripQuota\Expense\ExpenseRepository::class
        );

        // Settlement domain services
        $this->app->bind(
            \TripQuota\Settlement\SettlementRepositoryInterface::class,
            \TripQuota\Settlement\SettlementRepository::class
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
