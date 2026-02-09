<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reports
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <a href="{{ route('reports.cashflow') }}"
                   class="bg-white p-6 rounded shadow hover:bg-gray-50 transition">
                    <h3 class="text-lg font-semibold">Cash Flow Statement</h3>
                    <p class="text-sm text-gray-500">
                        View income, contributions, expenses and net cash flow
                    </p>
                </a>

                <a href="{{ route('reports.contributions') }}"
                   class="bg-white p-6 rounded shadow hover:bg-gray-50 transition">
                    <h3 class="text-lg font-semibold">Member Contributions</h3>
                    <p class="text-sm text-gray-500">
                        View member contributions
                    </p>
                </a>

                {{-- Future reports --}}
                {{--
                <a href="{{ route('reports.income') }}" class="bg-white p-6 rounded shadow">
                    Income Report
                </a>
                --}}
            </div>

        </div>
    </div>
</x-app-layout>
