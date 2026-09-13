<?php

namespace App\Filament\Resources\JobLevels\Tables;

use App\Models\JobLevel;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class JobLevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('level')
                    ->label('Rank')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            /** Sorted by seniority, not code: the ladder order is the useful one. */
            ->defaultSort('level')
            /** Keep the user's view between visits. */
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->persistColumnsInSession()
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                /** Toggling here instead of the form, so it always asks first. */
                Action::make('toggleActive')
                    ->iconButton()
                    ->icon(Heroicon::OutlinedPower)
                    ->tooltip(fn (JobLevel $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->color(fn (JobLevel $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (JobLevel $record): string => ($record->is_active ? 'Deactivate ' : 'Activate ').$record->name)
                    ->modalDescription(fn (JobLevel $record): string => $record->is_active
                        ? 'The level stays on existing records but cannot be assigned to new ones.'
                        : 'The level can be assigned again.')
                    ->modalSubmitActionLabel('Confirm')
                    ->action(function (JobLevel $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->success()
                            ->title($record->is_active ? 'Job level activated' : 'Job level deactivated')
                            ->send();
                    }),
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
