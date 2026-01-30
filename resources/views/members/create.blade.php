<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Add Member
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto py-6">

        <form method="POST" action="{{ route('members.store') }}"
              class="bg-white p-6 rounded shadow space-y-4">
            @csrf

            <input name="full_name" class="w-full border rounded px-3 py-2"
                   placeholder="Full Name" required>

            <input name="phone" class="w-full border rounded px-3 py-2"
                   placeholder="Phone" required>

            <input name="email" class="w-full border rounded px-3 py-2"
                   placeholder="Email">

            <textarea name="address" class="w-full border rounded px-3 py-2"
                      placeholder="Address"></textarea>

            <h3 class="font-semibold">Dependents</h3>

            <div class="grid grid-cols-2 gap-2">
                <input name="dependents[0][full_name]" class="border px-2 py-1"
                       placeholder="Name">
                <input name="dependents[0][relationship]" class="border px-2 py-1"
                       placeholder="Relationship">
            </div>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">
                Save Member
            </button>
        </form>

    </div>
</x-app-layout>
