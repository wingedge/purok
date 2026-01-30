<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Edit Member
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto py-6">
        <form method="POST"
              action="{{ route('members.update', $member) }}"
              class="bg-white p-6 rounded shadow space-y-4">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input name="name" required
                       value="{{ old('name', $member->name) }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input name="phone"
                       value="{{ old('phone', $member->phone) }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input name="email" type="email"
                       value="{{ old('email', $member->email) }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            {{-- Birthday --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Birthday</label>
                <input type="date" name="birthday"
                       value="{{ old('birthday', $member->birthday?->format('Y-m-d')) }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            {{-- Indigent --}}
            <div class="flex items-center space-x-2">
                <input type="checkbox" name="indigent" value="1"
                       {{ old('indigent', $member->indigent) ? 'checked' : '' }}>
                <label class="text-sm text-gray-700">Indigent</label>
            </div>

            <hr>

            {{-- Dependents --}}
            <h3 class="font-semibold text-gray-700">Dependents</h3>

            @foreach($member->dependents as $index => $dependent)
                <div class="grid grid-cols-2 gap-2">
                    <input name="dependents[{{ $index }}][full_name]"
                           class="border px-2 py-1"
                           value="{{ $dependent->full_name }}"
                           placeholder="Dependent Name">

                    <input name="dependents[{{ $index }}][relationship]"
                           class="border px-2 py-1"
                           value="{{ $dependent->relationship }}"
                           placeholder="Relationship">
                </div>
            @endforeach

            {{-- Actions --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('members.index') }}"
                   class="px-4 py-2 bg-gray-500 text-white rounded">
                    Cancel
                </a>
                <button class="px-4 py-2 bg-green-600 text-white rounded">
                    Update Member
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
