<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inventories') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Status Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded mb-4 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Action Button --}}
            <div class="mb-6">
                <a href="{{ route('inventories.create') }}" class="inline-flex items-center justify-center w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Add Inventory Item
                </a>
            </div>

            {{-- Mobile View: Stacked Cards (Visible only on mobile) --}}
            <div class="grid grid-cols-1 gap-4 lg:hidden">
                @forelse ($inventories as $inventory)
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $inventory->item_name }}</h3>
                                <p class="text-xs text-gray-400">Total Stock: {{ $inventory->total_quantity }}</p>
                                <p class="text-xs text-gray-400">Rental Rate: {{ $inventory->rental_rate }}</p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $inventory->available_quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $inventory->available_quantity > 0 ? '● In Stock' : '● Out of Stock' }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                <p class="text-[10px] text-blue-500 uppercase font-bold">Available</p>
                                <p class="text-xl font-black text-blue-700">{{ $inventory->available_quantity }}</p>
                            </div>
                            <div class="bg-orange-50 p-3 rounded-lg border border-orange-100">
                                <p class="text-[10px] text-orange-500 uppercase font-bold">Rented</p>
                                <p class="text-xl font-black text-orange-700">{{ $inventory->total_quantity - $inventory->available_quantity }}</p>
                            </div>
                        </div>                       

                        <div class="flex space-x-3">
                            <a href="{{ route('inventories.edit', $inventory) }}" class="flex-1 text-center py-2 text-sm font-medium text-yellow-700 bg-yellow-50 rounded-lg border border-yellow-100">
                                Edit
                            </a>
                            <form action="{{ route('inventories.destroy', $inventory) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button class="w-full text-center py-2 text-sm font-medium text-red-700 bg-red-50 rounded-lg border border-red-100" onclick="return confirm('Delete this item?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-white rounded-xl border-2 border-dashed border-gray-300">
                        <p class="text-gray-500">No inventory items found.</p>
                    </div>
                @endforelse
            </div>

            {{-- Desktop View: Table (Hidden on mobile) --}}
            <div class="hidden lg:block bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Item Details</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Availability</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Rental Rate</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Action</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($inventories as $inventory)
                            @php
                                $rentedCount = $inventory->total_quantity - $inventory->available_quantity;
                                $percentage = $inventory->total_quantity > 0 
                                    ? ($inventory->available_quantity / $inventory->total_quantity) * 100 
                                    : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $inventory->item_name }}</div>
                                    <div class="text-xs text-gray-400 font-normal">ID: #{{ str_pad($inventory->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-600 mr-2">{{ $inventory->total_quantity }}</span>
                                        <div class="w-24 bg-gray-200 rounded-full h-1.5 hidden xl:block">
                                            <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ 100 - $percentage }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-2 items-center">
                                        {{-- Available Badge --}}
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold shadow-sm
                                            {{ $inventory->available_quantity <= ($inventory->total_quantity * 0.1) 
                                                ? 'bg-red-100 text-red-700 border border-red-200' 
                                                : 'bg-blue-100 text-blue-700 border border-blue-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $inventory->available_quantity <= ($inventory->total_quantity * 0.1) ? 'bg-red-500' : 'bg-blue-500' }}"></span>
                                            {{ $inventory->available_quantity }} Available
                                        </span>

                                        {{-- Rented Badge --}}
                                        @if($rentedCount > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200 shadow-sm">
                                                <svg class="w-3 h-3 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ $rentedCount }} Rented
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-700">
                                        ₱{{ number_format($inventory->rental_rate ?? 0, 2) }}
                                    </span>
                                </td>                               
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('inventories.edit', $inventory) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded">Edit</a>
                                    <form action="{{ route('inventories.destroy', $inventory) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900 px-3 py-1 rounded hover:bg-red-50" onclick="return confirm('Delete this item?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $inventories->links() }}
            </div>

        </div>
    </div>
</x-app-layout>