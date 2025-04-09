<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $roster->name }}
                @if ($roster->is_published)
                    <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Published</span>
                @else
                    <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Draft</span>
                @endif
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('manage.rosters.edit', $roster) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition">
                    {{ __('Edit Roster') }}
                </a>
                <a href="{{ route('manage.rosters.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(isset($isDepartmentLeader) && $isDepartmentLeader)
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                            <h3 class="text-lg font-semibold text-blue-700">Department Leader Access</h3>
                            <p class="text-gray-700">Department: {{ $departmentName ?? 'Not Assigned' }}</p>
                            <p class="text-gray-700">Your Role: {{ ucfirst(str_replace('_', ' ', auth()->user()->staff->type)) }} Leader</p>
                        </div>
                    @endif
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Roster Details') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Staff Type') }}</p>
                            <p class="font-medium">{{ $roster->staff_type_label }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Department') }}</p>
                            <p class="font-medium">{{ $departmentName ?? $roster->department->name ?? 'Not Assigned' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Roster Type') }}</p>
                            <p class="font-medium">
                                @if($roster->roster_type)
                                    <span class="px-2 py-1 {{ $roster->roster_type == 'oncall' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }} rounded-full text-xs">
                                        {{ $roster->roster_type_label }} ({{ $roster->staff_type_label }})
                                    </span>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Period') }}</p>
                            <p class="font-medium">{{ $roster->start_date->format('d/m/Y') }} - {{ $roster->end_date->format('d/m/Y') }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Total Entries') }}</p>
                            <p class="font-medium">{{ $roster->entries->count() }}</p>
                        </div>
                        
                        @if ($roster->description)
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">{{ __('Description') }}</p>
                                <p class="font-medium">{{ $roster->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Interactive Roster Management -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Interactive Staff Assignment') }}</h3>
                    
                    <div x-data="interactiveRoster()" class="relative">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Staff Selection Panel -->
                            <div class="md:col-span-1 bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-3">{{ __('Available Staff') }}</h4>
                                <div class="flex mb-3">
                                    <input type="text" x-model="staffFilter" placeholder="Search staff..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>
                                
                                <div class="overflow-y-auto max-h-96 staff-list">
                                    <template x-for="staff in filteredStaff" :key="staff.id">
                                        <div 
                                            :id="'staff-' + staff.id"
                                            class="p-2 mb-2 bg-white rounded-md border border-gray-200 shadow-sm cursor-move hover:bg-indigo-50 transition"
                                            draggable="true"
                                            @dragstart="dragStart($event, staff)"
                                            x-text="staff.name">
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Calendar Panel -->
                            <div class="md:col-span-3">
                                <div class="flex justify-between items-center mb-4">
                                    <div>
                                        <label for="shift-filter" class="mr-2">{{ __('Filter by shift:') }}</label>
                                        <select id="shift-filter" x-model="shiftFilter" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="all">{{ __('All Shifts') }}</option>
                                            @if($roster->roster_type == 'shift')
                                                @if ($roster->department_id)
                                                    @php
                                                        $shiftSettings = \App\Models\RosterShiftSetting::where('department_id', $roster->department_id)->get();
                                                        $hasSettings = $shiftSettings->count() > 0;
                                                    @endphp
                                                    @if($hasSettings)
                                                        @foreach($shiftSettings as $setting)
                                                            <option value="{{ $setting->shift_type }}">{{ $setting->name }}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="morning">{{ __('Morning Shift') }}</option>
                                                        <option value="evening">{{ __('Evening Shift') }}</option>
                                                        <option value="night">{{ __('Night Shift') }}</option>
                                                    @endif
                                                @else
                                                    <option value="morning">{{ __('Morning Shift') }}</option>
                                                    <option value="evening">{{ __('Evening Shift') }}</option>
                                                    <option value="night">{{ __('Night Shift') }}</option>
                                                @endif
                                            @else
                                                <option value="oncall">{{ __('Oncall') }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <!-- Save Changes Button -->
                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="button" 
                                            @click="printCalendar" 
                                            class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            Print
                                        </button>
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" 
                                                @click="open = !open"
                                                class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Export
                                                <svg class="-mr-1 ml-1 h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div x-show="open" 
                                                @click.away="open = false"
                                                class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                                <div class="py-1">
                                                    <button @click="exportData('csv'); open = false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        CSV (.csv)
                                                    </button>
                                                    <button @click="exportData('html'); open = false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        HTML (.html)
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" 
                                            @click="autoAssignment" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Auto Assignment
                                        </button>
                                        <button type="button" 
                                            @click="saveAllChanges" 
                                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="!entries.some(e => e.is_temp) || isSaving"
                                            :class="{'relative': false}">
                                            <span x-show="!isSaving">Save All Changes</span>
                                            <span x-show="isSaving" class="flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Saving...
                                            </span>
                                            <template x-if="false">
                                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                    <span x-text="entries.filter(e => e.is_temp).length"></span>
                                                </span>
                                            </template>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center mb-4">
                                    <button type="button" 
                                        @click="previousTwoWeeks" 
                                        class="px-3 py-1 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                        &larr; Previous
                                    </button>
                                    <div class="text-center">
                                        <span x-text="dateRangeText"></span>
                                    </div>
                                    <button type="button" 
                                        @click="nextTwoWeeks" 
                                        class="px-3 py-1 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                        Next &rarr;
                                    </button>
                                </div>
                                
                                <div class="overflow-x-auto roster-calendar roster-calendar-container">
                                    <table class="min-w-full bg-white border-collapse">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border border-gray-300 px-4 py-2 text-left">{{ __('Date') }}</th>
                                                @if($roster->roster_type == 'shift')
                                                    <template x-if="shiftFilter === 'all' || shiftFilter === 'morning'">
                                                        <th class="border border-gray-300 px-4 py-2 text-left bg-blue-50">
                                                            @if($roster->department_id && isset($hasSettings) && $hasSettings)
                                                                @php
                                                                    $morningSetting = $shiftSettings->firstWhere('shift_type', 'morning');
                                                                @endphp
                                                                @if($morningSetting)
                                                                    {{ $morningSetting->name }}
                                                                @else
                                                                    {{ __('Morning Shift') }}
                                                                @endif
                                                            @else
                                                                {{ __('Morning Shift') }}
                                                            @endif
                                                        </th>
                                                    </template>
                                                    <template x-if="shiftFilter === 'all' || shiftFilter === 'evening'">
                                                        <th class="border border-gray-300 px-4 py-2 text-left bg-yellow-50">
                                                            @if($roster->department_id && isset($hasSettings) && $hasSettings)
                                                                @php
                                                                    $eveningSetting = $shiftSettings->firstWhere('shift_type', 'evening');
                                                                @endphp
                                                                @if($eveningSetting)
                                                                    {{ $eveningSetting->name }}
                                                                @else
                                                                    {{ __('Evening Shift') }}
                                                                @endif
                                                            @else
                                                                {{ __('Evening Shift') }}
                                                            @endif
                                                        </th>
                                                    </template>
                                                    <template x-if="shiftFilter === 'all' || shiftFilter === 'night'">
                                                        <th class="border border-gray-300 px-4 py-2 text-left bg-indigo-50">
                                                            @if($roster->department_id && isset($hasSettings) && $hasSettings)
                                                                @php
                                                                    $nightSetting = $shiftSettings->firstWhere('shift_type', 'night');
                                                                @endphp
                                                                @if($nightSetting)
                                                                    {{ $nightSetting->name }}
                                                                @else
                                                                    {{ __('Night Shift') }}
                                                                @endif
                                                            @else
                                                                {{ __('Night Shift') }}
                                                            @endif
                                                        </th>
                                                    </template>
                                                @else
                                                    <template x-if="shiftFilter === 'all' || shiftFilter === 'oncall'">
                                                        <th class="border border-gray-300 px-4 py-2 text-left bg-orange-50">
                                                            @php
                                                                $staffTypeRoster = $roster->department->staffTypeRosters->firstWhere('staff_type', $roster->staff_type);
                                                                $oncallStaffCount = $staffTypeRoster->settings['oncall_staff_count'] ?? 2;
                                                                $oncallStaffTitles = $staffTypeRoster->settings['oncall_staff_titles'] ?? [];
                                                                
                                                                // Ensure we have enough titles
                                                                while(count($oncallStaffTitles) < $oncallStaffCount) {
                                                                    $oncallStaffTitles[] = "oncall " . (count($oncallStaffTitles) + 1);
                                                                }
                                                            @endphp
                                                            {{ __('Oncall') }} ({{ $oncallStaffCount }} {{ __('staff') }})
                                                        </th>
                                                    </template>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(dateInfo, index) in visibleCalendarDates" :key="dateInfo.date">
                                                <tr :class="{'bg-yellow-50': dateInfo.isWeekend}" :data-date="dateInfo.date">
                                                    <td class="border border-gray-300 px-4 py-2">
                                                        <div x-text="dateInfo.formatted"></div>
                                                        <div class="text-xs text-gray-600" x-text="dateInfo.day"></div>
                                                        
                                                        @foreach($holidaysByDate as $date => $holidays)
                                                            <template x-if="dateInfo.date === '{{ $date }}'">
                                                                <div>
                                                                    @foreach($holidays as $holiday)
                                                                        <div class="mt-1 text-xs bg-red-100 text-red-800 px-1 py-0.5 rounded">
                                                                            {{ $holiday->name }}
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </template>
                                                        @endforeach
                                                        
                                                        <!-- Show oncall staff count for this date -->
                                                        <template x-if="rosterType === 'oncall'">
                                                            <div class="mt-1 text-xs">
                                                                <span class="px-2 py-0.5 bg-orange-100 text-orange-800 rounded-full">
                                                                    <span x-text="getEntriesForDateAndShift(dateInfo.date, 'oncall').length"></span>/<span>{{ $roster->department->staffTypeRosters->firstWhere('staff_type', $roster->staff_type)->settings['oncall_staff_count'] ?? 2 }}</span> staff
                                                                </span>
                                                            </div>
                                                        </template>
                                                    </td>
                                                    
                                                    @if($roster->roster_type == 'shift')
                                                        <template x-if="shiftFilter === 'all' || shiftFilter === 'morning'">
                                                            <td class="border border-gray-300 px-4 py-2 min-h-20 drop-zone"
                                                                :id="'cell-' + dateInfo.date + '-morning'"
                                                                @dragover.prevent
                                                                @drop="handleDrop($event, dateInfo.date, 'morning')">
                                                                <template x-for="entry in getEntriesForDateAndShift(dateInfo.date, 'morning')" :key="entry.id">
                                                                    <div 
                                                                        :class="{
                                                                            'mb-1 p-1 text-xs rounded shadow-sm border': true,
                                                                            'bg-blue-50 border-blue-300': entry.is_confirmed && !entry.is_temp,
                                                                            'bg-gray-50 border-gray-300': !entry.is_confirmed && !entry.is_temp,
                                                                            'bg-gray-50 border-gray-300': entry.is_temp
                                                                        }"
                                                                        :title="entry.staff.name + ' - ' + entry.shift_type_label + (false ? ' (Unsaved)' : '')"
                                                                    >
                                                                        <div class="flex items-start justify-between">
                                                                            <span class="font-medium" x-text="entry.staff.name"></span>
                                                                            <button type="button" class="text-gray-400 hover:text-red-500" @click="removeEntry(entry.id)">
                                                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                        <div>
                                                                            <span class="px-1 rounded-full text-xs bg-blue-100 text-blue-800" x-text="entry.shift_type_label"></span>
                                                                        </div>
                                                                        <!-- Time display removed -->
                                                                    </div>
                                                                </template>
                                                            </td>
                                                        </template>
                                                        
                                                        <template x-if="shiftFilter === 'all' || shiftFilter === 'evening'">
                                                            <td class="border border-gray-300 px-4 py-2 min-h-20 drop-zone"
                                                                :id="'cell-' + dateInfo.date + '-evening'"
                                                                @dragover.prevent
                                                                @drop="handleDrop($event, dateInfo.date, 'evening')">
                                                                <template x-for="entry in getEntriesForDateAndShift(dateInfo.date, 'evening')" :key="entry.id">
                                                                    <div 
                                                                        :class="{
                                                                            'mb-1 p-1 text-xs rounded shadow-sm border': true,
                                                                            'bg-indigo-50 border-indigo-300': entry.is_confirmed && !entry.is_temp,
                                                                            'bg-gray-50 border-gray-300': !entry.is_confirmed && !entry.is_temp,
                                                                            'bg-gray-50 border-gray-300': entry.is_temp
                                                                        }"
                                                                        :title="entry.staff.name + ' - ' + entry.shift_type_label + (false ? ' (Unsaved)' : '')"
                                                                    >
                                                                        <div class="flex items-start justify-between">
                                                                            <span class="font-medium" x-text="entry.staff.name"></span>
                                                                            <button type="button" class="text-gray-400 hover:text-red-500" @click="removeEntry(entry.id)">
                                                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                        <div>
                                                                            <span class="px-1 rounded-full text-xs bg-indigo-100 text-indigo-800" x-text="entry.shift_type_label"></span>
                                                                        </div>
                                                                        <!-- Time display removed -->
                                                                    </div>
                                                                </template>
                                                            </td>
                                                        </template>
                                                        
                                                        <template x-if="shiftFilter === 'all' || shiftFilter === 'night'">
                                                            <td class="border border-gray-300 px-4 py-2 min-h-20 drop-zone"
                                                                :id="'cell-' + dateInfo.date + '-night'"
                                                                @dragover.prevent
                                                                @drop="handleDrop($event, dateInfo.date, 'night')">
                                                                <template x-for="entry in getEntriesForDateAndShift(dateInfo.date, 'night')" :key="entry.id">
                                                                    <div 
                                                                        :class="{
                                                                            'mb-1 p-1 text-xs rounded shadow-sm border': true,
                                                                            'bg-purple-50 border-purple-300': entry.is_confirmed && !entry.is_temp,
                                                                            'bg-gray-50 border-gray-300': !entry.is_confirmed && !entry.is_temp,
                                                                            'bg-gray-50 border-gray-300': entry.is_temp
                                                                        }"
                                                                        :title="entry.staff.name + ' - ' + entry.shift_type_label + (false ? ' (Unsaved)' : '')"
                                                                    >
                                                                        <div class="flex items-start justify-between">
                                                                            <span class="font-medium" x-text="entry.staff.name"></span>
                                                                            <button type="button" class="text-gray-400 hover:text-red-500" @click="removeEntry(entry.id)">
                                                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                        <div>
                                                                            <span class="px-1 rounded-full text-xs bg-purple-100 text-purple-800" x-text="entry.shift_type_label"></span>
                                                                        </div>
                                                                        <!-- Time display removed -->
                                                                    </div>
                                                                </template>
                                                            </td>
                                                        </template>
                                                    @else
                                                        <!-- On Call Roster Type -->
                                                        <template x-if="shiftFilter === 'all' || shiftFilter === 'oncall'">
                                                            <td class="border border-gray-300 px-4 py-2 min-h-20 drop-zone"
                                                                :id="'cell-' + dateInfo.date + '-oncall'"
                                                                @dragover.prevent
                                                                @drop="handleDrop($event, dateInfo.date, 'oncall')">
                                                                
                                                                <!-- Group entries by oncall staff title -->
                                                                @php
                                                                    $staffTypeRoster = $roster->department->staffTypeRosters->firstWhere('staff_type', $roster->staff_type);
                                                                    $oncallStaffCount = $staffTypeRoster->settings['oncall_staff_count'] ?? 2;
                                                                    $oncallStaffTitles = $staffTypeRoster->settings['oncall_staff_titles'] ?? [];
                                                                    
                                                                    // Ensure we have enough titles
                                                                    while(count($oncallStaffTitles) < $oncallStaffCount) {
                                                                        $oncallStaffTitles[] = "oncall " . (count($oncallStaffTitles) + 1);
                                                                    }
                                                                @endphp

                                                                <!-- For each oncall staff title, create a subsection -->
                                                                <template x-for="(title, titleIndex) in {{ json_encode($oncallStaffTitles) }}" :key="titleIndex">
                                                                    <div class="mb-3 border rounded-md p-2 bg-gray-50">
                                                                        <div class="text-xs font-semibold text-gray-700 mb-2 bg-orange-100 text-orange-800 py-1 px-2 rounded-md">
                                                                            <span x-text="title"></span>
                                                                        </div>
                                                                        
                                                                        <!-- Filter entries for this title index -->
                                                                        <template x-for="entry in getEntriesForDateAndShift(dateInfo.date, 'oncall').filter((e, i) => {
                                                                            // Use subsection_index if available, otherwise fallback to modulo method
                                                                            if (e.subsection_index !== undefined) {
                                                                                return e.subsection_index === titleIndex;
                                                                            } else {
                                                                                return i % {{ count($oncallStaffTitles) }} === titleIndex;
                                                                            }
                                                                        })" :key="entry.id">
                                                                            <div 
                                                                                :class="{
                                                                                    'mb-1 p-1 text-xs rounded shadow-sm border': true,
                                                                                    'bg-orange-50 border-orange-300': entry.is_confirmed && !entry.is_temp,
                                                                                    'bg-gray-50 border-gray-300': !entry.is_confirmed && !entry.is_temp,
                                                                                    'bg-gray-50 border-gray-300': entry.is_temp
                                                                                }"
                                                                                :title="entry.staff.name + ' - ' + entry.shift_type_label + (false ? ' (Unsaved)' : '')"
                                                                            >
                                                                                <div class="flex items-start justify-between">
                                                                                    <div class="font-medium text-sm">
                                                                                        <span x-text="entry.staff.name"></span>
                                                                                    </div>
                                                                                    <button type="button" class="text-gray-400 hover:text-red-500" @click="removeEntry(entry.id)">
                                                                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                                                        </svg>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </template>
                                                                        
                                                                        <!-- Show empty slot message if no entry for this title -->
                                                                        <div x-show="!getEntriesForDateAndShift(dateInfo.date, 'oncall').some((e, i) => {
                                                                            // Use subsection_index if available, otherwise fallback to modulo method
                                                                            if (e.subsection_index !== undefined) {
                                                                                return e.subsection_index === titleIndex;
                                                                            } else {
                                                                                return i % {{ count($oncallStaffTitles) }} === titleIndex;
                                                                            }
                                                                        })" 
                                                                            class="text-xs text-gray-400 italic py-1">
                                                                            Empty slot
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </td>
                                                        </template>
                                                    @endif
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('interactiveRoster', () => ({
                staffFilter: '',
                shiftFilter: 'all',
                availableStaff: [],
                entries: [],
                calendarDates: [],
                visibleStartIndex: 0,
                visibleEndIndex: 13, // Show 14 days (2 weeks) by default
                rosterType: '{{ $roster->roster_type }}',
                draggingStaff: null,
                isSaving: false,
                
                // Additional properties for printing
                rosterName: '{{ $roster->name }}',
                departmentName: '{{ $roster->department->name ?? $departmentName ?? "Not Assigned" }}',
                staffTypeLabel: '{{ $roster->staff_type_label }}',
                rosterTypeLabel: '{{ $roster->roster_type_label }}',
                startDate: '{{ $roster->start_date->format("d/m/Y") }}',
                endDate: '{{ $roster->end_date->format("d/m/Y") }}',
                
                init() {
                    this.loadData();
                },
                
                loadData() {
                    // Load staff and entries data from the JSON encoded data
                    this.availableStaff = @json($availableStaff);
                    this.entries = @json($entries);
                    
                    console.log('Initial load - available staff:', this.availableStaff.length);
                    console.log('Initial load - entries:', this.entries.length);
                    
                    // For debugging - log actual entries data
                    if (this.entries && this.entries.length > 0) {
                        console.log('First few entries:', this.entries.slice(0, 3));
                    } else {
                        console.log('No entries loaded from server!');
                    }
                    
                    // Generate calendar dates and process entries right after loading data
                    this.generateCalendarDates();
                    this.processEntries();
                    
                    console.log('After processing - entries:', this.entries.length);
                },
                
                get filteredStaff() {
                    if (!this.staffFilter.trim()) {
                        return this.availableStaff;
                    }
                    
                    const filter = this.staffFilter.toLowerCase();
                    return this.availableStaff.filter(staff => 
                        staff.name.toLowerCase().includes(filter)
                    );
                },
                
                processEntries() {
                    // Process entries to add staff info and other details
                    console.log('Processing entries count:', this.entries.length);
                    
                    this.entries.forEach(entry => {
                        // Skip entries that already have staff data
                        if (entry.staff && entry.staff.id === entry.staff_id) {
                            console.log('Entry already has staff data:', entry.id);
                            entry.shift_type_label = this.getShiftTypeLabel(entry.shift_type);
                            return;
                        }
                        
                        const staff = this.availableStaff.find(s => s.id === entry.staff_id);
                        if (staff) {
                            entry.staff = staff;
                            entry.shift_type_label = this.getShiftTypeLabel(entry.shift_type);
                            
                            // Ensure both date and shift_date are available for compatibility
                            if (entry.date && !entry.shift_date) {
                                entry.shift_date = entry.date;
                                
                                // Handle date objects from Laravel serialization
                                if (typeof entry.shift_date === 'object' && entry.shift_date !== null) {
                                    if (entry.shift_date.date) {
                                        // Extract YYYY-MM-DD from object
                                        const dateStr = entry.shift_date.date.split(' ')[0];
                                        entry.shift_date = dateStr;
                                        entry.date = dateStr;
                                    }
                                }
                            } else if (entry.shift_date && !entry.date) {
                                entry.date = entry.shift_date;
                            }
                            
                            console.log('Added staff to entry:', entry.id, staff.name);
                        } else {
                            console.error('Staff not found for entry:', entry);
                        }
                    });
                    
                    // Sort entries by date for better organization
                    this.entries.sort((a, b) => {
                        const dateA = a.shift_date || a.date;
                        const dateB = b.shift_date || b.date;
                        
                        // Format dates consistently for comparison
                        const formatDate = (d) => {
                            if (typeof d === 'object' && d !== null) {
                                return d.date ? d.date.split(' ')[0] : '';
                            }
                            return d;
                        };
                        
                        return formatDate(dateA).localeCompare(formatDate(dateB));
                    });
                    
                    console.log('Entries after processing:', this.entries);
                },
                
                formatTime(timeString) {
                    if (!timeString) return '';
                    
                    // Handle both ISO datetime strings and simple time strings
                    if (timeString.includes('T')) {
                        // ISO datetime string
                        const date = new Date(timeString);
                        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    } else {
                        // Simple time string (HH:MM:SS)
                        return timeString.substring(0, 5); // Extract HH:MM
                    }
                },
                
                generateCalendarDates() {
                    const startDate = new Date('{{ $roster->start_date }}');
                    const endDate = new Date('{{ $roster->end_date }}');
                    
                    const dates = [];
                    const currentDate = new Date(startDate);
                    
                    while (currentDate <= endDate) {
                        const year = currentDate.getFullYear();
                        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                        const day = String(currentDate.getDate()).padStart(2, '0');
                        
                        const dateString = `${year}-${month}-${day}`;
                        const isWeekend = currentDate.getDay() === 0 || currentDate.getDay() === 6;
                        
                        const options = { weekday: 'short', day: 'numeric', month: 'short' };
                        const formatted = currentDate.toLocaleDateString('en-US', options);
                        
                        const dayName = currentDate.toLocaleDateString('en-US', { weekday: 'short' });
                        
                        dates.push({
                            date: dateString,
                            isWeekend,
                            formatted,
                            day: dayName
                        });
                        
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                    
                    this.calendarDates = dates;
                },
                
                getEntriesForDateAndShift(date, shiftType) {
                    // Log entry count if this is the first date/shift to help with debugging
                    const isFirstCell = date === this.calendarDates[0]?.date && shiftType === (this.rosterType === 'shift' ? 'morning' : 'oncall');
                    
                    if (isFirstCell) {
                        console.log(`Looking for entries matching date=${date}, shiftType=${shiftType}`);
                        console.log('Total entries available:', this.entries.length);
                    }
                    
                    // Filter entries by date and shift type
                    const entries = this.entries.filter(entry => {
                        const entryDate = entry.shift_date || entry.date;
                        
                        // Convert date objects to string format if needed
                        let formattedEntryDate = entryDate;
                        if (typeof entryDate === 'object' && entryDate !== null) {
                            formattedEntryDate = entryDate.date || entryDate;
                        }
                        
                        // Handle date string format from both RosterEntry and RosterSlot
                        if (typeof formattedEntryDate === 'string') {
                            // Extract YYYY-MM-DD from any date string format
                            if (formattedEntryDate.includes('T')) {
                                formattedEntryDate = formattedEntryDate.split('T')[0];
                            } else if (formattedEntryDate.includes(' ')) {
                                formattedEntryDate = formattedEntryDate.split(' ')[0];
                            }
                        }
                        
                        const matchesDate = formattedEntryDate === date;
                        const matchesShift = entry.shift_type === shiftType;
                        
                        if (isFirstCell && matchesDate) {
                            console.log(`Entry with ID ${entry.id} - date match: ${matchesDate}, shift match: ${matchesShift}`, entry);
                        }
                        
                        return matchesDate && matchesShift;
                    });
                    
                    // Sort entries by staff name for consistent display
                    if (entries.length > 0) {
                        entries.sort((a, b) => {
                            const nameA = a.staff?.name || '';
                            const nameB = b.staff?.name || '';
                            return nameA.localeCompare(nameB);
                        });
                    }
                    
                    if (isFirstCell) {
                        console.log(`Found ${entries.length} entries for ${date}/${shiftType}:`, entries);
                    }
                    
                    return entries;
                },
                
                getShiftTypeLabel(shiftType) {
                    if (this.rosterType === 'shift') {
                        switch(shiftType) {
                            case 'morning': return '{{ __('Morning Shift') }}';
                            case 'evening': return '{{ __('Evening Shift') }}';
                            case 'night': return '{{ __('Night Shift') }}';
                            default: return shiftType;
                        }
                    } else {
                        switch(shiftType) {
                            case 'oncall': return '{{ __('Oncall') }}';
                            case 'standby': return '{{ __('Standby') }}';
                            default: return shiftType;
                        }
                    }
                },
                
                // Drag and drop functionality
                dragStart(e, staff) {
                    this.draggingStaff = staff;
                    e.dataTransfer.setData('text/plain', staff.id);
                },
                
                handleDrop(e, date, shiftType) {
                    e.preventDefault();
                    
                    if (!this.draggingStaff) {
                        return;
                    }
                    
                    // Make sure the date is in YYYY-MM-DD format
                    const formattedDate = date;
                    
                    // For oncall roster type, determine which subsection was dropped on
                    let subsectionIndex = null;
                    if (this.rosterType === 'oncall' && shiftType === 'oncall') {
                        const currentEntries = this.getEntriesForDateAndShift(date, 'oncall');
                        const maxStaff = {{ $roster->department->staffTypeRosters->firstWhere('staff_type', $roster->staff_type)->settings['oncall_staff_count'] ?? 2 }};
                        const oncallTitles = {{ json_encode($oncallStaffTitles ?? []) }};
                        
                        if (currentEntries.length >= maxStaff) {
                            alert(`Cannot add more staff. Maximum ${maxStaff} on-call staff allowed for this date.`);
                            return;
                        }
                        
                        // Check if the drop target is a subsection
                        const target = e.target.closest('.mb-3.border.rounded-md.p-2.bg-gray-50');
                        if (target) {
                            // Find which subsection the drop occurred in
                            const allSubsections = Array.from(e.currentTarget.querySelectorAll('.mb-3.border.rounded-md.p-2.bg-gray-50'));
                            subsectionIndex = allSubsections.indexOf(target);
                            
                            // Check if this subsection already has a staff member
                            const subsectionEntries = currentEntries.filter((entry, idx) => idx % oncallTitles.length === subsectionIndex);
                            if (subsectionEntries.length > 0) {
                                alert(`This position already has a staff member assigned. Please remove existing staff first.`);
                                return;
                            }
                        }
                    }
                    
                    // Create temporary entry
                    const newEntry = {
                        id: 'temp_' + Date.now(),
                        staff_id: this.draggingStaff.id,
                        staff: this.draggingStaff,
                        shift_date: formattedDate,
                        date: formattedDate, // Also include the date field
                        shift_type: shiftType,
                        shift_type_label: this.getShiftTypeLabel(shiftType),
                        is_confirmed: false,
                        is_temp: true,
                        start_time: '00:00:00',
                        end_time: '23:59:59'
                    };
                    
                    // For oncall roster, add subsection information
                    if (subsectionIndex !== null) {
                        newEntry.subsection_index = subsectionIndex;
                    }
                    
                    console.log('Created new entry:', newEntry);
                    
                    // If this is an oncall roster and we have a subsection index,
                    // ensure the entry is placed in the correct position in the array
                    if (this.rosterType === 'oncall' && shiftType === 'oncall' && subsectionIndex !== null) {
                        // Find all existing entries for this date/shift
                        const dateEntries = this.entries.filter(e => 
                            (e.shift_date === formattedDate || e.date === formattedDate) && 
                            e.shift_type === shiftType
                        );
                        
                        // Reorder entries based on subsection index
                        const oncallTitles = {{ json_encode($oncallStaffTitles ?? []) }};
                        const reorderedEntries = [];
                        
                        // First add entries for subsections before this one
                        for (let i = 0; i < subsectionIndex; i++) {
                            const entry = dateEntries.find((e, idx) => idx % oncallTitles.length === i);
                            if (entry) reorderedEntries.push(entry);
                        }
                        
                        // Add the new entry
                        reorderedEntries.push(newEntry);
                        
                        // Then add entries for subsections after this one
                        for (let i = subsectionIndex + 1; i < oncallTitles.length; i++) {
                            const entry = dateEntries.find((e, idx) => idx % oncallTitles.length === i);
                            if (entry) reorderedEntries.push(entry);
                        }
                        
                        // Remove existing entries for this date/shift
                        this.entries = this.entries.filter(e => 
                            !(e.shift_date === formattedDate || e.date === formattedDate) || 
                            e.shift_type !== shiftType
                        );
                        
                        // Add the reordered entries
                        this.entries = [...this.entries, ...reorderedEntries];
                    } else {
                        // Just add the new entry normally
                        this.entries.push(newEntry);
                    }
                    
                    this.draggingStaff = null;
                },
                
                removeEntry(entryId) {
                    const index = this.entries.findIndex(e => e.id === entryId);
                    if (index !== -1) {
                        this.entries.splice(index, 1);
                    }
                },
                
                saveAllChanges() {
                    const tempEntries = this.entries.filter(e => e.is_temp);
                    
                    if (tempEntries.length === 0) {
                        return;
                    }
                    
                    const payload = tempEntries.map(entry => ({
                        staff_id: entry.staff_id,
                        shift_date: entry.shift_date,
                        date: entry.shift_date, // Include date for RosterSlot
                        shift_type: entry.shift_type,
                        roster_id: {{ $roster->id }},
                        is_confirmed: entry.is_confirmed ? 1 : 0,
                        id: entry.id,  // Include the temporary ID
                        start_time: entry.start_time || '00:00:00', // Default start time
                        end_time: entry.end_time || '23:59:59', // Default end time
                        notes: entry.notes || '', // Optional notes
                        subsection_index: entry.subsection_index // Include subsection index for oncall staff
                    }));
                    
                    console.log('Saving temporary entries:', payload);
                    
                    // Show a loading indicator
                    this.isSaving = true;
                    
                    // Access the bulk-store endpoint directly with the URL
                    fetch('/manage/rosters/{{ $roster->id }}/entries/bulk', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ entries: payload })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Map of temp ID to real entry
                            const idMap = {};
                            const newEntriesMap = {};
                            
                            // Create a map of temp IDs to real entries
                            if (data.new_entries && data.created_entries) {
                                console.log('Received new entries from server:', data.new_entries);
                                console.log('Created entries data:', data.created_entries);
                                
                                data.new_entries.forEach((entry, index) => {
                                    idMap[entry.temp_id] = entry.id;
                                    if (data.created_entries[index]) {
                                        newEntriesMap[entry.temp_id] = data.created_entries[index];
                                    }
                                });
                                
                                console.log('Mapped temp IDs to real IDs:', idMap);
                                console.log('Entries map with complete data:', newEntriesMap);
                            }
                            
                            console.log('Entries before update:', JSON.parse(JSON.stringify(this.entries)));
                            
                            // Update temporary entries with real data
                            this.entries = this.entries.map(entry => {
                                if (entry.is_temp && idMap[entry.id]) {
                                    const realId = idMap[entry.id];
                                    const newEntryData = newEntriesMap[entry.id];
                                    
                                    console.log(`Processing temp entry ${entry.id} for staff ${entry.staff?.name || 'unknown'}:`, entry);
                                    
                                    if (newEntryData) {
                                        // Use server data but keep staff info if needed
                                        const updatedEntry = {
                                            ...newEntryData,
                                            staff: newEntryData.staff || entry.staff,
                                            is_temp: false,
                                            shift_type_label: this.getShiftTypeLabel(newEntryData.shift_type)
                                        };
                                        console.log(`Updated entry with server data:`, updatedEntry);
                                        return updatedEntry;
                                    } else {
                                        // At minimum, update ID and remove temp flag
                                        const updatedEntry = {
                                            ...entry,
                                            id: realId,
                                            is_temp: false
                                        };
                                        console.log(`Updated entry with minimal data:`, updatedEntry);
                                        return updatedEntry;
                                    }
                                }
                                return entry;
                            });
                            
                            console.log('Entries after update:', this.entries);
                            
                            // Reprocess entries to ensure all staff data is properly linked
                            this.processEntries();
                            
                            // Force Alpine to refresh the entire display
                            this.$nextTick(() => {
                                console.log('Refreshing display after save');
                                
                                // After entries are updated, force Alpine to re-evaluate the display
                                // This ensures on-call staff count and empty slots are correctly updated
                                this.$el.querySelectorAll('[x-text="getEntriesForDateAndShift(dateInfo.date, \'oncall\').length"]').forEach(el => {
                                    // This trick forces Alpine to re-evaluate the expression
                                    const date = el.closest('tr').getAttribute('data-date');
                                    if (date) {
                                        el.textContent = this.getEntriesForDateAndShift(date, 'oncall').length;
                                    }
                                });
                                
                                // Show success message
                                alert('Changes saved successfully!');
                            });
                            
                        } else {
                            console.error('Error saving entries:', data.message);
                            // Keep the error alert for important errors
                            alert('Error saving entries: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Keep the error alert for important errors
                        alert('Error saving entries: ' + error.message);
                    })
                    .finally(() => {
                        // Hide loading indicator
                        this.isSaving = false;
                    });
                },
                
                // Two-week view navigation
                get visibleCalendarDates() {
                    if (!this.calendarDates || this.calendarDates.length === 0) {
                        return [];
                    }
                    
                    return this.calendarDates.slice(this.visibleStartIndex, this.visibleStartIndex + 14);
                },
                
                get dateRangeText() {
                    if (!this.visibleCalendarDates || this.visibleCalendarDates.length === 0) {
                        return "";
                    }
                    
                    const firstDate = this.visibleCalendarDates[0].date;
                    const lastDate = this.visibleCalendarDates[this.visibleCalendarDates.length - 1].date;
                    
                    // Format dates as DD MMM YYYY
                    const formatDate = (dateStr) => {
                        const date = new Date(dateStr);
                        return date.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
                    };
                    
                    return `${formatDate(firstDate)} - ${formatDate(lastDate)}`;
                },
                
                previousTwoWeeks() {
                    // Go back 14 days but don't go below 0
                    this.visibleStartIndex = Math.max(0, this.visibleStartIndex - 14);
                },
                
                nextTwoWeeks() {
                    // Go forward 14 days but don't exceed the total dates
                    const maxStart = Math.max(0, this.calendarDates.length - 14);
                    this.visibleStartIndex = Math.min(maxStart, this.visibleStartIndex + 14);
                },
                
                // Auto Assignment feature
                autoAssignment() {
                    // Get all visible dates and available shifts
                    const visibleDates = this.visibleCalendarDates;
                    if (visibleDates.length === 0 || this.availableStaff.length === 0) {
                        alert('No dates or staff available for auto assignment');
                        return;
                    }
                    
                    // Define shift types based on roster type
                    const shiftTypes = this.rosterType === 'shift' 
                        ? ['morning', 'evening', 'night'] 
                        : ['oncall'];
                    
                    // Filter shift types based on the current filter
                    const activeShiftTypes = this.shiftFilter === 'all' 
                        ? shiftTypes 
                        : [this.shiftFilter];
                    
                    // Create a mapping of how many assignments each staff has
                    const staffAssignments = {};
                    this.availableStaff.forEach(staff => {
                        staffAssignments[staff.id] = 0;
                    });
                    
                    // Count existing assignments for the current view
                    visibleDates.forEach(dateInfo => {
                        activeShiftTypes.forEach(shiftType => {
                            const entries = this.getEntriesForDateAndShift(dateInfo.date, shiftType);
                            entries.forEach(entry => {
                                if (staffAssignments.hasOwnProperty(entry.staff_id)) {
                                    staffAssignments[entry.staff_id]++;
                                }
                            });
                        });
                    });
                    
                    // Create a list of cells (date/shift combinations) that need staff
                    const emptyCells = [];
                    visibleDates.forEach(dateInfo => {
                        activeShiftTypes.forEach(shiftType => {
                            const entries = this.getEntriesForDateAndShift(dateInfo.date, shiftType);
                            
                            // Get max staff count for this shift type
                            let maxStaff = 1; // Default for regular shifts
                            if (this.rosterType === 'oncall' && shiftType === 'oncall') {
                                maxStaff = {{ $roster->department->staffTypeRosters->firstWhere('staff_type', $roster->staff_type)->settings['oncall_staff_count'] ?? 2 }};
                            }
                            
                            // Add empty cells based on how many more staff can be assigned
                            const slotsNeeded = Math.max(0, maxStaff - entries.length);
                            for (let i = 0; i < slotsNeeded; i++) {
                                emptyCells.push({
                                    date: dateInfo.date,
                                    shiftType
                                });
                            }
                        });
                    });
                    
                    console.log(`Found ${emptyCells.length} empty cells to fill`);
                    
                    // Sort staff by number of assignments (least to most)
                    const sortedStaff = [...this.availableStaff].sort((a, b) => {
                        return (staffAssignments[a.id] || 0) - (staffAssignments[b.id] || 0);
                    });
                    
                    // Create temporary assignments to fill the empty cells
                    const tempEntries = [];
                    
                    emptyCells.forEach(cell => {
                        // Find the staff with the fewest assignments
                        const staff = sortedStaff[0];
                        
                        // Create a new temporary entry
                        const newEntry = {
                            id: 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                            staff_id: staff.id,
                            staff: staff,
                            shift_date: cell.date,
                            date: cell.date,
                            shift_type: cell.shiftType,
                            shift_type_label: this.getShiftTypeLabel(cell.shiftType),
                            is_confirmed: false,
                            is_temp: true,
                            start_time: '00:00:00',
                            end_time: '23:59:59'
                        };
                        
                        // Add to the entries list
                        this.entries.push(newEntry);
                        
                        // Update the staff assignment count
                        staffAssignments[staff.id]++;
                        
                        // Re-sort staff by assignment count
                        sortedStaff.sort((a, b) => {
                            return (staffAssignments[a.id] || 0) - (staffAssignments[b.id] || 0);
                        });
                    });
                    
                    if (emptyCells.length > 0) {
                        alert(`Auto-assigned ${emptyCells.length} shifts. Click "Save All Changes" to make them permanent.`);
                    } else {
                        alert('No empty shifts to assign in the current view.');
                    }
                },
                
                // Print a nicely formatted calendar with roster details
                printCalendar() {
                    // Save current state
                    const currentFilter = this.shiftFilter;
                    
                    // Always show all shifts for printing
                    this.shiftFilter = 'all';
                    
                    // Wait for Alpine to update the DOM with all shifts visible
                    this.$nextTick(() => {
                        // Get the original table
                        const originalTable = document.querySelector('.roster-calendar table');
                        
                        if (!originalTable) {
                            console.error('Calendar table not found');
                            this.shiftFilter = currentFilter;
                            return;
                        }
                        
                        // Create a new window for printing
                        const printWindow = window.open('', '_blank');
                        
                        if (!printWindow) {
                            alert('Please allow pop-ups to print the calendar');
                            this.shiftFilter = currentFilter;
                            return;
                        }
                        
                        // Clone the table for the new window
                        const tableCopy = originalTable.cloneNode(true);
                        
                        // Remove delete buttons from the copy
                        tableCopy.querySelectorAll('button').forEach(button => {
                            button.remove();
                        });
                        
                        // Remove time displays
                        tableCopy.querySelectorAll('div.text-gray-500').forEach(timeDiv => {
                            timeDiv.remove();
                        });
                        
                        // Create roster details HTML
                        const rosterDetailsHTML = `
                            <div class="roster-header">
                                <h1>${this.rosterName}</h1>
                                <div class="roster-details">
                                    <div><strong>Department:</strong> ${this.departmentName}</div>
                                    <div><strong>Staff Type:</strong> ${this.staffTypeLabel}</div>
                                    <div><strong>Period:</strong> ${this.startDate} - ${this.endDate}</div>
                                    <div><strong>Type:</strong> ${this.rosterTypeLabel}</div>
                                </div>
                            </div>
                        `;
                        
                        // Add print-specific CSS with better styling for A4 paper and no empty pages
                        const printCSS = `
                            @page { 
                                size: A4 landscape;
                                margin: 0.5cm;
                                orphans: 4; 
                                widows: 2;
                            }
                            
                            html, body { 
                                margin: 0; 
                                padding: 0; 
                                font-family: Arial, sans-serif;
                                color: #333;
                                background: white;
                                width: 29.7cm;
                                height: 21cm;
                            }
                            
                            /* Header styling - more compact for A4 */
                            .roster-header {
                                margin-bottom: 8px;
                                border-bottom: 1px solid #0056b3;
                                padding-bottom: 5px;
                                break-after: avoid;
                            }
                            
                            .roster-header h1 {
                                margin: 0 0 5px 0;
                                font-size: 16px;
                                color: #0056b3;
                                text-align: center;
                            }
                            
                            .roster-details {
                                display: flex;
                                justify-content: space-between;
                                flex-wrap: wrap;
                                font-size: 10px;
                                line-height: 1.2;
                            }
                            
                            .roster-details div {
                                margin-right: 15px;
                            }
                            
                            .roster-details strong {
                                font-weight: bold;
                            }
                            
                            /* Calendar table styling - optimized for A4 */
                            #calendar-container {
                                width: 100%;
                            }
                            
                            table { 
                                width: 100%; 
                                border-collapse: collapse; 
                                border: 1px solid #444; 
                                margin-top: 3px;
                                table-layout: fixed;
                            }
                            
                            th { 
                                background-color: #f0f0f0; 
                                font-weight: bold; 
                                font-size: 11px; 
                                text-align: center; 
                                padding: 4px 2px; 
                                border: 1px solid #444; 
                                color: #333;
                            }
                            
                            td { 
                                vertical-align: top; 
                                padding: 3px 2px; 
                                border: 1px solid #444; 
                                font-size: 10px; 
                                height: 55px; /* Slightly reduced height to fit 7 rows per page */
                                overflow: hidden;
                            }
                            
                            /* Date column */
                            th:first-child, td:first-child { 
                                width: 15%; 
                                background-color: #f8f8f8; 
                            }
                            
                            td:first-child div:first-child { 
                                font-weight: bold; 
                                font-size: 11px; 
                                margin-bottom: 1px; 
                            }
                            
                            td:first-child div:nth-child(2) { 
                                font-size: 9px; 
                                color: #666; 
                            }
                            
                            /* Staff entries - make more compact */
                            td > div > div { 
                                margin-bottom: 3px; 
                                line-height: 1.2;
                            }
                            
                            td span.font-medium { 
                                display: block; 
                                font-weight: bold; 
                                font-size: 11px; 
                                margin-bottom: 1px;
                                white-space: nowrap;
                                overflow: hidden;
                                text-overflow: ellipsis;
                            }
                            
                            /* Oncall staff title styling for print */
                            .bg-orange-100 {
                                background-color: #fff3e0;
                                color: #d84315;
                                padding: 1px 4px;
                                border-radius: 3px;
                                font-size: 9px;
                                font-weight: bold;
                                display: inline-block;
                                margin-bottom: 2px;
                            }
                            
                            /* Shift columns background colors - subtle and professional */
                            th:nth-child(2), td:nth-child(2) { background-color: #f0f8ff; } /* Morning */
                            th:nth-child(3), td:nth-child(3) { background-color: #fffaed; } /* Evening */
                            th:nth-child(4), td:nth-child(4) { background-color: #f7f5ff; } /* Night */
                            
                            /* Column headers */
                            th:nth-child(2) { color: #0066cc; }
                            th:nth-child(3) { color: #cc7000; }
                            th:nth-child(4) { color: #6600cc; }
                            
                            /* Make columns equal width */
                            th:not(:first-child), td:not(:first-child) {
                                width: 28.33%;
                            }
                            
                            /* Weekends */
                            tr.bg-yellow-50 { 
                                background-color: #fffcf0; 
                            }
                            
                            /* Holiday formatting */
                            .bg-red-100 { 
                                color: #c00; 
                                font-style: italic; 
                                background-color: transparent !important; 
                                font-size: 9px; 
                            }
                            
                            /* Shift labels */
                            [x-text="entry.shift_type_label"] { 
                                font-size: 9px; 
                                color: #0066cc; 
                                display: inline-block; 
                                margin-top: 1px; 
                            }
                            
                            /* Shift time display - hidden */
                            div.text-gray-500 {
                                display: none !important;
                            }
                            
                            /* Print optimizations */
                            @media print {
                                html, body {
                                    width: 100%;
                                    height: auto;
                                }
                                
                                /* Headers get repeated on new pages */
                                thead {
                                    display: table-header-group !important;
                                }
                                
                                /* Prevent rows from breaking across pages */
                                tr {
                                    page-break-inside: avoid !important;
                                    break-inside: avoid !important;
                                }
                                
                                /* Force page breaks at logical points */
                                tr[style*="page-break-before: always"] {
                                    break-before: page !important;
                                    page-break-before: always !important;
                                }
                                
                                /* Prevent empty pages */
                                .roster-header, table {
                                    break-before: avoid-page !important;
                                    page-break-before: avoid !important;
                                }
                            }
                        `;
                        
                        // Create a filename based on staff type and period for saving
                        const cleanStaffType = this.staffTypeLabel.replace(/\s+/g, '_').toLowerCase();
                        const cleanPeriod = this.startDate.replace(/\//g, '-') + "_to_" + this.endDate.replace(/\//g, '-');
                        const saveFilename = `Roster_${cleanStaffType}_${cleanPeriod}`;
                        
                        // Write the content to the new window with roster details, using custom filename
                        printWindow.document.write(`
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <title>${saveFilename}</title>
                                <style>${printCSS}</style>
                            </head>
                            <body>
                                ${rosterDetailsHTML}
                                <div id="calendar-container"></div>
                            </body>
                            </html>
                        `);
                        
                        // Optimize table for A4 size by creating a single table with page break CSS
                        const optimizeForA4 = () => {
                            // Create a single table container
                            const tableContainer = document.createElement('div');
                            
                            // Clone the table
                            const mainTable = tableCopy.cloneNode(true);
                            
                            // Get all rows
                            const allRows = Array.from(mainTable.querySelectorAll('tbody tr'));
                            
                            // Add page break classes at appropriate intervals
                            allRows.forEach((row, index) => {
                                // Add page break before every 7th row (except the first set)
                                if (index > 0 && index % 7 === 0) {
                                    row.style.pageBreakBefore = 'always';
                                    
                                    // Add a small indicator for page break rows (only visible in print)
                                    const firstCell = row.querySelector('td:first-child');
                                    if (firstCell) {
                                        // Add page number to the date cell
                                        const pageNum = Math.floor(index / 7) + 1;
                                        const pageIndicator = document.createElement('div');
                                        pageIndicator.style.fontSize = '8px';
                                        pageIndicator.style.color = '#666';
                                        pageIndicator.style.marginTop = '2px';
                                        pageIndicator.textContent = `Page ${pageNum}`;
                                        firstCell.appendChild(pageIndicator);
                                    }
                                }
                            });
                            
                            // Make sure the header repeats on each page
                            const thead = mainTable.querySelector('thead');
                            if (thead) {
                                thead.style.display = 'table-header-group';
                            }
                            
                            // Add the table to the container
                            tableContainer.appendChild(mainTable);
                            
                            // Add the container to the print window
                            printWindow.document.getElementById('calendar-container').appendChild(tableContainer);
                        };
                        
                        // Run the optimization
                        optimizeForA4();
                        
                        // Wait for images and assets to load
                        printWindow.document.close();
                        
                        // Print once the content is loaded
                        printWindow.onload = function() {
                            setTimeout(() => {
                                printWindow.print();
                                // Close the window after printing (optional)
                                // printWindow.close();
                            }, 300);
                        };
                        
                        // Restore the original filter
                        this.shiftFilter = currentFilter;
                    });
                },
                
                // Export the calendar to Excel
                exportToExcel() {
                    // Save current state
                    const currentFilter = this.shiftFilter;
                    
                    // Always show all shifts for exporting
                    this.shiftFilter = 'all';
                    
                    // Wait for Alpine to update the DOM with all shifts visible
                    this.$nextTick(() => {
                        // Generate filename based on staff type and period
                        const cleanStaffType = this.staffTypeLabel.replace(/\s+/g, '_').toLowerCase();
                        const cleanPeriod = this.startDate.replace(/\//g, '-') + "_to_" + this.endDate.replace(/\//g, '-');
                        const fileName = `Roster_${cleanStaffType}_${cleanPeriod}.xlsx`;
                        
                        // Create header row for the Excel file
                        const headers = ['Date'];
                        
                        // Add shift type headers based on roster type
                        if (this.rosterType === 'shift') {
                            headers.push('Morning Shift', 'Evening Shift', 'Night Shift');
                        } else {
                            headers.push('Oncall');
                        }
                        
                        // Prepare data rows for each date
                        const rows = [];
                        
                        // Use all calendar dates, not just visible ones for export
                        this.calendarDates.forEach(dateInfo => {
                            // Create row with date information
                            const dateText = `${dateInfo.formatted} (${dateInfo.day})`;
                            const row = [dateText];
                            
                            // Add data for each shift type
                            const shiftTypes = this.rosterType === 'shift' 
                                ? ['morning', 'evening', 'night'] 
                                : ['oncall'];
                                
                            shiftTypes.forEach(shiftType => {
                                const entries = this.getEntriesForDateAndShift(dateInfo.date, shiftType);
                                if (entries.length > 0) {
                                    // Format staff assignments into a single cell
                                    const staffNames = entries.map(entry => {
                                        return entry.staff.name;
                                    }).join('\n');
                                    row.push(staffNames);
                                } else {
                                    row.push(''); // Empty cell if no assignments
                                }
                            });
                            
                            rows.push(row);
                        });
                        
                        // Add roster details as header rows
                        const titleRow = [`${this.rosterName}`];
                        for (let i = 1; i < headers.length; i++) titleRow.push('');
                        
                        const detailsRows = [
                            [`Department: ${this.departmentName}`, '', '', ''],
                            [`Staff Type: ${this.staffTypeLabel}`, '', '', ''],
                            [`Period: ${this.startDate} - ${this.endDate}`, '', '', ''],
                            [`Type: ${this.rosterTypeLabel}`, '', '', '']
                        ];
                        
                        // Combine all rows
                        const allRows = [
                            titleRow,
                            ...detailsRows,
                            [], // Empty row for spacing
                            headers,
                            ...rows
                        ];
                        
                        // Create a worksheet
                        const worksheet = XLSX.utils.aoa_to_sheet(allRows);
                        
                        // Set column widths
                        const colWidths = [
                            { wch: 25 }, // Date column
                            { wch: 30 }, // First shift column
                            { wch: 30 }, // Second shift column
                            { wch: 30 }  // Third shift column (if applicable)
                        ];
                        worksheet['!cols'] = colWidths;
                        
                        // Create a workbook
                        const workbook = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(workbook, worksheet, "Roster");
                        
                        // Save to file
                        XLSX.writeFile(workbook, fileName);
                        
                        // Restore the original filter
                        this.shiftFilter = currentFilter;
                    });
                },
                
                // Export the calendar data in different formats (CSV or HTML)
                exportData(format = 'csv') {
                    // Save current state
                    const currentFilter = this.shiftFilter;
                    
                    // Always show all shifts for exporting
                    this.shiftFilter = 'all';
                    
                    // Wait for Alpine to update the DOM with all shifts visible
                    this.$nextTick(() => {
                        try {
                            // Generate filename based on staff type and period
                            const cleanStaffType = this.staffTypeLabel.replace(/\s+/g, '_').toLowerCase();
                            const cleanPeriod = this.startDate.replace(/\//g, '-') + "_to_" + this.endDate.replace(/\//g, '-');
                            const fileExt = format === 'csv' ? 'csv' : 'html';
                            const fileName = `Roster_${cleanStaffType}_${cleanPeriod}.${fileExt}`;
                            
                            // Create header row for the file
                            const headers = ['Date'];
                            
                            // Add shift type headers based on roster type
                            if (this.rosterType === 'shift') {
                                headers.push('Morning Shift', 'Evening Shift', 'Night Shift');
                            } else {
                                headers.push('Oncall');
                            }
                            
                            // Prepare data rows for each date
                            const rows = [];
                            
                            // Use all calendar dates, not just visible ones for export
                            this.calendarDates.forEach(dateInfo => {
                                // Create row with date information
                                const dateText = `${dateInfo.formatted} (${dateInfo.day})`;
                                const row = [dateText];
                                
                                // Add data for each shift type
                                const shiftTypes = this.rosterType === 'shift' 
                                    ? ['morning', 'evening', 'night'] 
                                    : ['oncall'];
                                    
                                shiftTypes.forEach(shiftType => {
                                    const entries = this.getEntriesForDateAndShift(dateInfo.date, shiftType);
                                    if (entries.length > 0) {
                                        // Format staff assignments into a single cell
                                        const staffNames = entries.map(entry => {
                                            return entry.staff.name;
                                        }).join('\n');
                                        row.push(staffNames);
                                    } else {
                                        row.push(''); // Empty cell if no assignments
                                    }
                                });
                                
                                rows.push(row);
                            });
                            
                            // Add roster details as header rows
                            const titleRow = [`${this.rosterName}`];
                            for (let i = 1; i < headers.length; i++) titleRow.push('');
                            
                            const detailsRows = [
                                [`Department: ${this.departmentName}`, '', '', ''],
                                [`Staff Type: ${this.staffTypeLabel}`, '', '', ''],
                                [`Period: ${this.startDate} - ${this.endDate}`, '', '', ''],
                                [`Type: ${this.rosterTypeLabel}`, '', '', '']
                            ];
                            
                            // Combine all rows
                            const allRows = [
                                titleRow,
                                ...detailsRows,
                                [], // Empty row for spacing
                                headers,
                                ...rows
                            ];
                            
                            // Choose the appropriate export format
                            if (format === 'html') {
                                // Create HTML table for export
                                this.exportAsHTML(fileName, headers, allRows);
                            } else {
                                // Export as CSV with two-week chunks
                                this.exportAsCSVFallback(fileName, allRows);
                            }
                        } catch (error) {
                            console.error('Export error:', error);
                            alert(`Export error: ${error.message}`);
                        } finally {
                            // Restore the original filter
                            this.shiftFilter = currentFilter;
                        }
                    });
                },
                
                // Add the fallback CSV export method
                exportAsCSVFallback(fileName, allRows) {
                    try {
                        // Simple function to convert a cell to CSV format
                        const escapeCSV = (cell) => {
                            if (cell === null || cell === undefined) return '';
                            let text = String(cell);
                            // If the cell contains commas, quotes, or newlines, wrap it in quotes
                            if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                                // Double quotes inside the cell need to be escaped with another double quote
                                text = text.replace(/"/g, '""');
                                return `"${text}"`;
                            }
                            return text;
                        };
                        
                        // Extract headers and data rows
                        const headerRows = allRows.slice(0, 6); // Title, details, empty row, and column headers
                        const dataRows = allRows.slice(6); // Actual calendar data
                        
                        // Build CSV content
                        let csvContent = '';
                        
                        // First add header information
                        headerRows.forEach(row => {
                            csvContent += row.map(cell => escapeCSV(cell)).join(',') + '\n';
                        });
                        
                        // Now process data rows in two-week chunks
                        for (let i = 0; i < dataRows.length; i += 14) {
                            // If this is not the first chunk, add a separator and repeat headers
                            if (i > 0) {
                                csvContent += '\n\n'; // Empty rows as separator
                                
                                // Add period indicator for this two-week period
                                let startDate = '';
                                let endDate = '';
                                
                                if (i < dataRows.length) {
                                    // Extract date from first column, which includes date info
                                    startDate = dataRows[i][0].split(' ')[0];
                                }
                                
                                const endIndex = Math.min(i + 13, dataRows.length - 1);
                                if (endIndex < dataRows.length) {
                                    // Extract date from last row in this chunk
                                    endDate = dataRows[endIndex][0].split(' ')[0];
                                }
                                
                                const periodText = [`Two-Week Period: ${startDate} - ${endDate}`];
                                for (let j = 1; j < headerRows[5].length; j++) {
                                    periodText.push(''); // Empty cells to match column count
                                }
                                
                                // Add period text and column headers again
                                csvContent += periodText.map(cell => escapeCSV(cell)).join(',') + '\n';
                                csvContent += headerRows[5].map(cell => escapeCSV(cell)).join(',') + '\n';
                            }
                            
                            // Add the rows for this two-week chunk
                            const currentChunk = dataRows.slice(i, i + 14);
                            currentChunk.forEach(row => {
                                csvContent += row.map(cell => escapeCSV(cell)).join(',') + '\n';
                            });
                        }
                        
                        // Download the CSV file
                        this.downloadFile(csvContent, fileName, 'text/csv');
                    } catch (error) {
                        console.error('CSV fallback error:', error);
                        alert(`CSV export error: ${error.message}`);
                    }
                },
                
                // Helper function to download file data
                downloadFile(content, fileName, contentType) {
                    const blob = new Blob([content], { type: contentType });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = fileName;
                    a.click();
                    URL.revokeObjectURL(url);
                },
                
                // Export as HTML table
                exportAsHTML(fileName, headers, rows) {
                    // Create basic CSS for the HTML export
                    const css = `
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h1 { color: #0056b3; }
                        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                        th { background-color: #f0f0f0; text-align: left; padding: 8px; border: 1px solid #ddd; }
                        td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
                        .title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
                        .details { margin-bottom: 20px; }
                        .details div { margin-bottom: 5px; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                        td:first-child { font-weight: bold; }
                        /* Hide any time information */
                        .text-gray-500, div[class*="text-gray"] { display: none; }
                    `;
                    
                    // Start building HTML content
                    let html = `<!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <title>${this.rosterName}</title>
                        <style>${css}</style>
                    </head>
                    <body>`;
                    
                    // Add title and details
                    html += `<div class="title">${this.rosterName}</div>`;
                    html += `<div class="details">
                        <div><strong>Department:</strong> ${this.departmentName}</div>
                        <div><strong>Staff Type:</strong> ${this.staffTypeLabel}</div>
                        <div><strong>Period:</strong> ${this.startDate} - ${this.endDate}</div>
                        <div><strong>Type:</strong> ${this.rosterTypeLabel}</div>
                    </div>`;
                    
                    // Start table
                    html += '<table>';
                    
                    // Add table headers
                    html += '<thead><tr>';
                    headers.forEach(header => {
                        html += `<th>${header}</th>`;
                    });
                    html += '</tr></thead>';
                    
                    // Add table rows, skipping the title and details rows
                    html += '<tbody>';
                    const dataRows = rows.slice(6); // Skip title, details, and empty row
                    dataRows.forEach(row => {
                        html += '<tr>';
                        row.forEach((cell, index) => {
                            // Replace newlines with <br> for HTML display
                            const cellContent = cell ? cell.replace(/\n/g, '<br>') : '';
                            html += `<td>${cellContent}</td>`;
                        });
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    
                    // Close HTML
                    html += '</body></html>';
                    
                    // Download the HTML file
                    this.downloadFile(html, fileName, 'text/html');
                },
            }));
        });
    </script>

    @push('styles')
    <!-- Print-specific styles -->
    <style media="print">
        /* Reset all styling for print */
        * {
            box-sizing: border-box !important;
        }
        
        /* Hide everything by default */
        body * {
            display: none !important;
        }
        
        /* Only show what we explicitly want */
        .print-title, .print-roster-details, .roster-calendar-container,
        .roster-calendar-container *, body.printing-roster .print-title,
        body.printing-roster .print-roster-details {
            display: block !important;
        }
        
        /* Hide specific elements */
        .flex.justify-between.items-center.mb-4, 
        button[type="button"][class*="px-3 py-1"], 
        .text-center,
        .dateRangeText,
        .roster-calendar-container > *:not(table) {
            display: none !important;
        }
        
        /* Basic page setup */
        @page {
            size: landscape;
            margin: 0.5cm;
        }
        
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background: white !important;
            font-family: Arial, sans-serif !important;
            font-size: 12px !important;
        }
        
        /* Layout containers reset */
        .py-12, .max-w-7xl, .p-6 {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
            box-shadow: none !important;
            border: none !important;
        }
        
        /* Title styles */
        .print-title {
            text-align: center !important;
            font-size: 18px !important;
            font-weight: bold !important;
            margin: 0 0 5px 0 !important;
            padding: 0 !important;
            display: none !important; /* Hide title as per example */
        }
        
        /* Roster details styling */
        .print-roster-details {
            margin: 0 0 10px 0 !important;
            padding: 0 !important;
            border-bottom: 1px solid #000 !important;
            padding-bottom: 5px !important;
            display: none !important; /* Hide details as per example */
        }
        
        /* Calendar container */
        .roster-calendar {
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: visible !important;
        }
        
        /* Table formatting */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 0 !important;
            padding: 0 !important;
            page-break-inside: auto !important;
        }
        
        /* Table header */
        thead {
            display: table-header-group !important;
        }
        
        /* Column formatting */
        th {
            background-color: #f9f9f9 !important;
            color: black !important;
            font-weight: bold !important;
            text-align: center !important;
            padding: 8px 5px !important;
            border: 1px solid #000 !important;
            font-size: 14px !important;
        }
        
        /* Make date column narrower */
        th:first-child, td:first-child {
            width: 15% !important;
        }
        
        /* Make shift columns equal width */
        th:not(:first-child), td:not(:first-child) {
            width: 28.33% !important;
        }
        
        /* Row formatting */
        tr {
            page-break-inside: avoid !important;
        }
        
        /* Weekend highlighting */
        tr.bg-yellow-50 {
            background-color: #fffbe6 !important;
        }
        
        /* Cell formatting */
        td {
            vertical-align: top !important;
            padding: 8px 5px !important;
            border: 1px solid #000 !important;
            font-size: 12px !important;
            height: 80px !important; /* Fixed height for cells */
        }
        
        /* Date cell formatting */
        td:first-child {
            font-weight: bold !important;
            background-color: #f9f9f9 !important;
        }
        
        td:first-child div:first-child {
            font-weight: bold !important;
            font-size: 14px !important;
            margin-bottom: 2px !important;
        }
        
        td:first-child div:nth-child(2) {
            font-size: 12px !important;
            color: #666 !important;
        }
        
        /* Holiday formatting */
        td:first-child .bg-red-100 {
            color: #c00 !important;
            font-style: italic !important;
            padding: 0 !important;
            margin-top: 4px !important;
            border: none !important;
            background: transparent !important;
            font-size: 11px !important;
        }
        
        /* Staff entry cards */
        td > div[x-for] > div {
            border: none !important;
            margin-bottom: 8px !important;
            padding: 0 !important;
            background-color: transparent !important;
            font-size: 12px !important;
        }
        
        /* Show template elements */
        template {
            display: none !important;
        }
        
        [x-for] {
            display: block !important;
        }
        
        [x-cloak] {
            display: block !important;
        }
        
        /* Show staff names clearly */
        td span.font-medium {
            display: block !important;
            font-weight: bold !important;
            font-size: 14px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            width: 100% !important;
            color: #000 !important;
        }
        
        /* Clean staff entry layout */
        td div.flex {
            display: block !important;
        }
        
        /* Hide delete buttons */
        button.text-gray-400 {
            display: none !important;
        }
        
        /* Shift labels - hide as per example */
        span[x-text="entry.shift_type_label"] {
            font-size: 12px !important;
            color: #0000cc !important;
            font-weight: normal !important;
            display: inline-block !important;
            margin-top: 2px !important;
        }
        
        /* Shift type colors */
        /* Morning shift */
        th.bg-blue-50 {
            background-color: #eff6ff !important;
            color: #1e40af !important;
        }
        
        /* Evening shift */
        th.bg-yellow-50 {
            background-color: #fffbeb !important;
            color: #854d0e !important;
        }
        
        /* Night shift */
        th.bg-indigo-50 {
            background-color: #eef2ff !important;
            color: #3730a3 !important;
        }
        
        /* Shift time display - hidden */
        div.text-gray-500 {
            display: none !important;
        }
        
        /* Fix element visibility */
        .md\:col-span-3, .overflow-x-auto {
            display: block !important;
            width: 100% !important;
        }
        
        /* Remove background colors for shift labels */
        span.px-1.rounded-full.text-xs.bg-blue-100,
        span.px-1.rounded-full.text-xs.bg-indigo-100,
        span.px-1.rounded-full.text-xs.bg-purple-100 {
            background-color: transparent !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }
        
        /* Table coloring to match example */
        table {
            border: 1px solid #000 !important;
        }
        
        /* Color shift columns */
        th:nth-child(2), td:nth-child(2) {
            background-color: #f0f8ff !important;
        }
        
        th:nth-child(3), td:nth-child(3) {
            background-color: #fffff0 !important;
        }
        
        th:nth-child(4), td:nth-child(4) {
            background-color: #f0f0ff !important;
        }
        
        /* Full page margin reset - get content right to the edge */
        .max-w-7xl, .sm\:px-6, .lg\:px-8, .py-12, .p-6 {
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Ensure table starts at the very top of the print area */
        .roster-calendar {
            margin-top: 0 !important;
        }
    }
    </style>
    @endpush
    
    @push('scripts')
    <!-- No external libraries needed for CSV/HTML export -->
    @endpush
</x-app-layout> 