<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Enums\EmploymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            /** national_id and addresses are deliberately absent: identity data does not belong in a list. */
            ->columns([
                TextColumn::make('employee_number')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position.name')
                    ->label('Position')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('branch.name')
                    ->label('Work location')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('jobLevel.name')
                    ->label('Level')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('employment_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (EmploymentStatus $state): string => $state->label())
                    ->color(fn (EmploymentStatus $state): string => $state->color()),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('join_date')
                    ->label('Joined')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('contract_ends_on')
                    ->label('Contract ends')
                    ->date()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('employee_number')
            /** Keep the user's view between visits. */
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->persistColumnsInSession()
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Work location')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('employment_status')
                    ->label('Status')
                    ->options(EmploymentStatus::options()),
                TrashedFilter::make(),
            ])
            /**
             * No power toggle here: employment_status has three states, so a two-way
             * switch cannot express it. Status changes go through the form.
             */
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View')
                    ->color('info'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit')
                    ->color('warning'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
