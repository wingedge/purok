<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Contributions
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-4 px-2 sm:px-6">

        {{-- Condensed Filters --}}
        @php
            $currentYear = now()->year;
            $selectedMonth = \Carbon\Carbon::parse($month)->month;
        @endphp

        <div class="flex items-center justify-between mb-4 px-2">
            <form method="GET" class="flex items-center gap-2">
                <select name="month" class="border-gray-300 rounded text-xs py-1 shadow-sm focus:ring-blue-500">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $currentYear }}-{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" @selected($selectedMonth == $m)>
                            {{ \Carbon\Carbon::create()->month($m)->format('M') }}
                        </option>
                    @endforeach
                </select>
                <button class="bg-blue-600 text-white px-3 py-1 rounded text-xs font-bold uppercase tracking-wider">
                    Go
                </button>
            </form>
            <div class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">
                {{ \Carbon\Carbon::parse($month)->format('F Y') }}
            </div>
        </div>

        {{-- Compact Table --}}
        <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                {{-- Wrap the table in a container that doesn't force it to stretch to 100% --}}
                <div class="flex justify-start sm:justify-center overflow-x-auto">
                    <div class="inline-block min-w-full overflow-hidden">
                        <table class="border-separate min-w-full border-spacing-0">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="sticky left-0 z-20 bg-gray-50 border-b border-r px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase tracking-tighter sm:min-w-[150px]">
                                        Member / Total
                                    </th>
                                    @foreach($weeks as $week)
                                        <th class="border-b px-2 py-2 text-center text-[10px] font-bold text-gray-500 uppercase w-[70px] sm:w-[80px]">
                                            {{ $week->format('M d') }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($members as $member)
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="sticky left-0 z-10 bg-white border-r px-3 py-2 shadow-[1px_0_3px_rgba(0,0,0,0.05)] sm:min-w-[150px]">
                                        <div class="text-xs font-bold text-gray-800 truncate max-w-[100px] sm:max-w-none">
                                            {{ $member->name }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            {{-- Display current year label --}}
                                            {{-- <span class="text-[9px] text-gray-400 uppercase font-black">{{ $currentYear }} Total:</span> --}}
                                            <div class="text-[10px] text-gray-400 uppercase font-black member-total" data-member-id="{{ $member->id }}">
                                                {{ number_format($member->year_total ?? 0, 2) }}
                                            </div>
                                            
                                        </div>
                                    </td>

                                    @foreach($weeks as $week)
                                        @php
                                            $weekString = $week->toDateString();
                                            $contribution = $member->contributions->first(fn($c) => \Carbon\Carbon::parse($c->week_start)->toDateString() === $weekString);
                                        @endphp
                                        <td class="px-2 py-2 text-center">
                                            <div class="contribution-container flex justify-center" 
                                                data-member-id="{{ $member->id }}" 
                                                data-week-start="{{ $weekString }}">
                                                
                                                <button type="button" 
                                                        onclick="toggleContribution(this)"
                                                        class="toggle-btn w-9 h-9 sm:w-8 sm:h-8 flex items-center justify-center rounded-md border transition-all {{ $contribution ? 'bg-green-500 text-white border-green-600' : 'bg-gray-50 text-transparent border-gray-200 hover:border-blue-300' }}">
                                                    <svg class="w-5 h-5 {{ $contribution ? 'opacity-100' : 'opacity-0' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

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
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ member_id: memberId, week_start: weekStart, amount: 100, _method: method })
                });

                if (response.ok) {
                    if (isChecked) {
                        button.querySelector('svg').classList.replace('opacity-100', 'opacity-0');
                        button.className = "toggle-btn w-9 h-9 sm:w-8 sm:h-8 flex items-center justify-center rounded-md border bg-gray-50 text-transparent border-gray-200 hover:border-blue-300 transition-all";
                        updateTotal(totalCell, -100);
                    } else {
                        button.querySelector('svg').classList.replace('opacity-0', 'opacity-100');
                        button.className = "toggle-btn w-9 h-9 sm:w-8 sm:h-8 flex items-center justify-center rounded-md border bg-green-500 text-white border-green-600 transition-all";
                        updateTotal(totalCell, 100);
                    }
                }
            } catch (error) {
                console.error(error);
            } finally {
                button.disabled = false;
            }
        }

        function updateTotal(cell, change) {
            // Strips the $ and commas to do math, then puts them back
            let current = parseFloat(cell.innerText.replace(/[PHP,]/g, ''));
            const newTotal = current + change;
            cell.innerText = 'PHP' + newTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        </script>
    </div>
</x-app-layout>