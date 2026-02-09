<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Update Expense
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto py-6">

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

        <form method="POST" action="{{ $expense->exists ? route('expenses.update', $expense) : route('expenses.store') }}">
            @csrf
            @if($expense->exists)
                @method('PUT')
            @endif

            <div class="mb-4">
                <label class="block text-gray-700">Date</label>
                <input type="date" name="date" value="{{ old('date', $expense->date?->format('Y-m-d')) }}"
                    class="border px-3 py-2 rounded w-full">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700">Category</label>
                <select name="category" class="border px-3 py-2 rounded w-full">
                    <option value="Misc">-- Not Specified --</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category }}"
                            {{ old('category', $expense->category ?? '') === $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Description</label>
                <textarea name="description" class="border px-3 py-2 rounded w-full">{{ old('description', $expense->description) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Amount</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}"
                    class="border px-3 py-2 rounded w-full">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                {{ $expense->exists ? 'Update Expense' : 'Add Expense' }}
            </button>
        </form>

    </div>
</x-app-layout>
