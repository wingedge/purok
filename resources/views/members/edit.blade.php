<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Edit Member
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto py-6">

        <form method="POST" action="{{ route('members.update', $member) }}"
              class="bg-white p-6 rounded shadow space-y-4">
            @csrf
            @method('PUT')

            <input name="full_name" class="w-full border rounded px-3 py-2"
                   value="{{ old('full_name', $member->full_name) }}" required>

            <input name="phone" class="w-full border rounded px-3 py-2"
                   value="{{ old('phone', $member->phone) }}" required>

            <input name="email" class="w-full border rounded px-3 py-2"
                   value="{{ old('email', $member->email) }}">

            <textarea name="address" class="w-full border rounded px-3 py-2">
                {{ old('address', $member->address) }}
            </textarea>

            <h3 class="font-semibold">Dependents</h3>

            @foreach($member->dependents as $index => $dep)
                <div class="grid grid-cols-2 gap-2">
                    <input name="dependents[{{ $index }}][full_name]"
                           class="border px-2 py-1"
                           value="{{ $dep->full_name }}">
                    <input name="dependents[{{ $index }}][relationship]"
                           class="border px-2 py-1"
                           value="{{ $dep->relationship }}">
                </div>
            @endforeach

            <button class="bg-green-600 text-white px-4 py-2 rounded">
                Update Member
            </button>
        </form>

    </div>
</x-app-layout>
