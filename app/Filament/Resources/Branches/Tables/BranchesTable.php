<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Models\Branch;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                TextColumn::make('opened_on')
                    ->label('Opened on')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->defaultSort('code')
            /** Keep the user's view between visits. */
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->persistColumnsInSession()
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'head_office' => 'Head office',
                        'outlet' => 'Outlet',
                        'warehouse' => 'Warehouse',
                        'production' => 'Production',
                    ]),
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
                    ->tooltip(fn (Branch $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->color(fn (Branch $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Branch $record): string => ($record->is_active ? 'Deactivate ' : 'Activate ').$record->name)
                    ->modalDescription(fn (Branch $record): string => $record->is_active
                        ? 'Staff will no longer be able to clock in at this branch. Existing records are kept.'
                        : 'This branch will accept clock-ins again.')
                    ->modalSubmitActionLabel('Confirm')
                    ->action(function (Branch $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->success()
                            ->title($record->is_active ? 'Branch activated' : 'Branch deactivated')
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
