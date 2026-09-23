<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IncompleteEmployeeData extends StatsOverviewWidget
{
    /**
     * Optional employee fields that HR still needs filled in, keyed by column.
     *
     * @var array<string, string>
     */
    public const TRACKED_FIELDS = [
        'job_level_id' => 'No job level',
        'national_id' => 'No national ID',
        'birth_date' => 'No birth date',
        'fingerprint_id' => 'No fingerprint ID',
    ];

    protected static ?int $sort = 5;

    protected ?string $heading = 'Incomplete employee data';

    protected ?string $description = 'Active employees with fields still empty.';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $missingCounts = $this->missingCounts();

        return collect(self::TRACKED_FIELDS)
            ->map(fn (string $label, string $column): Stat => Stat::make($label, $missingCounts[$column])
                ->color($missingCounts[$column] > 0 ? 'warning' : 'success'))
            ->values()
            ->all();
    }

    /**
     * One query counting empty values for every tracked field.
     *
     * @return array<string, int>
     */
    public function missingCounts(): array
    {
        $selects = collect(array_keys(self::TRACKED_FIELDS))
            ->map(fn (string $column): string => "sum(case when {$column} is null then 1 else 0 end) as {$column}")
            ->implode(', ');

        $row = Employee::query()->active()->toBase()->selectRaw($selects)->first();

        return collect(self::TRACKED_FIELDS)
            ->map(fn (string $label, string $column): int => (int) ($row->{$column} ?? 0))
            ->all();
    }
}
