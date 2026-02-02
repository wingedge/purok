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
                            <h3 class="text-lg font-bold text-gray-900">{{ $inventory->item_name }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $inventory->available_quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $inventory->available_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4 bg-gray-50 p-3 rounded-lg">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Total</p>
                                <p class="text-sm font-semibold text-gray-700">{{ $inventory->total_quantity }} units</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Available</p>
                                <p class="text-sm font-bold text-blue-600">{{ $inventory->available_quantity }} units</p>
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
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Qty</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Available Qty</th>                             
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($inventories as $inventory)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $inventory->item_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $inventory->total_quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $inventory->available_quantity > 0 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $inventory->available_quantity }}
                                    </span>
                                </td>                                
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('inventories.edit', $inventory) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                    <form action="{{ route('inventories.destroy', $inventory) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this item?')">Delete</button>
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