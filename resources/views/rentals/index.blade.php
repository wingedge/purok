<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rentals') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded mb-4 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Action Button --}}
            <div class="mb-6">
                <a href="{{ route('rentals.create') }}" class="inline-flex items-center justify-center w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    + New Rental
                </a>
            </div>

            {{-- Mobile View: Stacked Cards --}}
            <div class="grid grid-cols-1 gap-4 lg:hidden">
                @forelse($rentals as $rental)
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $rental->inventory->item_name }}</h3>
                                <p class="text-sm text-indigo-600 font-medium">Qty: {{ $rental->quantity }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wide uppercase
                                {{ $rental->status === 'rented' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                {{ $rental->status }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path></svg>
                                <span>{{ $rental->renter_name }}</span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500 ml-6">
                                {{ $rental->renter_contact }}
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                <span>{{ $rental->rent_date->format('M d, Y') }}</span>
                            </div>
                        </div>

                        <div class="flex space-x-3 pt-3 border-t border-gray-50">
                            <a href="{{ route('rentals.edit', $rental) }}" class="flex-1 text-center py-2 text-sm font-semibold text-yellow-700 bg-yellow-50 rounded-lg">Edit</a>
                            <form action="{{ route('rentals.destroy', $rental) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button class="w-full text-center py-2 text-sm font-semibold text-red-700 bg-red-50 rounded-lg" onclick="return confirm('Delete this rental?')">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 bg-white rounded-xl border-2 border-dashed border-gray-200 text-gray-500">
                        No active rentals found.
                    </div>
                @endforelse
            </div>

            {{-- Desktop View: Table --}}
            <div class="hidden lg:block bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Item</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Renter</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Rent Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($rentals as $rental)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $rental->inventory->item_name }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $rental->renter_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $rental->renter_contact }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $rental->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $rental->rent_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold tracking-tight
                                        {{ $rental->status === 'rented' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($rental->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('rentals.edit', $rental) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('rentals.destroy', $rental) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900 font-medium" onclick="return confirm('Delete this rental?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $rentals->links() }}
            </div>

        </div>
    </div>
</x-app-layout>