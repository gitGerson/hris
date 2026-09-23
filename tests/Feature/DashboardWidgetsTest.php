<?php

use App\Enums\EmploymentStatus;
use App\Filament\Widgets\ExpiringContracts;
use App\Filament\Widgets\HeadcountByBranchChart;
use App\Filament\Widgets\HeadcountByDepartmentChart;
use App\Filament\Widgets\HeadcountOverview;
use App\Filament\Widgets\IncompleteEmployeeData;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 9, 15)->startOfDay());

    Filament::setCurrentPanel('admin');
    $this->actingAs(User::factory()->create());
});

test('headcount overview counts joiners and leavers in the current month', function () {
    Employee::factory()->create(['join_date' => '2026-09-01']);
    Employee::factory()->create(['join_date' => '2026-08-31']);
    Employee::factory()->create([
        'join_date' => '2025-01-01',
        'employment_status' => EmploymentStatus::Resigned,
        'termination_date' => '2026-09-10',
    ]);

    Livewire::test(HeadcountOverview::class)
        ->assertSeeInOrder(['Active employees', '2', 'Joined this month', '1', 'Left this month', '1']);
});

test('headcount trend reflects joins and terminations month by month', function () {
    Employee::factory()->create(['join_date' => '2026-04-15']);
    Employee::factory()->create(['join_date' => '2026-07-01']);
    Employee::factory()->create([
        'join_date' => '2026-04-15',
        'employment_status' => EmploymentStatus::Terminated,
        'termination_date' => '2026-06-20',
    ]);

    $trend = Livewire::test(HeadcountOverview::class)->instance()->activeHeadcountTrend();

    /** April through September 2026. */
    expect($trend)->toBe([2, 2, 1, 2, 2, 2]);
});

test('branch chart counts active employees per active branch, including empty ones', function () {
    $busyBranch = Branch::factory()->create(['name' => 'A Busy']);
    Branch::factory()->create(['name' => 'B Empty']);
    Branch::factory()->create(['name' => 'C Closed', 'is_active' => false]);

    /** Created one at a time: count() generates every employee_number before any insert. */
    Employee::factory()->create(['branch_id' => $busyBranch->id]);
    Employee::factory()->create(['branch_id' => $busyBranch->id]);
    Employee::factory()->resigned()->create(['branch_id' => $busyBranch->id]);

    $data = invade(Livewire::test(HeadcountByBranchChart::class)->instance())->getData();

    expect($data['labels'])->toContain('A Busy', 'B Empty')->not->toContain('C Closed')
        ->and(array_combine($data['labels'], $data['datasets'][0]['data']))
        ->toMatchArray(['A Busy' => 2, 'B Empty' => 0]);
});

test('department chart counts active employees per active department', function () {
    $department = Department::factory()->create(['name' => 'Kitchen']);

    Employee::factory()->create(['department_id' => $department->id]);
    Employee::factory()->create(['department_id' => $department->id]);
    Employee::factory()->create(['department_id' => $department->id]);
    Employee::factory()->resigned()->create(['department_id' => $department->id]);

    $data = invade(Livewire::test(HeadcountByDepartmentChart::class)->instance())->getData();

    expect(array_combine($data['labels'], $data['datasets'][0]['data']))->toMatchArray(['Kitchen' => 3]);
});

test('expiring contracts lists only active employees whose contract ends within the window', function () {
    $endingSoon = Employee::factory()->create(['contract_ends_on' => '2026-10-01']);
    $endingLater = Employee::factory()->create(['contract_ends_on' => '2026-12-31']);
    $alreadyEnded = Employee::factory()->create(['contract_ends_on' => '2026-09-14']);
    $noContract = Employee::factory()->create(['contract_ends_on' => null]);
    $resigned = Employee::factory()->resigned()->create(['contract_ends_on' => '2026-09-20']);

    Livewire::test(ExpiringContracts::class)
        ->assertCanSeeTableRecords([$endingSoon])
        ->assertCanNotSeeTableRecords([$endingLater, $alreadyEnded, $noContract, $resigned])
        ->assertSee('In 16 days');
});

test('incomplete data counts empty fields among active employees only', function () {
    Employee::factory()->create(['job_level_id' => null, 'fingerprint_id' => null]);
    Employee::factory()->create(['national_id' => null, 'fingerprint_id' => 'FP-1']);
    Employee::factory()->resigned()->create(['job_level_id' => null]);

    $counts = Livewire::test(IncompleteEmployeeData::class)->instance()->missingCounts();

    expect($counts)->toBe([
        'job_level_id' => 1,
        'national_id' => 1,
        'birth_date' => 0,
        'fingerprint_id' => 1,
    ]);
});
