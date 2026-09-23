<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class HeadcountByDepartmentChart extends ChartWidget
{
    protected ?string $heading = 'Headcount by department';

    protected ?string $description = 'Active employees in each active department.';

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $headcounts = Employee::query()
            ->active()
            ->selectRaw('department_id, count(*) as headcount')
            ->groupBy('department_id')
            ->pluck('headcount', 'department_id');

        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        return [
            'datasets' => [
                [
                    'label' => 'Active employees',
                    'data' => $departments->keys()->map(fn (int $departmentId): int => (int) ($headcounts[$departmentId] ?? 0))->all(),
                ],
            ],
            'labels' => $departments->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
