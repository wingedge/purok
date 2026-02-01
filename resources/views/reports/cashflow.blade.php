<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Cash Flow Statement
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            {{-- Filters --}}
            <div class="bg-white p-4 rounded shadow mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year</label>
                        <select name="year" class="mt-1 rounded border-gray-300">
                            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Month</label>
                        <select name="month" class="mt-1 rounded border-gray-300">
                            <option value="">All</option>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" @selected($month == $m)>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button class="bg-blue-600 text-white px-4 py-2 rounded">
                        Apply
                    </button>
                </form>
            </div>

            {{-- Cash Flow Table --}}
            <div class="flex justify-end mb-4 print:hidden">
                <button onclick="window.print()"
                        class="bg-gray-800 text-white px-4 py-2 rounded">
                    Print
                </button>
            </div>

            <div class="hidden print:block mb-6 text-center">
                <h1 class="text-2xl font-bold">Cash Flow Statement</h1>
                <p class="text-sm text-gray-600">
                    Period:
                    {{ $month
                        ? \Carbon\Carbon::create()->month($month)->format('F') . ' ' . $year
                        : $year
                    }}
                </p>
                <hr class="mt-4">
            </div>



            <div class="bg-white shadow rounded overflow-hidden">
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-200">

                        {{-- Inflows --}}
                        <tr class="bg-gray-50">
                            <td class="px-6 py-3 font-semibold" colspan="2">
                                Cash Inflows
                            </td>
                        </tr>

                        <tr>
                            <td class="px-6 py-3">Incomes</td>
                            <td class="px-6 py-3 text-right">
                                ₱{{ number_format($incomeTotal, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="px-6 py-3">Contributions</td>
                            <td class="px-6 py-3 text-right">
                                ₱{{ number_format($contributionTotal, 2) }}
                            </td>
                        </tr>

                        <tr class="font-semibold">
                            <td class="px-6 py-3">Total Cash In</td>
                            <td class="px-6 py-3 text-right">
                                ₱{{ number_format($incomeTotal + $contributionTotal, 2) }}
                            </td>
                        </tr>

                        {{-- Outflows --}}
                        <tr class="bg-gray-50">
                            <td class="px-6 py-3 font-semibold" colspan="2">
                                Cash Outflows
                            </td>
                        </tr>

                        <tr>
                            <td class="px-6 py-3">Expenses</td>
                            <td class="px-6 py-3 text-right text-red-600">
                                (₱{{ number_format($expenseTotal, 2) }})
                            </td>
                        </tr>

                        {{-- Net --}}
                        <tr class="bg-gray-100 font-bold text-lg">
                            <td class="px-6 py-4">Net Cash Flow</td>
                            <td class="px-6 py-4 text-right
                                {{ $netCashFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₱{{ number_format($netCashFlow, 2) }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
