<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Purok Certificate Logs') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded mb-4 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Action & Search Bar --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <a href="{{ route('purok_certificates.create') }}" class="inline-flex items-center justify-center w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Log Entry
                </a>

                <form method="GET" action="{{ route('purok_certificates.index') }}" class="flex w-full md:max-w-sm gap-2">
                    <x-text-input name="search" value="{{ request('search') }}" placeholder="Search name or dependent..." class="flex-1 shadow-sm" />
                    <x-primary-button class="shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </x-primary-button>
                </form>
            </div>

            {{-- Mobile View: Stacked Cards (Visible only on small screens) --}}
            <div class="grid grid-cols-1 gap-4 lg:hidden">
                @forelse($requests as $log)
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                        <div class="mb-3">
                            <h3 class="text-lg font-bold text-gray-900">{{ $log->member->name }}</h3>
                            @if($log->member->dependents->isNotEmpty())
                                <p class="text-xs text-indigo-600 font-medium italic">
                                    Deps: {{ $log->member->dependents->pluck('name')->implode(', ') }}
                                </p>
                            @endif
                        </div>

                        <div class="space-y-3 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>{{ \Carbon\Carbon::parse($log->request_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-start text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="flex-1 italic">"{{ $log->purpose }}"</span>
                            </div>
                        </div>

                        {{-- Optional: Add buttons for Edit/Delete here if needed later --}}

                        {{-- Inside Mobile View Loop --}}
                        <div class="flex space-x-3 pt-3 border-t border-gray-100 mt-4">
                            <a href="{{ route('purok_certificates.edit', $log) }}" class="flex-1 text-center py-2 text-sm font-semibold text-indigo-700 bg-indigo-50 rounded-lg">Edit</a>
                            <form action="{{ route('purok_certificates.destroy', $log) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button class="w-full text-center py-2 text-sm font-semibold text-red-700 bg-red-50 rounded-lg" onclick="return confirm('Delete this log?')">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 bg-white rounded-xl border-2 border-dashed border-gray-200 text-gray-500">
                        No certificate logs found.
                    </div>
                @endforelse
            </div>

            {{-- Desktop View: Table (Hidden on small screens, visible on LG upwards) --}}
            <div class="hidden lg:block bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Member / Household</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Purpose</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($requests as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $log->member->name }}</div>
                                    <div class="text-xs text-gray-500 italic">
                                        {{ $log->member->dependents->pluck('name')->implode(', ') ?: 'No dependents listed' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                    {{ $log->purpose }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($log->request_date)->format('M d, Y') }}
                                </td>
                                {{-- Inside Desktop View Table Body --}}
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('purok_certificates.edit', $log) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('purok_certificates.destroy', $log) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this log?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $requests->links() }}
            </div>

        </div>
    </div>
</x-app-layout>