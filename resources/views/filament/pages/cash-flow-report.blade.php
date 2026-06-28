<x-filament-panels::page>
    <form method="GET" class="fi-section print:hidden">
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

                        <button type="button" onclick="window.print()" class="fi-btn fi-size-md fi-color-gray">
                            Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="purok-print-area">
        <div class="purok-report-header">
            <div>
                <h2 class="purok-report-title">Cash Flow Statement</h2>
                <p class="purok-report-period">
                    Period:
                    {{ $month ? \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') : $year }}
                </p>
            </div>
        </div>

        <div class="purok-stat-grid">
            <div class="purok-stat-card">
                <p class="purok-stat-label">Total Inflow</p>
                <p class="purok-stat-value purok-stat-value-success">PHP {{ number_format($report['totalInflow'], 2) }}</p>
                <p class="purok-stat-note">Incomes plus member contributions</p>
            </div>

            <div class="purok-stat-card">
                <p class="purok-stat-label">Total Outflow</p>
                <p class="purok-stat-value purok-stat-value-danger">PHP {{ number_format($report['expenseTotal'], 2) }}</p>
                <p class="purok-stat-note">Expenses recorded for the selected period</p>
            </div>

            <div class="purok-stat-card">
                <p class="purok-stat-label">Net Cash Flow</p>
                <p class="purok-stat-value {{ $report['netCashFlow'] >= 0 ? 'purok-stat-value-success' : 'purok-stat-value-danger' }}">
                    PHP {{ number_format($report['netCashFlow'], 2) }}
                </p>
                <p class="purok-stat-note">Total inflow less outflow</p>
            </div>
        </div>

        <div class="fi-section purok-report-section">
            <div class="fi-section-content-ctn">
                <div class="fi-section-content">
                    <div class="purok-report-scroll">
                        <table class="purok-report-table">
                            <tbody>
                                <tr class="purok-report-band">
                                    <th colspan="2">
                                        Cash Inflows
                                    </th>
                                </tr>
                                <tr>
                                    <td>Cash on Hand / Incomes / Rentals / Donations / Funding</td>
                                    <td class="purok-report-amount">PHP {{ number_format($report['incomeTotal'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Member Contributions</td>
                                    <td class="purok-report-amount">PHP {{ number_format($report['contributionTotal'], 2) }}</td>
                                </tr>
                                <tr class="purok-report-band">
                                    <th colspan="2">
                                        Cash Outflows
                                    </th>
                                </tr>
                                <tr>
                                    <td>Purok Expenses</td>
                                    <td class="purok-report-amount purok-report-danger">PHP {{ number_format($report['expenseTotal'], 2) }}</td>
                                </tr>
                                <tr class="purok-report-total-row">
                                    <td>Net Cash Flow</td>
                                    <td class="purok-report-amount">PHP {{ number_format($report['netCashFlow'], 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="purok-signature-grid">
            <div class="purok-signature-line">
                <span>Prepared By</span>
                <strong>Purok Treasurer</strong>
            </div>
            <div class="purok-signature-line">
                <span>Audited By</span>
                <strong>Purok Auditor</strong>
            </div>
            <div class="purok-signature-line">
                <span>Approved By</span>
                <strong>Purok President</strong>
            </div>
        </div>
    </div>
</x-filament-panels::page>
