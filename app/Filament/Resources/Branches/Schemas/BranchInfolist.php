<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Branch;
use Fahiem\FilamentPinpoint\PinpointEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            /** One column at the top level, so sections stack like the form. */
            ->columns(1)
            ->components([
                Section::make('Attendance & location')
                    ->description('Pin and geofence used for clock-in.')
                    ->columns(3)
                    ->schema([
                        /** Coordinates are read straight off the record, not from entry state. */
                        PinpointEntry::make('location')
                            ->hiddenLabel()
                            ->provider('leaflet')
                            ->latField('latitude')
                            ->lngField('longitude')
                            ->radiusField('geofence_radius')
                            ->height(420)
                            ->columnSpan(2),
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('geofence_radius')
                                    ->label('Geofence radius')
                                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state} m" : '-'),
                                TextEntry::make('latitude')
                                    ->placeholder('Not set'),
                                TextEntry::make('longitude')
                                    ->placeholder('Not set'),
                                TextEntry::make('timezone')
                                    ->label('Timezone'),
                                TextEntry::make('opened_on')
                                    ->label('Opened on')
                                    ->date()
                                    ->placeholder('Not set'),
                            ]),
                    ]),
                Section::make('Identity')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('code')
                            ->copyable(),
                        TextEntry::make('name'),
                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->headline()->toString()),
                    ]),
                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('email')
                            ->label('Email address')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->placeholder('-')
                            ->copyable(),
                    ]),
                Section::make('Address')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('address')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('city')
                            ->placeholder('-'),
                        TextEntry::make('province')
                            ->placeholder('-'),
                        TextEntry::make('postal_code')
                            ->label('Postal code')
                            ->placeholder('-'),
                    ]),
                Section::make('Record')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label('Deleted')
                            ->dateTime()
                            ->visible(fn (Branch $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
