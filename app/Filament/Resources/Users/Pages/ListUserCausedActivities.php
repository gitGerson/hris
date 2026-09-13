<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesByCauser;

class ListUserCausedActivities extends ListActivitiesByCauser
{
    protected static string $resource = UserResource::class;

    protected static ?string $navigationLabel = 'Actions Taken';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;
}
