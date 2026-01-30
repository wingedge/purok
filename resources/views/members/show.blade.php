<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Member Details
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">

            <div class="bg-white shadow rounded p-6 space-y-4">

                {{-- Basic Info --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Basic Information</h3>
                    <div class="mt-2 space-y-1 text-gray-600">
                        <p><strong>Name:</strong> {{ $member->name }}</p>
                        <p><strong>Phone:</strong> {{ $member->phone ?? '—' }}</p>
                        <p><strong>Email:</strong> {{ $member->email ?? '—' }}</p>
                        <p><strong>Birthday:</strong>
                            {{ $member->birthday ? $member->birthday->format('F d, Y') : '—' }}
                        </p>
                        <p>
                            <strong>Indigent:</strong>
                            <span class="{{ $member->indigent ? 'text-red-600' : 'text-green-600' }}">
                                {{ $member->indigent ? 'Yes' : 'No' }}
                            </span>
                        </p>
                    </div>
                </div>

                <hr>

                {{-- Dependents --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Dependents</h3>

                    @if($member->dependents->count())
                        <ul class="mt-2 space-y-2">
                            @foreach($member->dependents as $dependent)
                                <li class="flex justify-between bg-gray-50 p-3 rounded">
                                    <span>{{ $dependent->name }}</span>
                                    <span class="text-gray-500">
                                        {{ $dependent->relationship ?? '—' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500 mt-2">No dependents recorded.</p>
                    @endif
                </div>

                <hr>

                {{-- Actions --}}
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('members.edit', $member) }}"
                       class="bg-yellow-500 text-white px-4 py-2 rounded">
                        Edit
                    </a>
                    <a href="{{ route('members.index') }}"
                       class="bg-gray-500 text-white px-4 py-2 rounded">
                        Back
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
