<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ExpiringContracts extends TableWidget
{
    /** How far ahead a contract end counts as "expiring". */
    public const WINDOW_DAYS = 60;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Contracts ending soon')
            ->description('Active employees whose contract ends in the next '.self::WINDOW_DAYS.' days.')
            ->query(fn (): Builder => Employee::query()
                ->active()
                ->with(['branch', 'position'])
                ->whereBetween('contract_ends_on', [today()->toDateString(), today()->addDays(self::WINDOW_DAYS)->toDateString()]))
            ->columns([
                TextColumn::make('employee_number')
                    ->label('ID'),
                TextColumn::make('name'),
                TextColumn::make('position.name')
                    ->label('Position'),
                TextColumn::make('branch.name')
                    ->label('Work location')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('contract_ends_on')
                    ->label('Contract ends')
                    ->date()
                    ->sortable()
                    ->description(fn (Employee $record): string => $this->daysLeftLabel($record)),
            ])
            ->defaultSort('contract_ends_on')
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No contracts ending soon')
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View')
                    ->color('info')
                    ->url(fn (Employee $record): string => EmployeeResource::getUrl('view', ['record' => $record])),
            ]);
    }

    protected function daysLeftLabel(Employee $employee): string
    {
        $daysLeft = (int) today()->diffInDays($employee->contract_ends_on);

        return match ($daysLeft) {
            0 => 'Ends today',
            1 => 'In 1 day',
            default => "In {$daysLeft} days",
        };
    }
}
