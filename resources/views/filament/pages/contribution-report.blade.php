<x-filament-panels::page>
    @php
        $report = $this->report();
        $weeks = $report['weeks'];
        $members = $report['members'];
        $memberTotals = $report['memberTotals'];
        $start = $report['start'];
        $end = $report['end'];
    @endphp

    <form method="GET" class="fi-section mb-6 print:hidden">
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

    <div class="fi-section">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content">
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-semibold text-gray-950 dark:text-white">Member Contributions</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Period: {{ $start->format('M Y') }} - {{ $end->format('M Y') }}
                    </p>
                    <p class="mt-2 text-sm font-semibold text-primary-600">
                        Total Contributions: PHP {{ number_format($report['reportTotal'], 2) }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="border-b-2 border-gray-300 px-3 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:border-gray-700 dark:text-gray-300">Member</th>
                                @foreach ($weeks as $week)
                                    <th class="border-b-2 border-gray-300 px-2 py-3 text-center text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                        {{ $week->format('M d') }}
                                    </th>
                                @endforeach
                                <th class="border-b-2 border-gray-300 bg-gray-50 px-3 py-3 text-right text-xs font-semibold uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($members as $member)
                                <tr>
                                    <td class="whitespace-nowrap border-r border-gray-100 px-3 py-3 font-medium text-gray-950 dark:border-gray-800 dark:text-white">{{ $member->name }}</td>
                                    @foreach ($weeks as $week)
                                        @php
                                            $weekString = $week->toDateString();
                                            $contribution = $member->contributions->first(
                                                fn ($item) => $item->week_start->toDateString() === $weekString,
                                            );
                                        @endphp
                                        <td class="px-2 py-3 text-center">
                                            @if ($contribution)
                                                <span class="font-semibold text-success-600">Paid</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="border-l border-gray-100 bg-gray-50 px-3 py-3 text-right font-semibold text-gray-950 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                        PHP {{ number_format($memberTotals[$member->id] ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $weeks->count() + 2 }}" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
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
</x-filament-panels::page>
