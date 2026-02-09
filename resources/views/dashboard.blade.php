<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Purok Financial Report
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filters --}}
            <div class="bg-white p-4 rounded shadow mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year</label>
                        <select name="year" class="mt-1 rounded border-gray-300">
                            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" @selected($year == $y)>
                                    {{ $y }}
                                </option>
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
                        Filter
                    </button>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Total Members -->
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="text-sm text-gray-500">Total Purok Members</h3>
                    <p class="text-xl font-bold">
                        {{ $totalMembers }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">Current Funds</p>
                    <p class="text-2xl font-bold text-green-600">
                        ₱{{ number_format($totalFunds, 2) }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">Total Income</p>
                    <p class="text-xl font-semibold">
                        ₱{{ number_format($totalIncomes + $totalContributions, 2) }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">Total Expenses</p>
                    <p class="text-xl font-semibold text-red-600">
                        ₱{{ number_format($totalExpenses, 2) }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">Members Contributed</p>
                    <p class="text-xl font-semibold">
                        {{ $contributorsCount }}
                    </p>
                </div>
                <div class="bg-white p-6 rounded shadow">                    
                    <p class="text-sm text-gray-500">
                        Total Contributions ({{ $year }})
                    </p>
                    <p class="text-xl font-semibold">
                        {{ number_format($thisYearContributions, 2) }}
                    </p>
                </div>
                
                
                @if($year == now()->year)
                    <div class="bg-white p-6 rounded shadow">                        
                        <p class="text-sm text-gray-500">
                            Contributions Collected on the last 7 Days
                        </p>
                        <p class="text-xl font-semibold">
                            {{ number_format($recentContributions, 2) }}
                        </p>                    
                    </div>
                @endif

                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">Total Rentals</p>
                    <p class="text-xl font-semibold">
                        {{ $totalRentals }}
                    </p>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
