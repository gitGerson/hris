<?php

namespace App\Filament\Resources\JobLevels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            /** One column at the top level, so sections stack instead of pairing up. */
            ->columns(1)
            ->components([
                /** Status is toggled from the table, so it is not a field here. */
                Section::make('Identity')
                    ->columns(3)
                    ->schema([
                        TextInput::make('code')
                            ->helperText('Short unique key used by imports and payroll exports.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('level')
                            ->label('Seniority rank')
                            ->helperText('Lower is more senior. Must be unique.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(255)
                            ->required()
                            ->unique(ignoreRecord: true),
                    ]),
            ]);
    }
}
