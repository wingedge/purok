<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Add Member
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto py-6">
        <form method="POST"
              action="{{ route('members.store') }}"
              class="bg-white p-6 rounded shadow space-y-4">
            @csrf

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input name="name" required
                       value="{{ old('name') }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input name="phone"
                       value="{{ old('phone') }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input name="email" type="email"
                       value="{{ old('email') }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            {{-- Birthday --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Birthday</label>
                <input type="date" name="birthday"
                       value="{{ old('birthday') }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            {{-- Indigent --}}
            <div class="flex items-center space-x-2">
                <input type="checkbox" name="indigent" value="1"
                       {{ old('indigent') ? 'checked' : '' }}>
                <label class="text-sm text-gray-700">Indigent</label>
            </div>

            <hr>

            {{-- Dependents --}}
            <h3 class="font-semibold text-gray-700">Dependents</h3>

            <div class="grid grid-cols-2 gap-2">
                <input name="dependents[0][full_name]"
                       class="border px-2 py-1"
                       placeholder="Dependent Name">

                <input name="dependents[0][relationship]"
                       class="border px-2 py-1"
                       placeholder="Relationship">
            </div>

            {{-- Actions --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('members.index') }}"
                   class="px-4 py-2 bg-gray-500 text-white rounded">
                    Cancel
                </a>
                <button class="px-4 py-2 bg-blue-600 text-white rounded">
                    Save Member
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
