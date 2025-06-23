<?php

namespace App\Filament\Admin\Resources\TravelPlanResource\Pages;

use App\Filament\Admin\Resources\TravelPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTravelPlan extends EditRecord
{
    protected static string $resource = TravelPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
