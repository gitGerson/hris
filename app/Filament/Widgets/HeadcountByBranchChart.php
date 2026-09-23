<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class HeadcountByBranchChart extends ChartWidget
{
    protected ?string $heading = 'Headcount by work location';

    protected ?string $description = 'Active employees at each active branch.';

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $headcounts = Employee::query()
            ->active()
            ->selectRaw('branch_id, count(*) as headcount')
            ->groupBy('branch_id')
            ->pluck('headcount', 'branch_id');

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        return [
            'datasets' => [
                [
                    'label' => 'Active employees',
                    'data' => $branches->keys()->map(fn (int $branchId): int => (int) ($headcounts[$branchId] ?? 0))->all(),
                ],
            ],
            'labels' => $branches->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
