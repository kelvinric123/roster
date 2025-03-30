@props(['staff', 'totalShifts', 'oncallShifts', 'weekendShifts', 'weekdayShifts', 'holidayShifts'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 bg-white border-b border-gray-200">
        <h3 class="text-lg font-semibold mb-4">Your Shift Statistics</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                <p class="text-sm font-medium text-indigo-800">Total Shifts</p>
                <p class="text-2xl font-bold text-indigo-900">{{ $totalShifts }}</p>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-sm font-medium text-blue-800">On-Call Shifts</p>
                <p class="text-2xl font-bold text-blue-900">{{ $oncallShifts }}</p>
            </div>
            
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <p class="text-sm font-medium text-yellow-800">Weekend Shifts</p>
                <p class="text-2xl font-bold text-yellow-900">{{ $weekendShifts }}</p>
            </div>
            
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <p class="text-sm font-medium text-red-800">Holiday Shifts</p>
                <p class="text-2xl font-bold text-red-900">{{ $holidayShifts }}</p>
            </div>
        </div>
        
        <!-- Chart Visualization -->
        <div class="mt-6">
            <h4 class="text-md font-medium text-gray-700 mb-3">Shift Distribution</h4>
            
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Weekday vs Weekend Chart -->
                <div class="flex-1 p-4 border border-gray-200 rounded-lg">
                    <h5 class="text-sm font-medium text-gray-600 mb-2">Weekday vs Weekend Shifts</h5>
                    <div class="flex items-end h-32">
                        @php
                            $weekdayPercentage = $totalShifts > 0 ? ($weekdayShifts / $totalShifts) * 100 : 0;
                            $weekendPercentage = $totalShifts > 0 ? ($weekendShifts / $totalShifts) * 100 : 0;
                        @endphp
                        
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-16 bg-indigo-500 rounded-t" style="height: {{ min(100, $weekdayPercentage) }}%"></div>
                            <span class="text-xs mt-1">Weekday</span>
                            <span class="text-xs font-semibold">{{ $weekdayShifts }}</span>
                        </div>
                        
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-16 bg-yellow-500 rounded-t" style="height: {{ min(100, $weekendPercentage) }}%"></div>
                            <span class="text-xs mt-1">Weekend</span>
                            <span class="text-xs font-semibold">{{ $weekendShifts }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Regular vs OnCall Chart -->
                <div class="flex-1 p-4 border border-gray-200 rounded-lg">
                    <h5 class="text-sm font-medium text-gray-600 mb-2">Regular vs On-Call Shifts</h5>
                    <div class="flex items-end h-32">
                        @php
                            $regularShifts = $totalShifts - $oncallShifts;
                            $regularPercentage = $totalShifts > 0 ? ($regularShifts / $totalShifts) * 100 : 0;
                            $oncallPercentage = $totalShifts > 0 ? ($oncallShifts / $totalShifts) * 100 : 0;
                        @endphp
                        
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-16 bg-green-500 rounded-t" style="height: {{ min(100, $regularPercentage) }}%"></div>
                            <span class="text-xs mt-1">Regular</span>
                            <span class="text-xs font-semibold">{{ $regularShifts }}</span>
                        </div>
                        
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-16 bg-blue-500 rounded-t" style="height: {{ min(100, $oncallPercentage) }}%"></div>
                            <span class="text-xs mt-1">On-Call</span>
                            <span class="text-xs font-semibold">{{ $oncallShifts }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 