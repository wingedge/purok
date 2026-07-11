<x-filament-panels::page>
    <div class="purok-link-grid">
        @can('view-cashflow-reports')
            <a href="{{ url('/admin/reports/income') }}" class="purok-link-card">
                <h2 class="purok-link-title">Income Report</h2>
                <p class="purok-link-description">Review income records by year or month and generate an Excel workbook.</p>
            </a>

            <a href="{{ url('/admin/reports/expenses') }}" class="purok-link-card">
                <h2 class="purok-link-title">Expense Report</h2>
                <p class="purok-link-description">Review expense records by year or month and generate an Excel workbook.</p>
            </a>

            <a href="{{ url('/admin/reports/cash-flow') }}" class="purok-link-card">
                <h2 class="purok-link-title">Cash Flow Statement</h2>
                <p class="purok-link-description">
                    Review income, member contributions, expenses, and net cash flow by year or month.
                </p>
            </a>

            <a href="{{ url('/admin/reports/community-funding') }}" class="purok-link-card">
                <h2 class="purok-link-title">Community Funding</h2>
                <p class="purok-link-description">
                    Review goals, progress, and donation details for a selected funding event.
                </p>
            </a>

            <a href="{{ url('/admin/reports/rentals') }}" class="purok-link-card">
                <h2 class="purok-link-title">Rental Report</h2>
                <p class="purok-link-description">
                    Review rental activity, statuses, quantities, and income by year or month.
                </p>
            </a>
        @endcan

        @can('view-contribution-reports')
            <a href="{{ url('/admin/reports/contributions') }}" class="purok-link-card">
                <h2 class="purok-link-title">Member Contributions</h2>
                <p class="purok-link-description">
                    View member contribution status and totals across a selected month range.
                </p>
            </a>
        @endcan
    </div>
</x-filament-panels::page>
