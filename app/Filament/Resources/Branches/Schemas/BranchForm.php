<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('type')
                            ->options([
                                'head_office' => 'Head office',
                                'outlet' => 'Outlet',
                                'warehouse' => 'Warehouse',
                                'production' => 'Production',
                            ])
                            ->default('outlet')
                            ->required(),
                        TextInput::make('code')
                            ->helperText('Short unique key used by attendance devices and imports.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
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
                    ->columns(2)
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
                Section::make('Attendance & location')
                    ->description('Coordinates and radius define the geofence for clock-in.')
                    ->columns(2)
                    ->schema([
                        Select::make('timezone')
                            ->options([
                                'Asia/Jakarta' => 'WIB - Asia/Jakarta',
                                'Asia/Makassar' => 'WITA - Asia/Makassar',
                                'Asia/Jayapura' => 'WIT - Asia/Jayapura',
                            ])
                            ->default('Asia/Jakarta')
                            ->required(),
                        TextInput::make('geofence_radius')
                            ->label('Geofence radius')
                            ->numeric()
                            ->minValue(10)
                            ->maxValue(5000)
                            ->suffix('meters')
                            ->default(100)
                            ->required(),
                        TextInput::make('latitude')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90),
                        TextInput::make('longitude')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180),
                    ]),
                Section::make('Status')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('opened_on')
                            ->label('Opened on')
                            ->native(false),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
