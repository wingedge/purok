<x-filament-panels::page>
    @php
        $grid = $this->grid();
        $weeks = $grid['weeks'];
        $members = $grid['members'];
    @endphp

    <form method="GET" class="fi-section mb-6">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content">
                <div class="purok-fi-filters purok-fi-filters-wide">
                    <label class="purok-fi-field">
                        <span class="purok-fi-label">Search Member</span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Name"
                            class="purok-fi-control"
                        >
                    </label>

                    <label class="purok-fi-field">
                        <span class="purok-fi-label">View</span>
                        <select name="view_type" class="purok-fi-control">
                            <option value="month" @selected($viewType === 'month')>Monthly</option>
                            <option value="year" @selected($viewType === 'year')>Full Year</option>
                        </select>
                    </label>

                    <label class="purok-fi-field">
                        <span class="purok-fi-label">Year</span>
                        <select name="year" class="purok-fi-control">
                            @foreach (range(now()->year + 1, now()->year - 5) as $y)
                                <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="purok-fi-field">
                        <span class="purok-fi-label">Month</span>
                        <select name="month" class="purok-fi-control" @disabled($viewType === 'year')>
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
                <div class="purok-grid-scroll">
                    <table class="purok-contribution-table">
                        <thead>
                            <tr>
                                <th class="purok-member-heading">
                                    Member / Total ({{ $year }})
                                </th>
                                @foreach ($weeks as $week)
                                    <th class="purok-week-heading">
                                        <span>{{ $week->format('M d') }}</span>
                                        <span class="purok-week-day">{{ $week->format('D') }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($members as $member)
                                <tr>
                                    <td class="purok-member-cell">
                                        <div class="purok-member-name">{{ $member->name }}</div>
                                        <div class="purok-member-total">
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
                                        <td class="purok-week-cell">
                                            <button
                                                type="button"
                                                wire:click="toggleContribution({{ $member->id }}, '{{ $weekString }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="toggleContribution({{ $member->id }}, '{{ $weekString }}')"
                                                @class([
                                                    'purok-toggle',
                                                    'purok-toggle-paid' => $contribution,
                                                ])
                                                aria-label="{{ $contribution ? 'Remove' : 'Record' }} contribution for {{ $member->name }} on {{ $week->format('M d, Y') }}"
                                            >
                                                {{ $contribution ? 'Paid' : 'Record' }}
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $weeks->count() + 1 }}" class="purok-empty">
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
