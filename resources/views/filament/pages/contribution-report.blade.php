<x-filament-panels::page>
    @php
        $report = $this->report();
        $weeks = $report['weeks'];
        $members = $report['members'];
        $memberTotals = $report['memberTotals'];
        $start = $report['start'];
        $end = $report['end'];
    @endphp

    <form method="GET" class="fi-section print:hidden">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content">
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
                        <span class="purok-fi-label">From</span>
                        <select name="start_month" class="purok-fi-control">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($startMonth === $m)>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="purok-fi-field">
                        <span class="purok-fi-label">To</span>
                        <select name="end_month" class="purok-fi-control">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($endMonth === $m)>
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
                <h2 class="purok-report-title">Member Contributions</h2>
                <p class="purok-report-period">
                    Period: {{ $start->format('M Y') }} - {{ $end->format('M Y') }}
                </p>
            </div>
            <div class="purok-report-total">
                <span>Report Total</span>
                <strong>Total Contributions: PHP {{ number_format($report['reportTotal'], 2) }}</strong>
            </div>
        </div>

        <div class="fi-section purok-report-section">
            <div class="fi-section-content-ctn">
                <div class="fi-section-content">
                    <div class="purok-report-scroll">
                        <table class="purok-contribution-report-table">
                            <thead>
                                <tr>
                                    <th class="purok-report-member-heading">Member</th>
                                    @foreach ($weeks as $week)
                                        <th class="purok-report-week-heading">
                                            {{ $week->format('M d') }}
                                        </th>
                                    @endforeach
                                    <th class="purok-report-total-heading">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($members as $member)
                                    <tr>
                                        <td class="purok-report-member-cell">{{ $member->name }}</td>
                                        @foreach ($weeks as $week)
                                            @php
                                                $weekString = $week->toDateString();
                                                $contribution = $member->contributions->first(
                                                    fn ($item) => $item->week_start->toDateString() === $weekString,
                                                );
                                            @endphp
                                            <td class="purok-report-week-cell">
                                                @if ($contribution)
                                                    <span class="purok-paid-badge">Paid</span>
                                                @else
                                                    <span class="purok-unpaid-mark">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="purok-report-total-cell">
                                            PHP {{ number_format($memberTotals[$member->id] ?? 0, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $weeks->count() + 2 }}" class="purok-empty">
                                            No contribution records found for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="purok-report-footer">
            Generated: {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>
</x-filament-panels::page>
