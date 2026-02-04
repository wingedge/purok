<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Rental
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded p-6">
                <form action="{{ route('rentals.update', $rental) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    @include('rentals.form', ['rental' => $rental, 'edit' => true])

                    <div class="flex space-x-3">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
                        <a href="{{ route('rentals.index') }}" class="bg-gray-200 px-4 py-2 rounded">Cancel</a>
                    </div>
                </form>                
            </div>

        </div>
    </div>
</x-app-layout>
