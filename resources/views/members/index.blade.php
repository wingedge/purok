<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Members
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <a href="{{ route('members.create') }}"
               class="mb-4 inline-block bg-blue-600 text-white px-4 py-2 rounded">
                Add Member
            </a>

            <div class="bg-white shadow rounded">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3">Phone</th>
                            <th class="px-6 py-3">Dependents</th>
                            <th class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($members as $member)
                        <tr>
                            <td class="px-6 py-4">{{ $member->full_name }}</td>
                            <td class="px-6 py-4">{{ $member->phone }}</td>
                            <td class="px-6 py-4">{{ $member->dependents_count }}</td>
                            <td class="px-6 py-4 space-x-2">
                                <a href="{{ route('members.show', $member) }}" class="text-blue-600">View</a>
                                <a href="{{ route('members.edit', $member) }}" class="text-yellow-600">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
