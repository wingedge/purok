<x-filament-panels::page>
    <div class="purok-link-grid">
        @can('view-cashflow-reports')
            <a href="{{ url('/admin/reports/cash-flow') }}" class="purok-link-card">
                <h2 class="purok-link-title">Cash Flow Statement</h2>
                <p class="purok-link-description">
                    Review income, member contributions, expenses, and net cash flow by year or month.
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
