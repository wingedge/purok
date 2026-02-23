<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Incomes and Donations') }}
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

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded mb-4 shadow-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Action Button (Moved to top for better mobile UX) --}}
            <div class="mb-6">
                <a href="{{ route('incomes.create') }}" class="inline-flex items-center justify-center w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Income
                </a>
            </div>

            {{-- Mobile View: Stacked Cards (Visible only on mobile) --}}
            <div class="grid grid-cols-1 gap-4 lg:hidden">
                @foreach ($incomes as $income)
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-400 tracking-wider">{{ $income->date->format('M d, Y') }}</p>
                                <p class="text-lg font-bold text-gray-900">{{ $income->description }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-indigo-600">₱{{ number_format($income->amount, 2) }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">{{ $income->source }}</p>
                        
                        <div class="flex border-t pt-3 mt-3 space-x-4">
                            <a href="{{ route('incomes.edit', $income) }}" class="flex-1 text-center py-2 text-sm font-medium text-yellow-700 bg-yellow-50 rounded-lg">Edit</a>
                            <form action="{{ route('incomes.destroy', $income) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button class="w-full text-center py-2 text-sm font-medium text-red-700 bg-red-50 rounded-lg" onclick="return confirm('Delete this income?')">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop View: Table (Hidden on mobile) --}}
            <div class="hidden lg:block bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Source</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>                             
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($incomes as $income)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $income->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $income->description }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $income->source }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">{{ number_format($income->amount, 2) }}</td>                                
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('incomes.edit', $income) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('incomes.destroy', $income) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this income?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $incomes->links() }}
            </div>

        </div>
    </div>
</x-app-layout>