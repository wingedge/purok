<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rentals
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Renter</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rent Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($rentals as $rental)
                            <tr>
                                <td class="px-6 py-4">{{ $rental->inventory->item_name }}</td>
                                <td class="px-6 py-4">
                                    {{ $rental->renter_name }}<br>
                                    <span class="text-sm text-gray-500">{{ $rental->renter_contact }}</span>
                                </td>
                                <td class="px-6 py-4">{{ $rental->quantity }}</td>
                                <td class="px-6 py-4">{{ $rental->rent_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-sm
                                        {{ $rental->status === 'rented' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($rental->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 space-x-2">
                                    <a href="{{ route('rentals.edit', $rental) }}" class="text-yellow-600">Edit</a>
                                    <form action="{{ route('rentals.destroy', $rental) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600" onclick="return confirm('Delete this rental?')">
                                            Delete
                                        </button>
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

            <div class="mt-6 bg-white p-4 rounded shadow">
                <a href="{{ route('rentals.create') }}"
                   class="bg-blue-600 text-white px-4 py-3 rounded font-medium">
                    + New Rental
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
