<x-filament-panels::page>
    @php($report = $this->report())

    <form method="GET" class="fi-section print:hidden">
        <div class="fi-section-content-ctn"><div class="fi-section-content">
            <div class="purok-fi-filters purok-fi-filters-compact">
                <label class="purok-fi-field">
                    <span class="purok-fi-label">Year</span>
                    <select name="year" class="purok-fi-control">
                        @foreach (range(now()->year, now()->year - 5) as $y)
                            <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="purok-fi-field">
                    <span class="purok-fi-label">Month</span>
                    <select name="month" class="purok-fi-control">
                        <option value="">Full Year</option>
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($month === $m)>{{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="purok-fi-field">
                    <span class="purok-fi-label">Status</span>
                    <select name="status" class="purok-fi-control">
                        <option value="">All Statuses</option>
                        <option value="rented" @selected($status === 'rented')>Rented</option>
                        <option value="returned" @selected($status === 'returned')>Returned</option>
                    </select>
                </label>
                <div class="purok-fi-actions">
                    <button type="submit" class="fi-btn fi-size-md fi-color-primary">Apply</button>
                    <button type="button" onclick="window.print()" class="fi-btn fi-size-md fi-color-gray">Print</button>
                    <button type="button" wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="fi-btn fi-size-md fi-color-gray">Generate Excel</button>
                </div>
            </div>
        </div></div>
    </form>

    <div class="purok-print-area">
        <div class="purok-report-header">
            <div>
                <h2 class="purok-report-title">Rental Report</h2>
                <p class="purok-report-period">Period: {{ $report['start']->format('M d, Y') }} - {{ $report['end']->format('M d, Y') }}</p>
                <p class="purok-report-period">Status: {{ $status ? ucfirst($status) : 'All' }}</p>
            </div>
            <div class="purok-report-total"><span>Total Rental Income</span><strong>PHP {{ number_format($report['totalIncome'], 2) }}</strong></div>
        </div>

        <div class="purok-stat-grid">
            <div class="purok-stat-card"><p class="purok-stat-label">Rental Records</p><p class="purok-stat-value">{{ number_format($report['rentalCount']) }}</p></div>
            <div class="purok-stat-card"><p class="purok-stat-label">Units Rented</p><p class="purok-stat-value">{{ number_format($report['totalQuantity']) }}</p></div>
            <div class="purok-stat-card"><p class="purok-stat-label">Active Rentals</p><p class="purok-stat-value">{{ number_format($report['activeCount']) }}</p></div>
        </div>

        <div class="fi-section purok-report-section"><div class="fi-section-content-ctn"><div class="fi-section-content"><div class="purok-report-scroll">
            <table class="purok-report-table">
                <thead><tr class="purok-report-band"><th>Rent Date</th><th>Item</th><th>Renter / Contact</th><th>Quantity</th><th>Return Date</th><th>Status</th><th class="purok-report-amount">Amount</th></tr></thead>
                <tbody>
                    @forelse ($report['rentals'] as $rental)
                        <tr>
                            <td>{{ $rental->rent_date->format('M d, Y') }}</td>
                            <td>{{ $rental->inventory?->item_name }}</td>
                            <td>{{ $rental->renter_name }}<br><small>{{ $rental->renter_contact }}</small></td>
                            <td>{{ $rental->quantity }}</td>
                            <td>{{ $rental->return_date?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ ucfirst($rental->status) }}</td>
                            <td class="purok-report-amount">PHP {{ number_format((float) ($rental->income?->amount ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="purok-empty">No rentals found for these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div></div></div>

        <div class="purok-report-footer">Generated: {{ now()->format('Y-m-d H:i') }}</div>
    </div>
</x-filament-panels::page>
