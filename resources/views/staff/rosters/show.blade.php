<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $roster->name }}
            </h2>
            <div>
                <a href="{{ route('staff.rosters.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                    Back to Roster List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="bg-green-100 text-green-800 p-4 mb-4 rounded">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <!-- Roster Details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded border border-blue-200">
                            <h3 class="text-md font-medium text-blue-800">Department</h3>
                            @if(isset($isDepartmentLeader) && $isDepartmentLeader)
                                <p class="text-lg font-semibold text-blue-900">{{ $departmentName ?? $roster->department->name }}</p>
                            @else
                                <p class="text-lg font-semibold text-blue-900">{{ $roster->department->name }}</p>
                            @endif
                        </div>
                        <div class="bg-green-50 p-4 rounded border border-green-200">
                            <h3 class="text-md font-medium text-green-800">Staff Type</h3>
                            <p class="text-lg font-semibold text-green-900">{{ $roster->staff_type_label }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded border border-purple-200">
                            <h3 class="text-md font-medium text-purple-800">Period</h3>
                            <p class="text-lg font-semibold text-purple-900">
                                {{ $roster->start_date->format('d/m/Y') }} - {{ $roster->end_date->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Calendar View -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Roster Calendar</h3>
                        
                        @php
                            // Set up dates for weekly view
                            $startDate = $roster->start_date->copy();
                            $endDate = $roster->end_date->copy();
                            
                            // Get current week (from URL parameter or default to first week)
                            $currentWeekStart = request()->query('week_start') 
                                ? \Carbon\Carbon::parse(request()->query('week_start')) 
                                : $startDate->copy();
                            
                            // Ensure currentWeekStart is not before startDate
                            if ($currentWeekStart->lt($startDate)) {
                                $currentWeekStart = $startDate->copy();
                            }
                            
                            // Calculate end of current week (6 days after start = 7 day week)
                            $currentWeekEnd = $currentWeekStart->copy()->addDays(6);
                            
                            // If week would extend past the roster end date, cap it
                            if ($currentWeekEnd->gt($endDate)) {
                                $currentWeekEnd = $endDate->copy();
                            }
                            
                            // Calculate previous and next week start dates
                            $prevWeekStart = $currentWeekStart->copy()->subDays(7);
                            if ($prevWeekStart->lt($startDate)) {
                                $prevWeekStart = $startDate->copy();
                            }
                            
                            $nextWeekStart = $currentWeekStart->copy()->addDays(7);
                            // Disable next week if it would start after the end date
                            $hasNextWeek = $nextWeekStart->lte($endDate);
                            
                            // Check if we're on the first week
                            $isFirstWeek = $currentWeekStart->eq($startDate);
                        @endphp
                        
                        <!-- Weekly Navigation -->
                        <div class="flex justify-between items-center mb-4">
                            <div class="text-sm font-medium text-gray-600">
                                Viewing week: {{ $currentWeekStart->format('d M Y') }} - {{ $currentWeekEnd->format('d M Y') }}
                            </div>
                            <div class="flex space-x-3">
                                @if(!$isFirstWeek)
                                    <a href="{{ route('staff.rosters.show', ['roster' => $roster->id, 'week_start' => $prevWeekStart->format('Y-m-d')]) }}" 
                                       class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                        &laquo; Previous Week
                                    </a>
                                @else
                                    <button disabled class="px-3 py-1 bg-gray-100 text-gray-400 rounded cursor-not-allowed">
                                        &laquo; Previous Week
                                    </button>
                                @endif
                                
                                @if($hasNextWeek)
                                    <a href="{{ route('staff.rosters.show', ['roster' => $roster->id, 'week_start' => $nextWeekStart->format('Y-m-d')]) }}" 
                                       class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                        Next Week &raquo;
                                    </a>
                                @else
                                    <button disabled class="px-3 py-1 bg-gray-100 text-gray-400 rounded cursor-not-allowed">
                                        Next Week &raquo;
                                    </button>
                                @endif
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto bg-white shadow rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                                        @php
                                            $currentDate = $currentWeekStart->copy();
                                        @endphp
                                        
                                        @while($currentDate->lte($currentWeekEnd))
                                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider {{ $currentDate->isWeekend() ? 'bg-gray-100' : '' }}">
                                                <div>{{ $currentDate->format('D') }}</div>
                                                <div>{{ $currentDate->format('d/m') }}</div>
                                                @if (isset($holidaysByDate[$currentDate->format('Y-m-d')]))
                                                    @foreach($holidaysByDate[$currentDate->format('Y-m-d')] as $holiday)
                                                        <div class="mt-1 text-xs bg-red-100 text-red-800 px-1 py-0.5 rounded">
                                                            {{ $holiday->name }}
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </th>
                                            @php $currentDate->addDay(); @endphp
                                        @endwhile
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php
                                        // Load slots with staff relationship if not already loaded
                                        if (!$roster->relationLoaded('slots') || !$roster->slots->first()?->relationLoaded('staff')) {
                                            $roster->load('slots.staff');
                                        }
                                        
                                        // Group slots by staff
                                        $slotsByStaff = collect([]);
                                        foreach ($roster->slots as $slot) {
                                            if (!$slotsByStaff->has($slot->staff_id)) {
                                                $slotsByStaff->put($slot->staff_id, collect([]));
                                            }
                                            $slotsByStaff->get($slot->staff_id)->push($slot);
                                        }
                                        
                                        // Get all unique staff IDs
                                        $staffIds = $slotsByStaff->keys();
                                    @endphp
                                    
                                    @foreach($staffIds as $staffId)
                                        @php
                                            $staffSlots = $slotsByStaff->get($staffId);
                                            $staffName = $staffSlots->first()->staff->name ?? 'Unknown Staff';
                                            
                                            // Create a map of date => slot for this staff
                                            $dateSlotMap = [];
                                            foreach ($staffSlots as $slot) {
                                                $dateKey = $slot->date->format('Y-m-d');
                                                $dateSlotMap[$dateKey] = $slot;
                                            }
                                        @endphp
                                        
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $staffName }}
                                            </td>
                                            
                                            @php $currentDate = $currentWeekStart->copy(); @endphp
                                            @while($currentDate->lte($currentWeekEnd))
                                                @php 
                                                    $dateKey = $currentDate->format('Y-m-d');
                                                    $slot = $dateSlotMap[$dateKey] ?? null;
                                                    $isWeekend = $currentDate->isWeekend();
                                                    
                                                    $bgColor = $isWeekend ? 'bg-gray-50' : 'bg-white';
                                                    $textColor = 'text-gray-900';
                                                    
                                                    if ($slot) {
                                                        switch ($slot->shift_type) {
                                                            case 'morning':
                                                                $bgColor = 'bg-blue-100';
                                                                $textColor = 'text-blue-800';
                                                                break;
                                                            case 'evening':
                                                                $bgColor = 'bg-indigo-100';
                                                                $textColor = 'text-indigo-800';
                                                                break;
                                                            case 'night':
                                                                $bgColor = 'bg-purple-100';
                                                                $textColor = 'text-purple-800';
                                                                break;
                                                            case 'oncall':
                                                                $bgColor = 'bg-orange-100';
                                                                $textColor = 'text-orange-800';
                                                                break;
                                                            case 'off':
                                                                $bgColor = 'bg-red-100';
                                                                $textColor = 'text-red-800';
                                                                break;
                                                            default:
                                                                $bgColor = 'bg-gray-100';
                                                                $textColor = 'text-gray-800';
                                                                break;
                                                        }
                                                    }
                                                @endphp
                                                
                                                <td class="px-3 py-4 whitespace-nowrap text-center text-sm {{ $bgColor }} {{ $textColor }}">
                                                    @if ($slot)
                                                        <div class="font-medium">{{ ucfirst($slot->shift_type) }}</div>
                                                        @if ($slot->start_time && $slot->end_time)
                                                            <div class="text-xs">
                                                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                
                                                @php $currentDate->addDay(); @endphp
                                            @endwhile
                                        </tr>
                                    @endforeach
                                    
                                    @if ($staffIds->isEmpty())
                                        <tr>
                                            <td colspan="{{ $currentWeekStart->diffInDays($currentWeekEnd) + 2 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No staff slots found for this roster.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Legend -->
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Shift Legend</h4>
                        <div class="flex flex-wrap gap-3">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-blue-100 rounded mr-1"></div>
                                <span class="text-sm text-gray-600">Morning</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-indigo-100 rounded mr-1"></div>
                                <span class="text-sm text-gray-600">Evening</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-purple-100 rounded mr-1"></div>
                                <span class="text-sm text-gray-600">Night</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-orange-100 rounded mr-1"></div>
                                <span class="text-sm text-gray-600">On Call</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-red-100 rounded mr-1"></div>
                                <span class="text-sm text-gray-600">Off Duty</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 