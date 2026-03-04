<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use App\Models\School;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = 2;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    protected function getStats(): array
    {
        $currentYear = date('Y');

        // All time
        $totalDonations = Donation::count();
        $totalDonationAmount = Donation::sum('amount');

        // Total donations this year
        $yearDonations = Donation::where('filing_year', $currentYear)->count();
        $yearDonationTotal = Donation::where('filing_year', $currentYear)->sum('amount');

        // Month comparison
        $thisMonthTotal = Donation::where('filing_year', $currentYear)
            ->whereMonth('created_at', date('m'))
            ->sum('amount');

        $lastMonthTotal = Donation::where('filing_year', $currentYear)
            ->whereMonth('created_at', date('m', strtotime('-1 month')))
            ->sum('amount');

        $monthlyChange = $lastMonthTotal > 0
            ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1)
            : ($thisMonthTotal > 0 ? 100 : 0);

        // Active schools
        $activeSchools = School::whereHas('donations', function ($query) use ($currentYear) {
            $query->where('filing_year', $currentYear);
        })->count();
        $totalSchools = School::count();

        return [
            Stat::make('Total Donations', '$'.number_format($totalDonationAmount / 100, 2))
                ->description($totalDonations.' donations all time')
                ->color('primary'),

            Stat::make("Filing Year {$currentYear}", '$'.number_format($yearDonationTotal / 100, 2))
                ->description($yearDonations.' donations')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            Stat::make('This Month', '$'.number_format($thisMonthTotal / 100, 2))
                ->description(($monthlyChange >= 0 ? '+' : '').$monthlyChange.'% from last month')
                ->descriptionIcon($monthlyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyChange >= 0 ? 'success' : 'danger'),

            Stat::make('Active Schools', $activeSchools.' / '.$totalSchools)
                ->description('Active / Total Schools')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
        ];
    }
}
