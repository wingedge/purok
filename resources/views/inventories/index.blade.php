<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Inventories
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Item Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Total Quantity
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Available Quantity
                            </th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($inventories as $inventory)
                            <tr>
                                <td class="px-6 py-4">{{ $inventory->item_name }}</td>
                                <td class="px-6 py-4">{{ $inventory->total_quantity }}</td>
                                <td class="px-6 py-4">{{ $inventory->available_quantity }}</td>
                                <td class="px-6 py-4 space-x-2">
                                    <a href="{{ route('inventories.edit', $inventory) }}"
                                       class="text-yellow-600 hover:underline">
                                        Edit
                                    </a>

                                    <form action="{{ route('inventories.destroy', $inventory) }}"
                                          method="POST"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline"
                                                onclick="return confirm('Delete this item?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 p-4">
                {{ $inventories->links() }}
            </div>

            <div class="mt-6 flex flex-col space-y-4 bg-white p-4 rounded shadow sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                <a href="{{ route('inventories.create') }}"
                   class="w-full sm:w-auto bg-blue-600 text-white px-4 py-3 rounded text-center font-medium">
                    + Add Inventory Item
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
