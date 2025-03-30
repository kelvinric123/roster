<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Roster Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-indigo-600">{{ $totalRosters }}</div>
                    <div class="text-sm text-gray-600">Total Rosters</div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-green-600">{{ $publishedRosters }}</div>
                    <div class="text-sm text-gray-600">Published Rosters</div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-blue-600">{{ $totalEntries }}</div>
                    <div class="text-sm text-gray-600">Total Roster Entries</div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-purple-600">{{ $confirmedEntries }}</div>
                    <div class="text-sm text-gray-600">Confirmed Entries</div>
                </div>
            </div>
            
            <!-- Current Active Rosters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Active Rosters</h3>
                    
                    @if($activeRosters->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($activeRosters as $roster)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $roster->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $typeLabels = [
                                                        'specialist_doctor' => 'Specialist Doctor',
                                                        'medical_officer' => 'Medical Officer',
                                                        'houseman_officer' => 'Houseman Officer',
                                                        'nurse' => 'Nurse'
                                                    ];
                                                @endphp
                                                {{ $typeLabels[$roster->staff_type] ?? $roster->staff_type }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $roster->department ? $roster->department->name : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($roster->start_date)->format('d/m/Y') }} - 
                                                {{ \Carbon\Carbon::parse($roster->end_date)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($roster->is_published)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Published
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Draft
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('manage.rosters.show', $roster->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                                <a href="{{ route('manage.rosters.edit', $roster->id) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No active rosters found for the current period.</p>
                    @endif
                </div>
            </div>
            
            <!-- Rosters by Type and Department -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Rosters by Type -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Rosters by Staff Type</h3>
                        
                        @if(count($rostersByType) > 0)
                            <div class="space-y-4">
                                @php
                                    $typeLabels = [
                                        'specialist_doctor' => 'Specialist Doctor',
                                        'medical_officer' => 'Medical Officer',
                                        'houseman_officer' => 'Houseman Officer',
                                        'nurse' => 'Nurse'
                                    ];
                                    $colors = [
                                        'specialist_doctor' => 'bg-indigo-500',
                                        'medical_officer' => 'bg-blue-500',
                                        'houseman_officer' => 'bg-green-500',
                                        'nurse' => 'bg-purple-500'
                                    ];
                                @endphp
                                
                                @foreach($rostersByType as $type => $count)
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <a href="{{ route('roster.staff_type_stats', $type) }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">
                                                {{ $typeLabels[$type] ?? $type }}
                                            </a>
                                            <span class="text-sm font-medium text-gray-700">{{ $count }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="{{ $colors[$type] ?? 'bg-gray-500' }} h-2.5 rounded-full" style="width: {{ ($count / $totalRosters) * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No roster data available.</p>
                        @endif
                    </div>
                </div>
                
                <!-- Rosters by Department -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Rosters by Department</h3>
                        
                        @if(count($rostersByDepartment) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Rosters</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($rostersByDepartment as $item)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $item['department'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $item['total'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('roster.department_stats', array_search($item['department'], array_column($rostersByDepartment->toArray(), 'department'))) }}" class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500">No department data available.</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Recent and Upcoming -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Recent Rosters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Recently Created Rosters</h3>
                        
                        @if($recentRosters->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentRosters as $roster)
                                    <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                                        <div class="flex justify-between">
                                            <a href="{{ route('manage.rosters.show', $roster->id) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                                {{ $roster->name }}
                                            </a>
                                            <span class="text-xs text-gray-500">
                                                {{ $roster->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            @php
                                                $typeLabels = [
                                                    'specialist_doctor' => 'Specialist Doctor',
                                                    'medical_officer' => 'Medical Officer',
                                                    'houseman_officer' => 'Houseman Officer',
                                                    'nurse' => 'Nurse'
                                                ];
                                            @endphp
                                            {{ $typeLabels[$roster->staff_type] ?? $roster->staff_type }} | 
                                            {{ $roster->department ? $roster->department->name : 'N/A' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No recent rosters found.</p>
                        @endif
                    </div>
                </div>
                
                <!-- Ending Soon -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Rosters Ending Soon</h3>
                        
                        @if($upcomingEndDates->count() > 0)
                            <div class="space-y-3">
                                @foreach($upcomingEndDates as $roster)
                                    <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                                        <div class="flex justify-between">
                                            <a href="{{ route('manage.rosters.show', $roster->id) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                                {{ $roster->name }}
                                            </a>
                                            <span class="text-xs font-medium {{ \Carbon\Carbon::parse($roster->end_date)->diffInDays(now()) <= 3 ? 'text-red-500' : 'text-orange-500' }}">
                                                Ends in {{ \Carbon\Carbon::parse($roster->end_date)->diffInDays(now()) }} days
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            End Date: {{ \Carbon\Carbon::parse($roster->end_date)->format('d/m/Y') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No rosters ending in the next 7 days.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 