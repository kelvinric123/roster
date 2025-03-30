@props(['departmentName', 'staffType', 'teamStats'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 bg-white border-b border-gray-200">
        <h3 class="text-lg font-semibold mb-2">{{ $departmentName }} Team Statistics</h3>
        <p class="text-sm text-gray-600 mb-4">Shift statistics for your {{ $staffType }} team</p>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <p class="text-sm font-medium text-purple-800">Team Members</p>
                <p class="text-2xl font-bold text-purple-900">{{ $teamStats['teamCount'] }}</p>
            </div>
            
            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                <p class="text-sm font-medium text-indigo-800">Total Shifts</p>
                <p class="text-2xl font-bold text-indigo-900">{{ $teamStats['totalShifts'] }}</p>
            </div>
            
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <p class="text-sm font-medium text-yellow-800">Weekend/Holiday</p>
                <p class="text-2xl font-bold text-yellow-900">{{ $teamStats['weekendHolidayShifts'] }}</p>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-sm font-medium text-blue-800">On-Call Shifts</p>
                <p class="text-2xl font-bold text-blue-900">{{ $teamStats['oncallShifts'] }}</p>
            </div>
        </div>
        
        <!-- Staff Distribution Chart -->
        <div class="mt-6">
            <h4 class="text-md font-medium text-gray-700 mb-3">Team Shift Distribution</h4>
            
            <div class="bg-white p-4 border border-gray-200 rounded-lg">
                <h5 class="text-sm font-medium text-gray-600 mb-2">Staff with Most Shifts</h5>
                
                @foreach($teamStats['topStaff'] as $staffMember)
                    <div class="mb-3">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $staffMember['name'] }}</span>
                            <span class="text-sm font-medium text-gray-700">{{ $staffMember['shifts'] }} shifts</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-indigo-500 h-2.5 rounded-full" style="width: {{ min(100, ($staffMember['shifts'] / max(1, $teamStats['maxShifts'])) * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Weekend/Holiday Distribution -->
        <div class="mt-6">
            <h4 class="text-md font-medium text-gray-700 mb-3">Weekend & Holiday Distribution</h4>
            
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 p-4 border border-gray-200 rounded-lg">
                    <h5 class="text-sm font-medium text-gray-600 mb-2">Weekend vs Weekday Shifts</h5>
                    <div class="flex items-end h-32 justify-center gap-6">
                        <div class="flex flex-col items-center">
                            <div class="w-16 bg-indigo-500 rounded-t" style="height: {{ min(100, ($teamStats['weekdayShifts'] / max(1, $teamStats['totalShifts'])) * 100) }}%"></div>
                            <span class="text-xs mt-1">Weekday</span>
                            <span class="text-xs font-semibold">{{ $teamStats['weekdayShifts'] }}</span>
                        </div>
                        
                        <div class="flex flex-col items-center">
                            <div class="w-16 bg-yellow-500 rounded-t" style="height: {{ min(100, ($teamStats['weekendShifts'] / max(1, $teamStats['totalShifts'])) * 100) }}%"></div>
                            <span class="text-xs mt-1">Weekend</span>
                            <span class="text-xs font-semibold">{{ $teamStats['weekendShifts'] }}</span>
                        </div>
                        
                        <div class="flex flex-col items-center">
                            <div class="w-16 bg-red-500 rounded-t" style="height: {{ min(100, ($teamStats['holidayShifts'] / max(1, $teamStats['totalShifts'])) * 100) }}%"></div>
                            <span class="text-xs mt-1">Holiday</span>
                            <span class="text-xs font-semibold">{{ $teamStats['holidayShifts'] }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 p-4 border border-gray-200 rounded-lg">
                    <h5 class="text-sm font-medium text-gray-600 mb-2">By Shift Type</h5>
                    <div class="flex items-end h-32 justify-center gap-6">
                        <div class="flex flex-col items-center">
                            <div class="w-16 bg-green-500 rounded-t" style="height: {{ min(100, (($teamStats['totalShifts'] - $teamStats['oncallShifts']) / max(1, $teamStats['totalShifts'])) * 100) }}%"></div>
                            <span class="text-xs mt-1">Regular</span>
                            <span class="text-xs font-semibold">{{ $teamStats['totalShifts'] - $teamStats['oncallShifts'] }}</span>
                        </div>
                        
                        <div class="flex flex-col items-center">
                            <div class="w-16 bg-blue-500 rounded-t" style="height: {{ min(100, ($teamStats['oncallShifts'] / max(1, $teamStats['totalShifts'])) * 100) }}%"></div>
                            <span class="text-xs mt-1">On-Call</span>
                            <span class="text-xs font-semibold">{{ $teamStats['oncallShifts'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 