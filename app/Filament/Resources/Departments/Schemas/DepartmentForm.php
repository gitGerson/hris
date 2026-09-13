<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            /** One column at the top level, so sections stack instead of pairing up. */
            ->columns(1)
            ->components([
                /** Company is filled by the model; status is toggled from the table. */
                Section::make('Identity')
                    ->columns(2)
                    ->schema([
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
