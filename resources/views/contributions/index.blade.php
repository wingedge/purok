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
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json', // Force Laravel to send JSON errors
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({ 
                        member_id: memberId, 
                        week_start: weekStart, 
                        _method: method 
                    })
                });

                // Check if the response is valid JSON
                const contentType = response.headers.get("content-type");
                if (!response.ok || !contentType || !contentType.includes("application/json")) {
                    const errorText = await response.text();
                    console.error("Server Error Response:", errorText);
                    alert("Server error. Check the console for details.");
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    // Use the exact amount sent by the Controller
                    const serverAmount = parseFloat(data.amount); 

                    if (isChecked) {
                        // Turn Gray (Unchecked)
                        button.querySelector('svg').classList.replace('opacity-100', 'opacity-0');
                        button.className = "toggle-btn w-9 h-9 sm:w-8 sm:h-8 flex items-center justify-center rounded-md border bg-gray-50 text-transparent border-gray-200 hover:border-blue-300 transition-all";
                        updateTotal(totalCell, -serverAmount);
                    } else {
                        // Turn Green (Checked)
                        button.querySelector('svg').classList.replace('opacity-0', 'opacity-100');
                        button.className = "toggle-btn w-9 h-9 sm:w-8 sm:h-8 flex items-center justify-center rounded-md border bg-green-500 text-white border-green-600 transition-all";
                        updateTotal(totalCell, serverAmount);
                    }
                }
            } catch (error) {
                console.error("Network or Parsing Error:", error);
            } finally {
                button.disabled = false;
            }
        }

        function updateTotal(cell, change) {
            // 1. Remove commas and any non-numeric characters except the decimal point
            let text = cell.innerText.replace(/,/g, '').replace(/[^\d.-]/g, '');
            let current = parseFloat(text) || 0;
            
            const newTotal = current + change;
            
            // 2. Format back to standard number with commas
            cell.innerText = newTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        </script>
    </div>
</x-app-layout>