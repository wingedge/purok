<x-app-layout>
    <x-slot name="header">
        Member Profile
    </x-slot>

    <div class="max-w-3xl mx-auto">
        @if (session('success'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($member === null)
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-lg font-semibold text-gray-900">Account not linked</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Your login is active, but it is not linked to a member record yet. Please contact the purok staff to complete your account setup.
                </p>
            </div>
        @else
            @php
                $dependentRows = old('dependents', $member->dependents
                    ->map(fn ($dependent) => [
                        'name' => $dependent->name,
                        'relationship' => $dependent->relationship,
                    ])
                    ->values()
                    ->all());

                if ($dependentRows === []) {
                    $dependentRows = [['name' => '', 'relationship' => '']];
                }
            @endphp

            <form method="POST"
                  action="{{ route('member.portal.update') }}"
                  class="bg-white p-6 rounded shadow space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $member->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">Update your contact information and household dependents.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input name="phone"
                           value="{{ old('phone', $member->phone) }}"
                           class="mt-1 w-full border rounded px-3 py-2">
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input name="email"
                           type="email"
                           value="{{ old('email', $member->email) }}"
                           class="mt-1 w-full border rounded px-3 py-2">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Birthday</label>
                    <input name="birthday"
                           type="date"
                           value="{{ old('birthday', $member->birthday?->format('Y-m-d')) }}"
                           class="mt-1 w-full border rounded px-3 py-2">
                    <x-input-error :messages="$errors->get('birthday')" class="mt-2" />
                </div>

                <div class="border-t pt-5"
                     x-data="{ dependents: @js($dependentRows) }">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Dependents</h3>
                        <button type="button"
                                @click="dependents.push({ name: '', relationship: '' })"
                                class="text-sm bg-blue-600 text-white px-3 py-1 rounded">
                            + Add
                        </button>
                    </div>

                    <div class="mt-3 space-y-3">
                        <template x-for="(dependent, index) in dependents" :key="index">
                            <div class="grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                                <input type="text"
                                       class="border px-3 py-2 rounded"
                                       :name="`dependents[${index}][name]`"
                                       x-model="dependent.name"
                                       placeholder="Dependent name">

                                <input type="text"
                                       class="border px-3 py-2 rounded"
                                       :name="`dependents[${index}][relationship]`"
                                       x-model="dependent.relationship"
                                       placeholder="Relationship">

                                <button type="button"
                                        @click="dependents.splice(index, 1)"
                                        x-show="dependents.length > 1"
                                        class="bg-red-600 text-white px-3 py-2 rounded">
                                    X
                                </button>
                            </div>
                        </template>
                    </div>

                    <x-input-error :messages="$errors->get('dependents.*.name')" class="mt-2" />
                    <x-input-error :messages="$errors->get('dependents.*.relationship')" class="mt-2" />
                </div>

                <div class="flex justify-end">
                    <button class="px-4 py-2 bg-green-600 text-white rounded">
                        Save Changes
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
