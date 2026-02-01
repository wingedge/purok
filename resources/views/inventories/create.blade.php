<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Inventory Item
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

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

            <div class="bg-white shadow rounded p-6">
                <form action="{{ route('inventories.store') }}" method="POST" class="space-y-4">
                    @csrf

                    @include('inventories.form')

                    <div class="flex space-x-3">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">
                            Save
                        </button>
                        <a href="{{ route('inventories.index') }}"
                           class="bg-gray-200 px-4 py-2 rounded">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
