<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';

    protected static ?string $title = 'Branches';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
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
                TextInput::make('city')
                    ->maxLength(255),
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
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->headline()->toString()),
                TextColumn::make('city')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('timezone')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('geofence_radius')
                    ->label('Geofence')
                    ->suffix(' m')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('code')
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'head_office' => 'Head office',
                        'outlet' => 'Outlet',
                        'warehouse' => 'Warehouse',
                        'production' => 'Production',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            /** No associate/dissociate: company_id is NOT NULL, so a branch cannot be orphaned. */
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
