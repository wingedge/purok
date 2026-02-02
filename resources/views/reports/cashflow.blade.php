<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight print:hidden">
            {{ __('Cash Flow Statement') }}
        </h2>
    </x-slot>

    <style>
        @media print {
            /* Force the page to use smaller margins to fit more content */
            @page { margin: 0.5cm; }
            body { background-color: white !important; }
            .py-6 { padding-top: 0 !important; padding-bottom: 0 !important; }
            /* Hide UI elements */
            .print\:hidden { display: none !important; }
            /* Keep table colors visible */
            tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>

    <div class="py-4 sm:py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Filters: Hidden during Print --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-4 print:hidden">
                <form method="GET" class="flex flex-col md:flex-row md:items-end gap-3">
                    <div class="flex-1 grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Year</label>
                            <select name="year" class="w-full text-sm rounded-lg border-gray-300 shadow-sm">
                                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Month</label>
                            <select name="month" class="w-full text-sm rounded-lg border-gray-300 shadow-sm">
                                <option value="">Full Year</option>
                                @foreach(range(1,12) as $m)
                                    <option value="{{ $m }}" @selected($month == $m)>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-md">Apply</button>
                        <button type="button" onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm flex items-center shadow-md">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print
                        </button>
                    </div>
                </form>
            </div>

            {{-- Compact Print Header --}}
            <div class="hidden print:block text-center mb-4 pb-2 border-b border-gray-300">
                <h1 class="text-xl font-black uppercase tracking-tight">Purok Cash Flow Statement</h1>
                <p class="text-sm text-gray-600 font-medium">
                    For the Period of: {{ $month ? \Carbon\Carbon::createFromDate($year, (int)$month, 1)->format('F Y') : $year }}
                </p>
            </div>

            {{-- Compressed Summary Cards --}}
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="bg-white p-3 rounded-lg border border-gray-100 text-center">
                    <p class="text-[9px] font-bold text-gray-400 uppercase">Total Inflow</p>
                    <p class="text-lg font-black text-green-600">₱{{ number_format($incomeTotal + $contributionTotal, 2) }}</p>
                </div>
                <div class="bg-white p-3 rounded-lg border border-gray-100 text-center">
                    <p class="text-[9px] font-bold text-gray-400 uppercase">Total Outflow</p>
                    <p class="text-lg font-black text-rose-600">₱{{ number_format($expenseTotal, 2) }}</p>
                </div>
                <div class="bg-white p-3 rounded-lg border border-gray-200 text-center bg-gray-50">
                    <p class="text-[9px] font-bold text-gray-400 uppercase">Net Position</p>
                    <p class="text-lg font-black {{ $netCashFlow >= 0 ? 'text-green-600' : 'text-rose-600' }}">
                        ₱{{ number_format($netCashFlow, 2) }}
                    </p>
                </div>
            </div>

            {{-- Statement Table --}}
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 font-bold text-gray-700 uppercase text-xs" colspan="2">A. Cash Inflows</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-600">Cash on Hand / Incomes / Rentals / Donations / Funding</td>
                            <td class="px-4 py-2 text-right font-semibold">₱{{ number_format($incomeTotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-600">Member Contributions</td>
                            <td class="px-4 py-2 text-right font-semibold">₱{{ number_format($contributionTotal, 2) }}</td>
                        </tr>
                        <tr class="bg-rose-50/30">
                            <td class="px-4 py-2 font-bold text-gray-700 uppercase text-xs" colspan="2">B. Cash Outflows</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-600">Purok Expenses</td>
                            <td class="px-4 py-2 text-right font-semibold text-rose-600">({{ number_format($expenseTotal, 2) }})</td>
                        </tr>
                        <tr class="bg-gray-900 text-white font-bold">
                            <td class="px-4 py-3 uppercase text-xs tracking-wider">Net Cash Flow (A - B)</td>
                            <td class="px-4 py-3 text-right text-base">₱{{ number_format($netCashFlow, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Compact Signatories --}}
            <div class="mt-8 grid grid-cols-3 gap-12 px-2">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-6">Prepared By:</p>
                    <div class="border-b border-gray-800">
                        <p class="text-sm font-bold text-gray-900">&nbsp;</p>
                    </div>
                    <p class="text-[10px] text-gray-500 italic mt-1">Purok Treasurer</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-6">Audited By:</p>
                    <div class="border-b border-gray-800">
                        <p class="text-sm font-bold text-gray-900">&nbsp;</p>
                    </div>
                    <p class="text-[10px] text-gray-500 italic mt-1">Purok Auditor</p>
                </div>

                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-6">Approved By:</p>
                    <div class="border-b border-gray-800">
                        <p class="text-sm font-bold text-gray-900">&nbsp;</p>
                    </div>
                    <p class="text-[10px] text-gray-500 italic mt-1">Purok President</p>
                </div>
            </div>

            <p class="mt-6 text-center text-[9px] text-gray-400 italic">
                System Generated Report • {{ now()->format('M d, Y h:i A') }}
            </p>

            {{-- Developer Signature / System Stamp --}}
            <div class="mt-8 pt-4 border-t border-gray-100 flex justify-between items-center opacity-60 print:hidden">
                <div class="text-[8px] text-gray-400 uppercase tracking-widest font-semibold">
                    System Architecture by: <span class="text-gray-600">Francis Moreno</span>
                </div>
                <div class="text-[8px] text-gray-400 font-medium">
                    v1.0.1-stable
                </div>
            </div>

        </div>
    </div>
</x-app-layout>