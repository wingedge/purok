<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Log New Certificate</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <form method="POST" action="{{ route('purok_certificates.store') }}" class="space-y-6">
                    @csrf

                    <div x-data="{ 
                        query: '', results: [], selectedId: '', selectedName: '',
                        search() {
                            if(this.query.length < 2) { this.results = []; return; }
                            fetch(`{{ route('members.search') }}?q=${this.query}`).then(res => res.json()).then(data => this.results = data);
                        },
                        select(m) {
                            this.selectedId = m.id; this.selectedName = m.name; this.query = ''; this.results = [];
                        }
                    }" class="relative">
                        <x-input-label value="Search Resident (Member or Dependent)" />
                        <input type="hidden" name="member_id" :value="selectedId" required>
                        <x-text-input x-model="query" @input.debounce.300ms="search()" placeholder="Start typing name..." class="w-full mt-1" />
                        
                        <template x-if="selectedName">
                            <div class="mt-2 p-3 bg-green-50 border border-green-200 rounded text-sm flex justify-between">
                                <span><strong>Selected:</strong> <span x-text="selectedName"></span></span>
                                <button type="button" @click="selectedId = ''; selectedName = ''" class="text-red-500 text-xs">Change</button>
                            </div>
                        </template>

                        <div x-show="results.length > 0" class="absolute z-50 w-full bg-white border mt-1 rounded shadow-xl max-h-60 overflow-y-auto">
                            <template x-for="m in results" :key="m.id">
                                <button type="button" @click="select(m)" class="w-full text-left px-4 py-3 hover:bg-indigo-50 border-b last:border-0">
                                    <div class="font-bold text-sm" x-text="m.name"></div>
                                    <div class="text-xs text-gray-500 italic" x-text="m.deps"></div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Request Date" />
                        <x-text-input type="date" name="request_date" value="{{ date('Y-m-d') }}" class="w-full mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Purpose" />
                        <textarea name="purpose" class="w-full border-gray-300 rounded-md shadow-sm" rows="4" required placeholder="e.g., Scholarship, Financial Assistance, Employment..."></textarea>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Save Entry</x-primary-button>
                        <a href="{{ route('purok_certificates.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>