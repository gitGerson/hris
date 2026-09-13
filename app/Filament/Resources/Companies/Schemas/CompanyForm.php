<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->helperText('Short unique key used by imports and exports.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('legal_name')
                            ->label('Legal name')
                            ->helperText('Registered entity name, used on payroll documents.')
                            ->maxLength(255),
                        TextInput::make('tax_id')
                            ->label('Tax ID')
                            ->maxLength(255),
                    ]),
                Section::make('Contact')
                    ->columns(3)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('website')
                            ->url()
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
                        Select::make('country')
                            ->options([
                                'ID' => 'Indonesia',
                                'SG' => 'Singapore',
                                'MY' => 'Malaysia',
                            ])
                            ->default('ID')
                            ->required(),
                    ]),
                Section::make('Branding & status')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('companies')
                            ->visibility('public'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
