<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\Religion;
use App\Models\Employee;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            /** One column at the top level, so sections stack like the form. */
            ->columns(1)
            ->components([
                Section::make('Employee')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('employee_number')
                            ->label('Employee ID')
                            ->copyable(),
                        TextEntry::make('name')
                            ->label('Full name'),
                        TextEntry::make('phone')
                            ->label('Phone')
                            ->copyable(),
                        TextEntry::make('employment_status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (EmploymentStatus $state): string => $state->label())
                            ->color(fn (EmploymentStatus $state): string => $state->color()),
                    ]),
                Section::make('Placement')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('branch.name')
                            ->label('Work location')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('department.name')
                            ->label('Department')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('position.name')
                            ->label('Position'),
                        TextEntry::make('jobLevel.name')
                            ->label('Level')
                            ->badge()
                            ->color('gray')
                            ->placeholder('-'),
                    ]),
                Section::make('Employment')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('join_date')
                            ->label('Joined')
                            ->date(),
                        TextEntry::make('contract_ends_on')
                            ->label('Contract ends')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('termination_date')
                            ->label('Left on')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('fingerprint_id')
                            ->label('Fingerprint ID')
                            ->placeholder('-'),
                    ]),
                Section::make('Identity')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('national_id')
                            ->label('No. KTP')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('gender')
                            ->label('Gender')
                            ->formatStateUsing(fn (?Gender $state): ?string => $state?->label())
                            ->placeholder('-'),
                        TextEntry::make('marital_status')
                            ->label('Marital status')
                            ->formatStateUsing(fn (?MaritalStatus $state): ?string => $state?->label())
                            ->placeholder('-'),
                        TextEntry::make('religion')
                            ->label('Religion')
                            ->formatStateUsing(fn (?Religion $state): ?string => $state?->label())
                            ->placeholder('-'),
                        TextEntry::make('birth_place')
                            ->label('Place of birth')
                            ->placeholder('-'),
                        TextEntry::make('birth_date')
                            ->label('Date of birth')
                            ->date()
                            ->placeholder('-'),
                    ]),
                Section::make('Address on ID card')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('identity_address')
                            ->hiddenLabel()
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('identityCity.name')
                            ->label('City / Regency')
                            ->placeholder('-'),
                        TextEntry::make('identityProvince.name')
                            ->label('Province')
                            ->placeholder('-'),
                    ]),
                Section::make('Domicile address')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('domicile_address')
                            ->hiddenLabel()
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('domicileCity.name')
                            ->label('City / Regency')
                            ->placeholder('-'),
                        TextEntry::make('domicileProvince.name')
                            ->label('Province')
                            ->placeholder('-'),
                    ]),
                Section::make('Record')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
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
                            ->visible(fn (Employee $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
