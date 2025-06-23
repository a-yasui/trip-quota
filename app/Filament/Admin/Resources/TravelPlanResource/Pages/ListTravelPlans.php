<?php

namespace App\Filament\Admin\Resources\TravelPlanResource\Pages;

use App\Filament\Admin\Resources\TravelPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTravelPlans extends ListRecords
{
    protected static string $resource = TravelPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
