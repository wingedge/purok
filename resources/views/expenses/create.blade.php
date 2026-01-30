<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Add Member
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

        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700">Date</label>
                <input type="date" name="date" value="{{ old('date') }}"
                    class="border px-3 py-2 rounded w-full">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Category</label>
                <input type="text" name="category" value="{{ old('category') }}"
                    class="border px-3 py-2 rounded w-full">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Description</label>
                <textarea name="description" class="border px-3 py-2 rounded w-full">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Amount</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}"
                    class="border px-3 py-2 rounded w-full">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                Add Expense
            </button>
        </form>


    </div>
</x-app-layout>
