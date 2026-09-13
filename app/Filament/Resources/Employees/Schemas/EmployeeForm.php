<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\Religion;
use App\Models\City;
use App\Models\Employee;
use App\Models\Position;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            /** One column at the top level, so sections stack instead of pairing up. */
            ->columns(1)
            ->components([
                Section::make('Employee')
                    ->columns(3)
                    ->schema([
                        TextInput::make('employee_number')
                            ->label('Employee ID')
                            ->helperText('Generated automatically on save.')
                            ->placeholder(fn (?Employee $record): string => $record?->employee_number ?? Employee::generateEmployeeNumber())
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('name')
                            ->label('Full name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Phone')
                            ->helperText('Also the attendance binding key, so it must be unique.')
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
                Section::make('Placement')
                    ->columns(4)
                    ->schema([
                        Select::make('branch_id')
                            ->label('Work location')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            /** Changing department invalidates the chosen position. */
                            ->afterStateUpdated(fn (Set $set) => $set('position_id', null)),
                        Select::make('position_id')
                            ->label('Position')
                            ->options(fn (Get $get): array => Position::query()
                                ->where('is_active', true)
                                ->when(
                                    $get('department_id'),
                                    fn ($query, $departmentId) => $query->where('department_id', $departmentId),
                                )
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->helperText('Filtered by the chosen department.'),
                        Select::make('job_level_id')
                            ->label('Level')
                            ->relationship('jobLevel', 'name')
                            ->preload(),
                    ]),
                Section::make('Employment')
                    ->columns(4)
                    ->schema([
                        DatePicker::make('join_date')
                            ->label('Joined on')
                            ->native(false)
                            ->required(),
                        DatePicker::make('contract_ends_on')
                            ->label('Contract ends on')
                            ->helperText('Leave empty for permanent staff.')
                            ->native(false),
                        Select::make('employment_status')
                            ->label('Status')
                            ->options(EmploymentStatus::options())
                            ->default(EmploymentStatus::Active->value)
                            ->required()
                            ->live(),
                        DatePicker::make('termination_date')
                            ->label('Left on')
                            ->native(false)
                            /** Only meaningful once the person has left. */
                            ->visible(fn (Get $get): bool => $get('employment_status') !== EmploymentStatus::Active->value),
                    ]),
                Section::make('Identity')
                    ->columns(3)
                    ->schema([
                        TextInput::make('national_id')
                            ->label('No. KTP')
                            ->maxLength(32)
                            ->unique(ignoreRecord: true),
                        Select::make('gender')
                            ->label('Gender')
                            ->options(Gender::options()),
                        Select::make('marital_status')
                            ->label('Marital status')
                            ->options(MaritalStatus::options()),
                        Select::make('religion')
                            ->label('Religion')
                            ->options(Religion::options()),
                        TextInput::make('birth_place')
                            ->label('Place of birth')
                            ->maxLength(255),
                        DatePicker::make('birth_date')
                            ->label('Date of birth')
                            ->native(false)
                            ->maxDate(now()),
                    ]),
                Section::make('Address on ID card')
                    ->columns(2)
                    ->schema([
                        Textarea::make('identity_address')
                            ->hiddenLabel()
                            ->rows(2)
                            ->columnSpanFull(),
                        Select::make('identity_province_id')
                            ->label('Province')
                            ->relationship('identityProvince', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('identity_city_id', null)),
                        Select::make('identity_city_id')
                            ->label('City / Regency')
                            ->options(fn (Get $get): array => self::cityOptions($get('identity_province_id')))
                            ->searchable(),
                    ]),
                Section::make('Domicile address')
                    ->description('Where the employee actually lives, if different from the ID card.')
                    ->columns(2)
                    ->schema([
                        Textarea::make('domicile_address')
                            ->hiddenLabel()
                            ->rows(2)
                            ->columnSpanFull(),
                        Select::make('domicile_province_id')
                            ->label('Province')
                            ->relationship('domicileProvince', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('domicile_city_id', null)),
                        Select::make('domicile_city_id')
                            ->label('City / Regency')
                            ->options(fn (Get $get): array => self::cityOptions($get('domicile_province_id')))
                            ->searchable(),
                    ]),
                Section::make('Attendance')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('fingerprint_id')
                            ->label('Fingerprint ID')
                            ->helperText('Only when an attendance machine is used.')
                            ->maxLength(64)
                            ->unique(ignoreRecord: true),
                    ]),
            ]);
    }

    /**
     * Cities belonging to the chosen province, empty until one is picked.
     *
     * @return array<int, string>
     */
    protected static function cityOptions(mixed $provinceId): array
    {
        if (blank($provinceId)) {
            return [];
        }

        return City::query()
            ->where('province_id', $provinceId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
