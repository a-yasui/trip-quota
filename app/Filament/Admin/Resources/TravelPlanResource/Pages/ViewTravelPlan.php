<?php

namespace App\Filament\Admin\Resources\TravelPlanResource\Pages;

use App\Filament\Admin\Resources\TravelPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTravelPlan extends ViewRecord
{
    protected static string $resource = TravelPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
