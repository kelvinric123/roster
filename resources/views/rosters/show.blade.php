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
                                                <option value="oncall">{{ __('First Oncall') }}</option>
                                                <option value="standby">{{ __('Second Oncall') }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <!-- Save Changes Button -->
                                    <div class="mt-4 flex justify-end">
                                        <button type="button" 
                                            @click="saveAllChanges" 
                                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="!entries.some(e => e.is_temp)"
                                            :class="{'relative': false}">
                                            Save All Changes
                                            <template x-if="false">
                                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                    <span x-text="entries.filter(e => e.is_temp).length"></span>
                                                </span>
                                            </template>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="overflow-x-auto roster-calendar">
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
                                                            {{ __('First Oncall') }}
                                                        </th>
                                                    </template>
                                                    <template x-if="shiftFilter === 'all' || shiftFilter === 'standby'">
                                                        <th class="border border-gray-300 px-4 py-2 text-left bg-purple-50">
                                                            {{ __('Second Oncall') }}
                                                        </th>
                                                    </template>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(dateInfo, index) in calendarDates" :key="dateInfo.date">
                                                <tr :class="{'bg-yellow-50': dateInfo.isWeekend}">
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
                                                                        <template x-if="entry.start_time && entry.end_time">
                                                                            <div class="text-gray-500">
                                                                                <span x-text="formatTime(entry.start_time)"></span> - 
                                                                                <span x-text="formatTime(entry.end_time)"></span>
                                                                            </div>
                                                                        </template>
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
                                                                        <template x-if="entry.start_time && entry.end_time">
                                                                            <div class="text-gray-500">
                                                                                <span x-text="formatTime(entry.start_time)"></span> - 
                                                                                <span x-text="formatTime(entry.end_time)"></span>
                                                                            </div>
                                                                        </template>
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
                                                                        <template x-if="entry.start_time && entry.end_time">
                                                                            <div class="text-gray-500">
                                                                                <span x-text="formatTime(entry.start_time)"></span> - 
                                                                                <span x-text="formatTime(entry.end_time)"></span>
                                                                            </div>
                                                                        </template>
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
                                                                <template x-for="entry in getEntriesForDateAndShift(dateInfo.date, 'oncall')" :key="entry.id">
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
                                                                            <span class="font-medium" x-text="entry.staff.name"></span>
                                                                            <button type="button" class="text-gray-400 hover:text-red-500" @click="removeEntry(entry.id)">
                                                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                        <div>
                                                                            <span class="px-1 rounded-full text-xs bg-orange-100 text-orange-800" x-text="entry.shift_type_label"></span>
                                                                        </div>
                                                                        <template x-if="entry.start_time && entry.end_time">
                                                                            <div class="text-gray-500">
                                                                                <span x-text="formatTime(entry.start_time)"></span> - 
                                                                                <span x-text="formatTime(entry.end_time)"></span>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </td>
                                                        </template>
                                                        
                                                        <template x-if="shiftFilter === 'all' || shiftFilter === 'standby'">
                                                            <td class="border border-gray-300 px-4 py-2 min-h-20 drop-zone"
                                                                :id="'cell-' + dateInfo.date + '-standby'"
                                                                @dragover.prevent
                                                                @drop="handleDrop($event, dateInfo.date, 'standby')">
                                                                <template x-for="entry in getEntriesForDateAndShift(dateInfo.date, 'standby')" :key="entry.id">
                                                                    <div 
                                                                        :class="{
                                                                            'mb-1 p-1 text-xs rounded shadow-sm border': true,
                                                                            'bg-amber-50 border-amber-300': entry.is_confirmed && !entry.is_temp,
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
                                                                            <span class="px-1 rounded-full text-xs bg-amber-100 text-amber-800" x-text="entry.shift_type_label"></span>
                                                                        </div>
                                                                        <template x-if="entry.start_time && entry.end_time">
                                                                            <div class="text-gray-500">
                                                                                <span x-text="formatTime(entry.start_time)"></span> - 
                                                                                <span x-text="formatTime(entry.end_time)"></span>
                                                                            </div>
                                                                        </template>
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
                rosterType: '{{ $roster->roster_type }}',
                draggingStaff: null,
                
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
                            case 'oncall': return '{{ __('First Oncall') }}';
                            case 'standby': return '{{ __('Second Oncall') }}';
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
                    
                    console.log('Created new entry:', newEntry);
                    
                    this.entries.push(newEntry);
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
                        notes: entry.notes || '' // Optional notes
                    }));
                    
                    console.log('Saving temporary entries:', payload);
                    
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
                            
                            // Force Alpine to refresh the display
                            this.$nextTick(() => {
                                console.log('Refreshing display after save');
                            });
                            
                            // Success message is disabled - entries will be saved silently
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
                    });
                }
            }));
        });
    </script>
</x-app-layout> 