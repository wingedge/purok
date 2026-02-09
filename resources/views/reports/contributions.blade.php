<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Contribution Report</h2>            
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        
        {{-- Filter Panel (Hidden on Print) --}}
        <div class="bg-white p-4 rounded-lg shadow-sm border mb-6 print:hidden">
            <div class="flex flex-wrap gap-4">
                <div class="flex flex-wrap items-end gap-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">&nbsp;</label>
                    <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-2 rounded text-sm font-bold uppercase">
                        Print / Save PDF
                    </button>
                </div>
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Year</label>
                        <select name="year" class="border-gray-300 rounded text-sm">
                            @foreach(range(now()->year, now()->year - 3) as $y)
                                <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">From</label>
                        <select name="start_month" class="border-gray-300 rounded text-sm">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected(request('start_month') == $m)>{{ Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">To</label>
                        <select name="end_month" class="border-gray-300 rounded text-sm">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected(request('end_month', 12) == $m)>{{ Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded text-sm font-bold uppercase">Filter</button>
                    <a href="{{ route('reports.contributions') }}" class="text-gray-400 text-sm pb-2 underline">Reset</a>
                </form>
                
            </div>
        </div>

        {{-- Report Content --}}
        <div class="bg-white p-8 border shadow-sm rounded-lg overflow-x-auto print:shadow-none print:border-none print:p-0">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-black uppercase text-gray-900">Member Contributions</h1>
                <p class="text-gray-600">Period: {{ $start->format('M Y') }} — {{ $end->format('M Y') }}</p>
            </div>

            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="border-b-2 border-gray-800 px-2 py-3 text-left text-[10px] font-bold uppercase text-gray-600">Member</th>
                        @foreach($weeks as $week)
                            <th class="border-b-2 border-gray-800 px-1 py-3 text-center text-[9px] font-bold text-gray-500 rotate-[-45deg] sm:rotate-0">
                                {{ $week->format('M d') }}
                            </th>
                        @endforeach
                        <th class="border-b-2 border-gray-800 px-2 py-3 text-right text-[10px] font-bold uppercase text-gray-900 bg-gray-50">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($members as $member)
                        @php $memberTotal = 0; @endphp
                        <tr class="even:bg-gray-50/50">
                            <td class="px-2 py-2 text-xs font-bold text-gray-800 whitespace-nowrap border-r border-gray-100">{{ $member->name }}</td>
                            @foreach($weeks as $week)
                                @php
                                    $wStr = $week->toDateString();
                                    $contribution = $member->contributions->first(fn($c) => \Carbon\Carbon::parse($c->week_start)->toDateString() === $wStr);
                                    if($contribution) $memberTotal += $contribution->amount;
                                @endphp
                                <td class="px-1 py-2 text-center text-xs">
                                    @if($contribution)
                                        <span class="text-green-600 font-bold">&#10003;</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-2 py-2 text-right text-xs font-black text-gray-900 bg-gray-50 border-l border-gray-100">
                                {{ number_format($memberTotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-12 hidden print:flex justify-between text-[10px] text-gray-400 uppercase tracking-widest">
                <span>Generated: {{ now()->format('Y-m-d H:i') }}</span>
                <span>Authorized Signature: ___________________________</span>
            </div>
        </div>
    </div>

    <style>
        @media print {
            /* Force Landscape */
            @page { size: landscape; margin: 0.5cm; }
            body { background: white !important; }
            nav, .header-shadow, .print\:hidden { display: none !important; }
            .max-w-7xl { max-width: 100% !important; width: 100% !important; padding: 0 !important; }
            table { font-size: 8px; border-collapse: collapse !important; }
            th, td { border: 1px solid #e5e7eb !important; }
        }
    </style>
</x-app-layout>