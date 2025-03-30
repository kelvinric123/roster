<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Team Leader Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('team-leaders.edit', $teamLeader) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('team-leaders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Staff Information</h3>
                            <div class="bg-gray-100 p-4 rounded-lg">
                                <p><span class="font-semibold">Name:</span> {{ $teamLeader->staff->name }}</p>
                                <p><span class="font-semibold">Staff Type:</span> {{ $teamLeader->staff->type_label }}</p>
                                <p><span class="font-semibold">Email:</span> {{ $teamLeader->staff->email }}</p>
                                <p><span class="font-semibold">Phone:</span> {{ $teamLeader->staff->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Leader Information</h3>
                            <div class="bg-gray-100 p-4 rounded-lg">
                                <p><span class="font-semibold">Department:</span> {{ $teamLeader->department->name }}</p>
                                <p><span class="font-semibold">Leader Type:</span> {{ $teamLeader->leader_type_label }}</p>
                                <p><span class="font-semibold">Start Date:</span> {{ $teamLeader->start_date->format('d M Y') }}</p>
                                <p><span class="font-semibold">End Date:</span> {{ $teamLeader->end_date ? $teamLeader->end_date->format('d M Y') : 'Active (No End Date)' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($teamLeader->notes)
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">Notes</h3>
                            <div class="bg-gray-100 p-4 rounded-lg">
                                {{ $teamLeader->notes }}
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-8 flex justify-between">
                        <form action="{{ route('team-leaders.destroy', $teamLeader) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this leader?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Remove Leader Role
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 