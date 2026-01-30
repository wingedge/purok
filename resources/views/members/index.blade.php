<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Members
        </h2>
    </x-slot>

    

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">      
            
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
                        
            <div class="bg-white shadow rounded p-4 sm:p-0">

                <div class="grid grid-cols-1 gap-4 sm:hidden">
                    @foreach($members as $member)
                    <div class="bg-white p-4 rounded-lg shadow space-y-3 border-l-4 border-blue-600">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-bold text-gray-900">{{ $member->name }}</div>
                            <div class="text-xs font-semibold uppercase px-2 py-1 rounded {{ $member->indigent ? 'bg-red-100 text-red-700' : '' }}">
                                {{ $member->indigent ? 'Indigent' : '' }}
                            </div>
                        </div>
                        
                        <div class="text-sm text-gray-600">
                            <p><strong>Birthday:</strong> {{ $member->birthday?->format('M d, Y') ?? '—' }}</p>
                            <p><strong>Dependents:</strong> {{ $member->dependents_count }}</p>
                        </div>

                        <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                            <a href="{{ route('members.show', $member) }}" class="text-blue-600 text-sm font-medium">View</a>
                            <a href="{{ route('members.edit', $member) }}" class="text-yellow-600 text-sm font-medium">Edit</a>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="hidden sm:block bg-white shadow rounded overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Birthday</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Is Indigent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dependents</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($members as $member)
                        <tr>
                            <td class="px-6 py-4">{{ $member->name }}</td>
                            <td class="px-6 py-4">{{ $member->birthday?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $member->indigent ? 'Indigent' : 'No' }}</td>
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

            <div class="mt-6 p-4">
                {{ $members->links() }}
            </div>

            <div class="mt-6 flex flex-col space-y-4 bg-white p-4 rounded shadow sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                <a href="{{ route('members.create') }}" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-3 rounded text-center font-medium">
                    + Add Member
                </a>

                <form method="POST" action="{{ route('members.import') }}" enctype="multipart/form-data" 
                    class="flex flex-col w-full sm:flex-row sm:items-center border-t pt-4 sm:border-t-0 sm:pt-0">
                    @csrf
                    <input type="file" name="csv_file" accept=".csv" required 
                        class="block w-full text-sm text-gray-500 mb-2 sm:mb-0 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
                    
                    <button class="w-full sm:w-auto bg-green-600 text-white px-4 py-3 rounded hover:bg-green-700">
                        Import CSV
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
