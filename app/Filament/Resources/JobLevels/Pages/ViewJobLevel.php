<?php

namespace App\Filament\Resources\JobLevels\Pages;

use App\Filament\Resources\JobLevels\JobLevelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJobLevel extends ViewRecord
{
    protected static string $resource = JobLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
