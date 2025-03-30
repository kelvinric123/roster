<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Shift Settings') }}
            </h2>
            <a href="{{ route('roster-shift-settings.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                + {{ __('Add New Setting') }}
            </a>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Filter Settings') }}</h3>
                    <form action="{{ route('roster-shift-settings.index') }}" method="GET" class="flex flex-wrap gap-4">
                        <div class="w-full md:w-1/4">
                            <x-input-label for="filter_type" :value="__('View By')" />
                            <select id="filter_type" name="filter_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="this.form.submit()">
                                <option value="department" {{ request('filter_type', 'department') == 'department' ? 'selected' : '' }}>Department</option>
                                <option value="staff_type" {{ request('filter_type') == 'staff_type' ? 'selected' : '' }}>Staff Type</option>
                                <option value="roster_type" {{ request('filter_type') == 'roster_type' ? 'selected' : '' }}>Roster Type</option>
                            </select>
                        </div>
                        
                        <div class="w-full md:w-1/4">
                            <x-input-label for="staff_type" :value="__('Staff Type')" />
                            <select id="staff_type" name="staff_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="this.form.submit()">
                                <option value="">All Staff Types</option>
                                @foreach($staffTypes as $key => $label)
                                    <option value="{{ $key }}" {{ request('staff_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="w-full md:w-1/4">
                            <x-input-label for="roster_type" :value="__('Roster Type')" />
                            <select id="roster_type" name="roster_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="this.form.submit()">
                                <option value="">All Roster Types</option>
                                <option value="weekly" {{ request('roster_type') == 'weekly' ? 'selected' : '' }}>Weekly Roster</option>
                                <option value="monthly" {{ request('roster_type') == 'monthly' ? 'selected' : '' }}>Monthly Roster</option>
                                <option value="oncall" {{ request('roster_type') == 'oncall' ? 'selected' : '' }}>On Call Roster</option>
                            </select>
                        </div>
                        
                        <div class="w-full md:w-1/4 flex items-end">
                            <x-primary-button>
                                {{ __('Apply Filters') }}
                            </x-primary-button>
                            @if(request('staff_type') || request('roster_type') || request('filter_type', 'department') != 'department')
                                <a href="{{ route('roster-shift-settings.index') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Reset') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            
            @if (count($departments) > 0)
                @if(request('filter_type') == 'staff_type')
                    @php
                        $filteredSettings = collect();
                        foreach ($departments as $department) {
                            $filteredSettings = $filteredSettings->merge($department->shiftSettings);
                        }
                        $staffTypeGroups = $filteredSettings->groupBy('staff_type');
                    @endphp
                    
                    @foreach($staffTypeGroups as $staffType => $settings)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">
                                    @if($staffType)
                                        {{ $staffTypes[$staffType] ?? ucfirst($staffType) }} Staff
                                    @else
                                        Unassigned Staff Type
                                    @endif
                                </h3>
                                
                                @if(count($settings) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="py-3 px-4 text-left">{{ __('Department') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Roster Type') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Shift Name') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Type') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Start Time') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('End Time') }}</th>
                                                    <th class="py-3 px-4 text-center">{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($settings as $setting)
                                                    <tr class="border-b hover:bg-gray-50">
                                                        <td class="py-3 px-4">
                                                            {{ $setting->department->name }}
                                                        </td>
                                                        <td class="py-3 px-4">
                                                            @if($setting->roster_type == 'weekly')
                                                                Weekly Roster
                                                            @elseif($setting->roster_type == 'monthly')
                                                                Monthly Roster
                                                            @elseif($setting->roster_type == 'oncall')
                                                                On Call Roster
                                                            @else
                                                                {{ ucfirst($setting->roster_type ?? 'Unknown') }}
                                                            @endif
                                                        </td>
                                                        <td class="py-3 px-4 font-medium">{{ $setting->name }}</td>
                                                        <td class="py-3 px-4">{{ $setting->shift_type_label }}</td>
                                                        <td class="py-3 px-4">{{ $setting->start_time->format('H:i') }}</td>
                                                        <td class="py-3 px-4">{{ $setting->end_time->format('H:i') }}</td>
                                                        <td class="py-3 px-4 text-center">
                                                            <div class="flex items-center justify-center space-x-2">
                                                                <a href="{{ route('roster-shift-settings.edit', $setting) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </a>
                                                                <form action="{{ route('roster-shift-settings.destroy', $setting) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this setting?') }}')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-gray-500">No shift settings configured for this staff type.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                
                @elseif(request('filter_type') == 'roster_type')
                    @php
                        $filteredSettings = collect();
                        foreach ($departments as $department) {
                            $filteredSettings = $filteredSettings->merge($department->shiftSettings);
                        }
                        $rosterTypeGroups = $filteredSettings->groupBy('roster_type');
                    @endphp
                    
                    @foreach($rosterTypeGroups as $rosterType => $settings)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">
                                    @if($rosterType == 'weekly')
                                        Weekly Roster
                                    @elseif($rosterType == 'monthly')
                                        Monthly Roster
                                    @elseif($rosterType == 'oncall')
                                        On Call Roster
                                    @else
                                        {{ ucfirst($rosterType ?? 'Unknown') }} Roster
                                    @endif
                                </h3>
                                
                                @if(count($settings) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="py-3 px-4 text-left">{{ __('Department') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Staff Type') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Shift Name') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Type') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Start Time') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('End Time') }}</th>
                                                    <th class="py-3 px-4 text-center">{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($settings as $setting)
                                                    <tr class="border-b hover:bg-gray-50">
                                                        <td class="py-3 px-4">
                                                            {{ $setting->department->name }}
                                                        </td>
                                                        <td class="py-3 px-4">
                                                            {{ $setting->staff_type_label }}
                                                        </td>
                                                        <td class="py-3 px-4 font-medium">{{ $setting->name }}</td>
                                                        <td class="py-3 px-4">{{ $setting->shift_type_label }}</td>
                                                        <td class="py-3 px-4">{{ $setting->start_time->format('H:i') }}</td>
                                                        <td class="py-3 px-4">{{ $setting->end_time->format('H:i') }}</td>
                                                        <td class="py-3 px-4 text-center">
                                                            <div class="flex items-center justify-center space-x-2">
                                                                <a href="{{ route('roster-shift-settings.edit', $setting) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </a>
                                                                <form action="{{ route('roster-shift-settings.destroy', $setting) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this setting?') }}')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-gray-500">No shift settings configured for this roster type.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    
                @else
                    {{-- Default: Group by Department --}}
                    @foreach ($departments as $department)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $department->name }}</h3>
                                
                                @if ($department->shiftSettings->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="py-3 px-4 text-left">{{ __('Staff Type') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Roster Type') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Shift Name') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Type') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('Start Time') }}</th>
                                                    <th class="py-3 px-4 text-left">{{ __('End Time') }}</th>
                                                    <th class="py-3 px-4 text-center">{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($department->shiftSettings as $setting)
                                                    <tr class="border-b hover:bg-gray-50">
                                                        <td class="py-3 px-4">
                                                            {{ $setting->staff_type_label }}
                                                        </td>
                                                        <td class="py-3 px-4">
                                                            @if($setting->roster_type == 'weekly')
                                                                Weekly Roster
                                                            @elseif($setting->roster_type == 'monthly')
                                                                Monthly Roster
                                                            @elseif($setting->roster_type == 'oncall')
                                                                On Call Roster
                                                            @else
                                                                {{ ucfirst($setting->roster_type ?? 'Unknown') }}
                                                            @endif
                                                        </td>
                                                        <td class="py-3 px-4 font-medium">{{ $setting->name }}</td>
                                                        <td class="py-3 px-4">{{ $setting->shift_type_label }}</td>
                                                        <td class="py-3 px-4">{{ $setting->start_time->format('H:i') }}</td>
                                                        <td class="py-3 px-4">{{ $setting->end_time->format('H:i') }}</td>
                                                        <td class="py-3 px-4 text-center">
                                                            <div class="flex items-center justify-center space-x-2">
                                                                <a href="{{ route('roster-shift-settings.edit', $setting) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </a>
                                                                <form action="{{ route('roster-shift-settings.destroy', $setting) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this setting?') }}')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-gray-500">No shift settings configured for this department.</div>
                                    <div class="mt-2">
                                        <a href="{{ route('roster-shift-settings.create', ['department_id' => $department->id]) }}" class="text-blue-600 hover:underline">
                                            Configure shift settings for this department
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        {{ __('No departments found. Please create departments first.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 