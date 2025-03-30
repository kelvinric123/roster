<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $staffTypeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }} Dashboard
            </h2>
            <a href="{{ route('roster.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-indigo-600">{{ $rosters->count() }}</div>
                    <div class="text-sm text-gray-600">Total {{ $staffTypeLabels[$type] }} Rosters</div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-green-600">{{ $staff->count() }}</div>
                    <div class="text-sm text-gray-600">{{ $staffTypeLabels[$type] }} Staff</div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-blue-600">{{ $entries->count() }}</div>
                    <div class="text-sm text-gray-600">Total Shift Entries</div>
                </div>
            </div>
            
            <!-- Staff List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ $staffTypeLabels[$type] }} Staff</h3>
                    
                    @if($staff->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($staff as $staffMember)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $staffMember->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $staffMember->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $staffMember->department ? $staffMember->department->name : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($staffMember->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No {{ $staffTypeLabels[$type] }} staff found.</p>
                    @endif
                </div>
            </div>
            
            <!-- Rosters and Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Rosters List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">{{ $staffTypeLabels[$type] }} Rosters</h3>
                        
                        @if($rosters->count() > 0)
                            <div class="space-y-3">
                                @foreach($rosters as $roster)
                                    <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                                        <div class="flex justify-between">
                                            <a href="{{ route('manage.rosters.show', $roster->id) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                                {{ $roster->name }}
                                            </a>
                                            <span class="text-xs {{ $roster->is_published ? 'text-green-600' : 'text-yellow-600' }}">
                                                {{ $roster->is_published ? 'Published' : 'Draft' }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            {{ $roster->department ? $roster->department->name : 'N/A' }} |
                                            {{ \Carbon\Carbon::parse($roster->start_date)->format('d/m/Y') }} - 
                                            {{ \Carbon\Carbon::parse($roster->end_date)->format('d/m/Y') }}
                                            @if($roster->roster_type)
                                                <span class="ml-1 px-2 py-0.5 text-xs {{ $roster->roster_type == 'oncall' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }} rounded-full">
                                                    {{ $roster->roster_type_label }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No rosters found for {{ $staffTypeLabels[$type] }}.</p>
                        @endif
                    </div>
                </div>
                
                <!-- Staff with Most Entries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Staff with Most Shifts</h3>
                        
                        @if(count($staffWithMostEntries) > 0)
                            <div class="space-y-4">
                                @foreach($staffWithMostEntries as $item)
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">{{ $item['staff_name'] }}</span>
                                            <span class="text-sm font-medium text-gray-700">{{ $item['total_entries'] }} shifts</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-indigo-500 h-2.5 rounded-full" style="width: {{ min(100, ($item['total_entries'] / max(1, $entries->count())) * 100 * 5) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No shift data available.</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Entries by Month -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Shifts by Month</h3>
                    
                    @if(count($entriesByMonth) > 0)
                        <div class="h-64">
                            <div class="h-full flex items-end">
                                @foreach($entriesByMonth as $month => $count)
                                    <div class="flex-1 h-full flex flex-col items-center justify-end">
                                        <div class="w-full px-2">
                                            <div class="bg-blue-500 w-full rounded-t" style="height: {{ min(100, ($count / max(array_values($entriesByMonth->toArray()))) * 100) }}%"></div>
                                        </div>
                                        <div class="text-xs mt-1 text-gray-600">{{ \Carbon\Carbon::parse($month)->format('M Y') }}</div>
                                        <div class="text-xs font-medium">{{ $count }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">No monthly shift data available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 