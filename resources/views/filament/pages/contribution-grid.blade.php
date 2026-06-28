<x-filament-panels::page>
    @php
        $grid = $this->grid();
        $weeks = $grid['weeks'];
        $members = $grid['members'];
    @endphp

    <form method="GET" class="fi-section mb-6">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content">
                <div class="grid gap-4 md:grid-cols-[1fr_160px_120px_160px_auto] md:items-end">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Search Member</span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Name"
                            class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900"
                        >
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">View</span>
                        <select name="view_type" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <option value="month" @selected($viewType === 'month')>Monthly</option>
                            <option value="year" @selected($viewType === 'year')>Full Year</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Year</span>
                        <select name="year" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            @foreach (range(now()->year + 1, now()->year - 5) as $y)
                                <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Month</span>
                        <select name="month" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900" @disabled($viewType === 'year')>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($month === $m)>
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="flex gap-2">
                        <button type="submit" class="fi-btn fi-size-md fi-color-primary">
                            Apply
                        </button>
                        <a href="{{ url('/admin/contribution-grid') }}" class="fi-btn fi-size-md fi-color-gray">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="fi-section">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content p-0">
                <div class="max-h-[70vh] overflow-auto">
                    <table class="w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr>
                                <th class="sticky left-0 top-0 z-30 min-w-56 border-b border-r border-gray-200 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                    Member / Total ({{ $year }})
                                </th>
                                @foreach ($weeks as $week)
                                    <th class="sticky top-0 z-20 min-w-20 border-b border-gray-200 bg-gray-50 px-3 py-3 text-center text-xs font-semibold uppercase text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                        {{ $week->format('M d') }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($members as $member)
                                <tr>
                                    <td class="sticky left-0 z-10 border-r border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-950">
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $member->name }}</div>
                                        <div class="text-xs font-semibold text-primary-600">
                                            PHP {{ number_format((float) ($member->year_total ?? 0), 2) }}
                                        </div>
                                    </td>

                                    @foreach ($weeks as $week)
                                        @php
                                            $weekString = $week->toDateString();
                                            $contribution = $member->contributions->first(
                                                fn ($item) => $item->week_start->toDateString() === $weekString,
                                            );
                                        @endphp
                                        <td class="px-3 py-3 text-center">
                                            <button
                                                type="button"
                                                wire:click="toggleContribution({{ $member->id }}, '{{ $weekString }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="toggleContribution({{ $member->id }}, '{{ $weekString }}')"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-sm font-semibold transition {{ $contribution ? 'border-success-600 bg-success-500 text-white' : 'border-gray-200 bg-gray-50 text-transparent hover:border-primary-400 dark:border-gray-800 dark:bg-gray-900' }}"
                                                aria-label="{{ $contribution ? 'Remove' : 'Record' }} contribution for {{ $member->name }} on {{ $week->format('M d, Y') }}"
                                            >
                                                <span @class(['opacity-100' => $contribution, 'opacity-0' => ! $contribution])>✓</span>
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $weeks->count() + 1 }}" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No non-indigent members found for this filter.
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
