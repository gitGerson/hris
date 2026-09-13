<?php

namespace App\Filament\Resources\Branches\Schemas;

use Fahiem\FilamentPinpoint\Pinpoint;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            /** One column at the top level, so sections stack instead of pairing up. */
            ->columns(1)
            ->components([
                Section::make('Attendance & location')
                    ->description('Drop the pin on the branch, then drag the circle handle to size the clock-in geofence.')
                    ->columns(3)
                    ->schema([
                        /**
                         * The map writes into latitude/longitude/geofence_radius. Its own state is
                         * not a column, so it must not dehydrate or saving hits an unknown column.
                         */
                        Pinpoint::make('location')
                            ->hiddenLabel()
                            ->provider('leaflet')
                            ->latField('latitude')
                            ->lngField('longitude')
                            ->radiusField('geofence_radius')
                            // Centre and zoom come from config/filament-pinpoint.php.
                            ->defaultRadius(100)
                            ->height(420)
                            ->draggable()
                            ->searchable()
                            ->dehydrated(false)
                            ->columnSpan(2),
                        /** Stacked beside the map. Timezone is omitted: the column defaults to WIB. */
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('geofence_radius')
                                    ->label('Geofence radius')
                                    ->helperText('Or drag the circle on the map.')
                                    ->numeric()
                                    ->minValue(10)
                                    ->maxValue(5000)
                                    ->suffix('m')
                                    ->default(100)
                                    ->required(),
                                TextInput::make('latitude')
                                    ->helperText('Set by the map.')
                                    ->numeric()
                                    ->minValue(-90)
                                    ->maxValue(90),
                                TextInput::make('longitude')
                                    ->helperText('Set by the map.')
                                    ->numeric()
                                    ->minValue(-180)
                                    ->maxValue(180),
                                DatePicker::make('opened_on')
                                    ->label('Opened on')
                                    ->native(false),
                            ]),
                    ]),
                Section::make('Identity')
                    ->columns(3)
                    ->schema([
                        TextInput::make('code')
                            ->helperText('Short unique key used by attendance devices and imports.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->options([
                                'head_office' => 'Head office',
                                'outlet' => 'Outlet',
                                'warehouse' => 'Warehouse',
                                'production' => 'Production',
                            ])
                            ->default('outlet')
                            ->required(),
                    ]),
                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                    ]),
                Section::make('Address')
                    ->columns(3)
                    ->schema([
                        Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->maxLength(255),
                        TextInput::make('province')
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->maxLength(20),
                    ]),
            ]);
    }
}
