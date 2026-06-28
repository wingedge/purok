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
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="fi-section">
            <div class="fi-section-content-ctn">
                <div class="fi-section-content">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Inflow</p>
                    <p class="mt-2 text-2xl font-semibold text-success-600">PHP {{ number_format($report['totalInflow'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="fi-section">
            <div class="fi-section-content-ctn">
                <div class="fi-section-content">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Outflow</p>
                    <p class="mt-2 text-2xl font-semibold text-danger-600">PHP {{ number_format($report['expenseTotal'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="fi-section">
            <div class="fi-section-content-ctn">
                <div class="fi-section-content">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Cash Flow</p>
                    <p class="mt-2 text-2xl font-semibold {{ $report['netCashFlow'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        PHP {{ number_format($report['netCashFlow'], 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="fi-section">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <tr>
                                <th colspan="2" class="bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:bg-gray-900 dark:text-gray-300">
                                    Cash Inflows
                                </th>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">Cash on Hand / Incomes / Rentals / Donations / Funding</td>
                                <td class="px-4 py-3 text-right font-medium">PHP {{ number_format($report['incomeTotal'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">Member Contributions</td>
                                <td class="px-4 py-3 text-right font-medium">PHP {{ number_format($report['contributionTotal'], 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="2" class="bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:bg-gray-900 dark:text-gray-300">
                                    Cash Outflows
                                </th>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">Purok Expenses</td>
                                <td class="px-4 py-3 text-right font-medium text-danger-600">PHP {{ number_format($report['expenseTotal'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-base font-semibold text-gray-900 dark:text-white">Net Cash Flow</td>
                                <td class="px-4 py-3 text-right text-base font-semibold">PHP {{ number_format($report['netCashFlow'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
