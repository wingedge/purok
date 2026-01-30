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

            @if(session('errors'))
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                    {{ session('errors') }}
                </div>
            @endif
            
            <div class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-6 bg-white p-4 rounded shadow border border-gray-100">

                <a href="{{ route('members.create') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded text-center whitespace-nowrap">
                    Add Member
                </a>

                <form method="POST" 
                    action="{{ route('members.import') }}" 
                    enctype="multipart/form-data" 
                    class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4  w-full sm:w-auto">
                    @csrf
                    
                    <input type="file" 
                        name="csv_file" 
                        accept=".csv" 
                        required 
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                    <button class="w-full sm:w-auto bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 whitespace-nowrap">
                        Import CSV
                    </button>
                </form>
            </div>

            <div class="bg-white shadow rounded">
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
                            <td class="px-6 py-4">{{ $member->isIndigent }}</td>
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

            <div class="mt-6">
                {{ $members->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
