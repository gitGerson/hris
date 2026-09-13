<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListUserActivities extends ListActivitiesBySubject
{
    protected static string $resource = UserResource::class;

    protected static ?string $navigationLabel = 'Activity Log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;
}
