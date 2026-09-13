<?php

namespace App\Filament\Resources\Positions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PositionForm
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
                        Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('code')
                            ->helperText('Short unique key used by imports and payroll exports.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ]),
            ]);
    }
}
