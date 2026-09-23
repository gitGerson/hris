<?php

namespace App\Filament\Widgets;

use App\Enums\EmploymentStatus;
use App\Models\Employee;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class HeadcountOverview extends StatsOverviewWidget
{
    /** Months plotted on the active headcount sparkline. */
    public const TREND_MONTHS = 6;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $joinedThisMonth = Employee::query()
            ->whereBetween('join_date', [$monthStart, $monthEnd])
            ->count();

        $leftThisMonth = Employee::query()
            ->where('employment_status', '!=', EmploymentStatus::Active)
            ->whereBetween('termination_date', [$monthStart, $monthEnd])
            ->count();

        return [
            Stat::make('Active employees', Employee::query()->active()->count())
                ->description('Last '.self::TREND_MONTHS.' months')
                ->chart($this->activeHeadcountTrend())
                ->color('success'),
            Stat::make('Joined this month', $joinedThisMonth)
                ->descriptionIcon(Heroicon::OutlinedUserPlus)
                ->description(now()->format('F Y'))
                ->color('info'),
            Stat::make('Left this month', $leftThisMonth)
                ->descriptionIcon(Heroicon::OutlinedUserMinus)
                ->description('Resigned or terminated')
                ->color($leftThisMonth > 0 ? 'danger' : 'gray'),
        ];
    }

    /**
     * Headcount at the end of each month: joined by then and not yet terminated.
     *
     * @return list<int>
     */
    public function activeHeadcountTrend(): array
    {
        return collect(range(self::TREND_MONTHS - 1, 0))
            ->map(fn (int $monthsAgo): Carbon => now()->subMonthsNoOverflow($monthsAgo)->endOfMonth())
            ->map(fn (Carbon $monthEnd): int => Employee::query()
                ->whereDate('join_date', '<=', $monthEnd)
                ->where(fn (Builder $query): Builder => $query
                    ->whereNull('termination_date')
                    ->orWhereDate('termination_date', '>', $monthEnd))
                ->count())
            ->all();
    }
}
