<x-filament-panels::page>
    <form method="GET" class="fi-section mb-6">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content">
                <div class="purok-fi-filters" style="--purok-fi-filter-columns: 2;">
                    <label class="purok-fi-field">
                        <span class="purok-fi-label">Year</span>
                        <select name="year" class="purok-fi-control">
                            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </label>

                    <label class="purok-fi-field">
                        <span class="purok-fi-label">Month</span>
                        <select name="month" class="purok-fi-control">
                            <option value="">Full Year</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($month === $m)>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="purok-fi-actions">
                        <button type="submit" class="fi-btn fi-size-md fi-color-primary">
                            Apply
                        </button>
                        <a href="{{ url('/admin/dashboard-summary') }}" class="fi-btn fi-size-md fi-color-gray">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="fi-section mb-6">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content">
                <p class="purok-fi-label">Selected Period</p>
                <p class="purok-stat-value purok-stat-value-primary">{{ $this->periodLabel() }}</p>
            </div>
        </div>
    </div>

    <div class="purok-stat-grid">
        <div class="purok-stat-card">
            <p class="purok-stat-label">Total Purok Members</p>
            <p class="purok-stat-value">{{ number_format($summary['totalMembers']) }}</p>
            <p class="purok-stat-note">All registered member records</p>
        </div>

        <div class="purok-stat-card">
            <p class="purok-stat-label">Current Funds</p>
            <p @class([
                'purok-stat-value',
                'purok-stat-value-success' => $summary['totalFunds'] >= 0,
                'purok-stat-value-danger' => $summary['totalFunds'] < 0,
            ])>
                PHP {{ number_format((float) $summary['totalFunds'], 2) }}
            </p>
            <p class="purok-stat-note">Income, contributions, and funding minus expenses</p>
        </div>

        <div class="purok-stat-card">
            <p class="purok-stat-label">Total Inflow</p>
            <p class="purok-stat-value purok-stat-value-success">
                PHP {{ number_format((float) ($summary['totalIncomes'] + $summary['totalContributions'] + $summary['totalCommunityFunding']), 2) }}
            </p>
            <p class="purok-stat-note">Income records, member contributions, and community funding</p>
        </div>

        <div class="purok-stat-card">
            <p class="purok-stat-label">Total Expenses</p>
            <p class="purok-stat-value purok-stat-value-danger">
                PHP {{ number_format((float) $summary['totalExpenses'], 2) }}
            </p>
            <p class="purok-stat-note">Expenses for the selected period</p>
        </div>

        <div class="purok-stat-card">
            <p class="purok-stat-label">Members Contributed</p>
            <p class="purok-stat-value">{{ number_format($summary['contributorsCount']) }}</p>
            <p class="purok-stat-note">Unique contributors in the selected period</p>
        </div>

        <div class="purok-stat-card">
            <p class="purok-stat-label">Total Contributions ({{ $year }})</p>
            <p class="purok-stat-value purok-stat-value-primary">
                PHP {{ number_format((float) $summary['thisYearContributions'], 2) }}
            </p>
            <p class="purok-stat-note">Full-year contribution total</p>
        </div>

        <div class="purok-stat-card">
            <p class="purok-stat-label">Recent Contributions</p>
            <p class="purok-stat-value purok-stat-value-success">
                PHP {{ number_format((float) $summary['recentContributions'], 2) }}
            </p>
            <p class="purok-stat-note">Recorded during the last 7 days</p>
        </div>

        <div class="purok-stat-card">
            <p class="purok-stat-label">Total Rentals</p>
            <p class="purok-stat-value">{{ number_format($summary['totalRentals']) }}</p>
            <p class="purok-stat-note">Rental records for the selected period</p>
        </div>
    </div>
</x-filament-panels::page>
