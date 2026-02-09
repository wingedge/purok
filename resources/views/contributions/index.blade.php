<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Contributions</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-4 px-2 sm:px-6">
        
        {{-- Filters & Search Section --}}
        <div class="bg-white p-4 rounded-lg shadow-sm mb-4 border">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                
                {{-- Search Member --}}
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Search Member</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Name..." 
                        class="w-full border-gray-300 rounded text-sm shadow-sm focus:ring-blue-500">
                </div>

                {{-- View Type --}}
                <div class="w-32">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">View</label>
                    <select name="view_type" onchange="this.form.submit()" class="w-full border-gray-300 rounded text-sm shadow-sm focus:ring-blue-500">
                        <option value="month" @selected($viewType == 'month')>Monthly</option>
                        <option value="year" @selected($viewType == 'year')>Full Year</option>
                    </select>
                </div>

                {{-- Year Dropdown (Always Shown because year_total depends on it) --}}
                <div class="w-24">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Year</label>
                    <select name="year" class="w-full border-gray-300 rounded text-sm shadow-sm focus:ring-blue-500">
                        @foreach(range(now()->year + 1, now()->year - 5) as $y)
                            <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Month Dropdown (Only shown if view_type is 'month') --}}
                @if($viewType === 'month')
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Month</label>
                        <select name="month" class="w-full border-gray-300 rounded text-sm shadow-sm focus:ring-blue-500">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($selectedMonth == $m)>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex gap-2">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-xs font-bold uppercase tracking-wider transition-colors">
                        Apply
                    </button>
                    <a href="{{ route('contributions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded text-xs font-bold uppercase tracking-wider transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Main Table Container --}}
        <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
            <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                <table class="border-separate w-full border-spacing-0 mb-6">
                    <thead>
                        <tr class="bg-gray-50">
                            {{-- Frozen Column --}}
                            <th class="sticky left-0 z-30 bg-gray-100 border-b border-r px-3 py-3 text-left text-[10px] font-bold text-gray-600 uppercase tracking-tighter min-w-[140px] sm:min-w-[180px]">
                                Member / Total ({{ $selectedYear }})
                            </th>
                            @foreach($weeks as $week)
                                <th class="border-b px-2 py-3 text-center text-[10px] font-bold text-gray-500 uppercase min-w-[60px]">
                                    {{ $week->format('M d') }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($members as $member)
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="sticky left-0 z-10 bg-white border-r px-3 py-2 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
                                <div class="text-xs font-bold text-gray-800 truncate max-w-[120px] sm:max-w-none">
                                    {{ $member->name }}
                                </div>
                                <div class="text-[11px] text-blue-600 font-black member-total" data-member-id="{{ $member->id }}">
                                    {{ number_format($member->year_total ?? 0, 2) }}
                                </div>
                            </td>

                            @foreach($weeks as $week)
                                @php
                                    $weekString = $week->toDateString();
                                    $contribution = $member->contributions->first(fn($c) => \Carbon\Carbon::parse($c->week_start)->toDateString() === $weekString);
                                @endphp
                                <td class="px-1 py-2 text-center">
                                    <div class="contribution-container flex justify-center" 
                                         data-member-id="{{ $member->id }}" 
                                         data-week-start="{{ $weekString }}">
                                        
                                        <button type="button" 
                                                onclick="toggleContribution(this)"
                                                class="toggle-btn w-8 h-8 flex items-center justify-center rounded-md border transition-all {{ $contribution ? 'bg-green-500 text-white border-green-600' : 'bg-gray-50 text-transparent border-gray-100 hover:border-blue-300' }}">
                                            <svg class="w-4 h-4 {{ $contribution ? 'opacity-100' : 'opacity-0' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($weeks) + 1 }}" class="py-10 text-center text-gray-400 italic">No members found matching your search.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- The Javascript remains mostly the same as your original --}}
    <script>
        async function toggleContribution(button) {
            const container = button.closest('.contribution-container');
            const memberId = container.dataset.memberId;
            const weekStart = container.dataset.weekStart;
            const totalCell = document.querySelector(`.member-total[data-member-id="${memberId}"]`);
            
            const isChecked = button.classList.contains('bg-green-500');
            const url = isChecked ? "{{ route('contributions.destroy') }}" : "{{ route('contributions.store') }}";
            const method = isChecked ? 'DELETE' : 'POST';

            button.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({ 
                        member_id: memberId, 
                        week_start: weekStart, 
                        _method: method 
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const serverAmount = parseFloat(data.amount); 

                    if (isChecked) {
                        button.querySelector('svg').classList.replace('opacity-100', 'opacity-0');
                        button.className = "toggle-btn w-8 h-8 flex items-center justify-center rounded-md border bg-gray-50 text-transparent border-gray-100 transition-all";
                        updateTotal(totalCell, -serverAmount);
                    } else {
                        button.querySelector('svg').classList.replace('opacity-0', 'opacity-100');
                        button.className = "toggle-btn w-8 h-8 flex items-center justify-center rounded-md border bg-green-500 text-white border-green-600 transition-all";
                        updateTotal(totalCell, serverAmount);
                    }
                }
            } catch (error) {
                console.error("Error:", error);
            } finally {
                button.disabled = false;
            }
        }

        function updateTotal(cell, change) {
            let text = cell.innerText.replace(/,/g, '').replace(/[^\d.-]/g, '');
            let current = parseFloat(text) || 0;
            const newTotal = current + change;
            cell.innerText = newTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    </script>
</x-app-layout>