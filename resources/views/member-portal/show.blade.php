<x-app-layout>
    <x-slot name="header">
        Member Profile
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
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
                    <h2 class="text-lg uppercase font-semibold text-gray-900">{{ $member->name }}</h2>
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
                           required
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

            <section class="bg-white p-6 rounded shadow space-y-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Contribution Status</h2>
                        <p class="mt-1 text-sm text-gray-500">Review your full recorded contribution history.</p>
                    </div>

                    <form method="GET" action="{{ route('member.portal.show') }}" class="flex flex-wrap items-end gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Year</label>
                            <select name="year" class="mt-1 border rounded px-8 py-2 text-sm">
                                @foreach (range(now()->year + 1, now()->year - 5) as $year)
                                    <option value="{{ $year }}" @selected($contributionStatus['selected_year'] === $year)>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Month</label>
                            <select name="month" class="mt-1 border rounded px-8 py-2 text-sm">
                                <option value="" @selected($contributionStatus['selected_month'] === null)>All months</option>
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ $month }}" @selected($contributionStatus['selected_month'] === $month)>
                                        {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm">
                            Apply
                        </button>
                    </form>
                </div>

                <div class="grid gap-3 sm:grid-cols-4">
                    <div class="rounded border bg-gray-50 p-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Year Total</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            {{ number_format($contributionStatus['year_total'], 2) }}
                        </div>
                    </div>

                    <div class="rounded border bg-gray-50 p-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Month Paid</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            {{ number_format($contributionStatus['monthly_paid_total'], 2) }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">{{ $contributionStatus['period_label'] }}</div>
                    </div>

                    <div class="rounded border bg-gray-50 p-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Unpaid Weeks</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            {{ $contributionStatus['unpaid_weeks'] }}
                        </div>
                    </div>

                    {{-- <div class="rounded border bg-gray-50 p-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Balance</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            {{ number_format($contributionStatus['monthly_balance'], 2) }}
                        </div>
                    </div> --}}
                </div>

                @if ($contributionStatus['required_weekly_amount'] <= 0)
                    <div class="rounded border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                        No weekly contribution is currently required for your member record.
                    </div>
                @endif

                <div class="overflow-x-auto border rounded">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Week Start</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($contributionStatus['weekly_status'] as $week)
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $week['week_start']->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($week['is_paid'])
                                            <span class="rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">Paid</span>
                                        @elseif ($contributionStatus['required_weekly_amount'] <= 0)
                                            <span class="rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">Not Required</span>
                                        @else
                                            <span class="rounded bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-700">
                                        {{ number_format($week['contribution']?->amount ?? 0, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800">Contribution History</h3>

                    @if ($contributionStatus['filtered_contributions']->isEmpty())
                        <p class="mt-2 text-sm text-gray-500">No contributions match the selected filters.</p>
                    @else
                        <div class="mt-3 overflow-x-auto border rounded">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Week Start</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($contributionStatus['filtered_contributions'] as $contribution)
                                        <tr>
                                            <td class="px-4 py-3 text-gray-700">
                                                {{ $contribution->week_start->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-gray-700">
                                                {{ number_format($contribution->amount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            <section class="bg-white p-6 rounded shadow space-y-5">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Password</h2>
                    <p class="mt-1 text-sm text-gray-500">Change your portal login password.</p>
                </div>

                @if (session('status') === 'password-updated')
                    <div class="rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        Password updated.
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input name="current_password"
                               type="password"
                               autocomplete="current-password"
                               class="mt-1 w-full border rounded px-3 py-2">
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">New Password</label>
                        <input name="password"
                               type="password"
                               autocomplete="new-password"
                               class="mt-1 w-full border rounded px-3 py-2">
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input name="password_confirmation"
                               type="password"
                               autocomplete="new-password"
                               class="mt-1 w-full border rounded px-3 py-2">
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
                        <button class="px-4 py-2 bg-green-600 text-white rounded">
                            Update Password
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>
</x-app-layout>
