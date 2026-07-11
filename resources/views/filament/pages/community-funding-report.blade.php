<x-filament-panels::page>
    @php
        $events = $this->events();
        $report = $this->report();
    @endphp

    <form method="GET" class="fi-section print:hidden">
        <div class="fi-section-content-ctn">
            <div class="fi-section-content">
                <div class="purok-fi-filters" style="--purok-fi-filter-columns: 1;">
                    <label class="purok-fi-field">
                        <span class="purok-fi-label">Community Funding Event</span>
                        <select name="event_id" class="purok-fi-control">
                            @forelse ($events as $event)
                                <option value="{{ $event->id }}" @selected($eventId === $event->id)>{{ $event->name }}</option>
                            @empty
                                <option value="">No community funding events available</option>
                            @endforelse
                        </select>
                    </label>

                    <div class="purok-fi-actions">
                        <button type="submit" class="fi-btn fi-size-md fi-color-primary" @disabled($events->isEmpty())>Apply</button>
                        <button type="button" onclick="window.print()" class="fi-btn fi-size-md fi-color-gray" @disabled($report === null)>Print</button>
                        <button type="button" wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="fi-btn fi-size-md fi-color-gray" @disabled($report === null)>
                            Generate Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if ($report)
        <div class="purok-print-area">
            <div class="purok-report-header">
                <div>
                    <h2 class="purok-report-title">Community Funding Report</h2>
                    <p class="purok-report-period">Event: {{ $report['event']->name }}</p>
                    @if ($report['event']->description)
                        <p class="purok-report-period">{{ $report['event']->description }}</p>
                    @endif
                    <p class="purok-report-period">Deadline: {{ $report['event']->deadline?->format('M d, Y') ?? 'None' }}</p>
                </div>
                <div class="purok-report-total">
                    <span>Total Collected</span>
                    <strong>PHP {{ number_format($report['collectedAmount'], 2) }}</strong>
                </div>
            </div>

            <div class="purok-stat-grid">
                <div class="purok-stat-card">
                    <p class="purok-stat-label">Goal Amount</p>
                    <p class="purok-stat-value">PHP {{ number_format($report['goalAmount'], 2) }}</p>
                </div>
                <div class="purok-stat-card">
                    <p class="purok-stat-label">Remaining</p>
                    <p class="purok-stat-value">PHP {{ number_format($report['remainingAmount'], 2) }}</p>
                </div>
                <div class="purok-stat-card">
                    <p class="purok-stat-label">Progress</p>
                    <p class="purok-stat-value purok-stat-value-success">{{ number_format($report['progressPercentage'], 2) }}%</p>
                    <p class="purok-stat-note">{{ $report['donorCount'] }} unique donor{{ $report['donorCount'] === 1 ? '' : 's' }}</p>
                </div>
            </div>

            <div class="fi-section purok-report-section">
                <div class="fi-section-content-ctn">
                    <div class="fi-section-content">
                        <div class="purok-report-scroll">
                            <table class="purok-report-table">
                                <thead>
                                    <tr class="purok-report-band">
                                        <th>Date Received</th>
                                        <th>Member</th>
                                        <th>Remarks</th>
                                        <th class="purok-report-amount">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report['donations'] as $donation)
                                        <tr>
                                            <td>{{ $donation->received_at->format('M d, Y') }}</td>
                                            <td>{{ $donation->member?->name }}</td>
                                            <td>{{ $donation->remarks ?: '—' }}</td>
                                            <td class="purok-report-amount">PHP {{ number_format((float) $donation->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="purok-empty">No donations recorded for this event.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="purok-report-footer">Generated: {{ now()->format('Y-m-d H:i') }}</div>
        </div>
    @else
        <div class="fi-section"><div class="fi-section-content-ctn"><div class="fi-section-content purok-empty">Create a community funding event before generating this report.</div></div></div>
    @endif
</x-filament-panels::page>
