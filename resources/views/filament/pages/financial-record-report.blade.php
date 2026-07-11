<x-filament-panels::page>
    @php
        $type = $this->reportType();
        $report = $this->report();
    @endphp

    <form method="GET" class="fi-section print:hidden">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content">
                <div class="purok-fi-filters" style="--purok-fi-filter-columns: 2;">
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
                                <option value="{{ $m }}" @selected($month === $m)>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="purok-fi-actions">
                        <button type="submit" class="fi-btn fi-size-md fi-color-primary">Apply</button>
                        <button type="button" onclick="window.print()" class="fi-btn fi-size-md fi-color-gray">Print</button>
                        <button type="button" wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="fi-btn fi-size-md fi-color-gray">
                            Generate Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="purok-print-area">
        <div class="purok-report-header">
            <div>
                <h2 class="purok-report-title">{{ $type->title() }}</h2>
                <p class="purok-report-period">Period: {{ $report['start']->format('M d, Y') }} - {{ $report['end']->format('M d, Y') }}</p>
            </div>
            <div class="purok-report-total">
                <span>Report Total</span>
                <strong>PHP {{ number_format($report['total'], 2) }}</strong>
            </div>
        </div>

        <div class="fi-section purok-report-section">
            <div class="fi-section-content-ctn">
                <div class="fi-section-content">
                    <div class="purok-report-scroll">
                        <table class="purok-report-table">
                            <thead>
                                <tr class="purok-report-band">
                                    <th>Date</th>
                                    <th>{{ $type->classificationLabel() }}</th>
                                    <th>Description</th>
                                    <th class="purok-report-amount">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($report['records'] as $record)
                                    <tr>
                                        <td>{{ $record->date->format('M d, Y') }}</td>
                                        <td>{{ $type === \App\Enums\FinancialReportType::Expense ? $record->category : $record->source }}</td>
                                        <td>{{ $record->description }}</td>
                                        <td class="purok-report-amount">PHP {{ number_format((float) $record->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="purok-empty">No records found for this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="purok-report-footer">Generated: {{ now()->format('Y-m-d H:i') }}</div>
    </div>
</x-filament-panels::page>
