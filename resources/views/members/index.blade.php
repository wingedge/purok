<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Members') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">      
            
            {{-- Success Message --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded mb-4 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <a href="{{ route('members.create') }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Add Member
                </a>

                {{-- Optional: Keep the Import Button clean if you uncomment it --}}
                {{-- <div class="flex items-center">
                    <button class="text-sm font-medium text-gray-600 hover:text-indigo-600 underline">Import from CSV</button>
                </div> --}}
            </div>

            {{-- Mobile View: Stacked Cards --}}
            <div class="grid grid-cols-1 gap-4 sm:hidden">
                @forelse($members as $member)
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden">
                        {{-- Indigent Indicator Stripe --}}
                        @if($member->indigent)
                            <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                        @endif

                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-base font-bold text-gray-900">{{ $member->name }}</div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Dependents: {{ $member->dependents_count }}</p>
                                </div>
                            </div>
                            @if($member->indigent)
                                <span class="bg-red-100 text-red-700 text-[10px] font-black px-2 py-1 rounded uppercase tracking-tighter">Indigent</span>
                            @endif
                        </div>
                        
                        <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                            <div class="bg-gray-50 p-2 rounded">
                                <p class="text-[10px] text-gray-400 uppercase font-bold">Birthday</p>
                                <p class="text-gray-700">{{ $member->birthday?->format('M d, Y') ?? '—' }}</p>
                            </div>
                            <div class="bg-gray-50 p-2 rounded text-right">
                                <p class="text-[10px] text-gray-400 uppercase font-bold">Age</p>
                                <p class="text-gray-700">{{ $member->birthday ? $member->birthday->age : '—' }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-4 pt-3 border-t border-gray-50">
                            <a href="{{ route('members.show', $member) }}" class="text-indigo-600 text-sm font-bold px-3 py-1">View</a>
                            <a href="{{ route('members.edit', $member) }}" class="text-yellow-600 text-sm font-bold px-3 py-1 border border-yellow-100 rounded-lg bg-yellow-50">Edit</a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-8 text-center rounded-xl border-2 border-dashed border-gray-200 text-gray-500">
                        No members found in the records.
                    </div>
                @endforelse
            </div>

            {{-- Desktop View: Table --}}
            <div class="hidden sm:block bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Birthday</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Dependents</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($members as $member)
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                                        {{ substr($member->name, 0, 1) }}
                                    </div>
                                    <div class="ml-3 font-semibold text-gray-900">{{ $member->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $member->birthday?->format('M d, Y') ?? '—' }}
                                <span class="text-xs text-gray-400 ml-1">({{ $member->birthday ? $member->birthday->age : '?' }})</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->indigent)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        Indigent
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Regular
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center font-medium">
                                {{ $member->dependents_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                <a href="{{ route('members.show', $member) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                <a href="{{ route('members.edit', $member) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $members->links() }}
            </div>
        </div>
    </div>
</x-app-layout>