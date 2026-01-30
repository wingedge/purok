<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Expenses
        </h2>
    </x-slot>

    

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">      
            
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
                        
            <div class="bg-white shadow rounded p-4 sm:p-0">

                

                <div class="bg-white shadow rounded overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($expenses as $expense)
                            <tr>
                                <td class="px-6 py-4">{{ $expense->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4">{{ $expense->category }}</td>
                                <td class="px-6 py-4">{{ $expense->description }}</td>
                                <td class="px-6 py-4">{{ number_format($expense->amount, 2) }}</td>
                                <td class="px-6 py-4">{{ $expense->creator->name }}</td>
                                <td class="px-6 py-4 space-x-2">
                                    <a href="{{ route('expenses.edit', $expense) }}" class="text-yellow-600">Edit</a>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600" onclick="return confirm('Delete this expense?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
        
            </div>

            <div class="mt-6 p-4">
                {{ $expenses->links() }}
            </div>

            <div class="mt-6 flex flex-col space-y-4 bg-white p-4 rounded shadow sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                <a href="{{ route('expenses.create') }}" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-3 rounded text-center font-medium">
                    + Add Expense
                </a>               
            </div>

        </div>
    </div>
</x-app-layout>
