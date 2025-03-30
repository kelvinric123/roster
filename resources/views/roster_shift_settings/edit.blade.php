<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Shift Setting') }}
            </h2>
            <a href="{{ route('roster-shift-settings.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                {{ __('Back to Settings') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('roster-shift-settings.update', $rosterShiftSetting) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="department_id" :value="__('Department')" />
                            <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $rosterShiftSetting->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }} ({{ $department->code }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('department_id')" />
                        </div>

                        <div>
                            <x-input-label for="staff_type" :value="__('Staff Type')" />
                            <select id="staff_type" name="staff_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select Staff Type</option>
                                @foreach($staffTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('staff_type', $rosterShiftSetting->staff_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('staff_type')" />
                        </div>

                        <div>
                            <x-input-label for="roster_type" :value="__('Roster Type')" />
                            <select id="roster_type" name="roster_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required onchange="updateShiftTypeOptions()">
                                <option value="">Select Roster Type</option>
                                @foreach($rosterTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('roster_type', $rosterShiftSetting->roster_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('roster_type')" />
                            <p class="text-sm text-gray-500 mt-1">
                                Note: Only shift-based roster types (Weekly, Monthly) can have shift settings like Morning, Evening, Night.
                                On-call roster can only have On-call and Standby shifts.
                            </p>
                        </div>

                        <div>
                            <x-input-label for="shift_type" :value="__('Shift Type')" />
                            <select id="shift_type" name="shift_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select Shift Type</option>
                                @foreach($shiftTypes as $key => $label)
                                    <option value="{{ $key }}" data-category="{{ in_array($key, ['oncall', 'standby']) ? 'oncall' : 'regular' }}" {{ old('shift_type', $rosterShiftSetting->shift_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('shift_type')" />
                        </div>

                        <div>
                            <x-input-label for="name" :value="__('Shift Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $rosterShiftSetting->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            <p class="text-sm text-gray-500 mt-1">Enter a custom name for this shift (e.g., "Morning A", "Evening Special", "Night Emergency")</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="start_time" :value="__('Start Time')" />
                                <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full" :value="old('start_time', $rosterShiftSetting->start_time->format('H:i'))" required />
                                <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
                            </div>

                            <div>
                                <x-input-label for="end_time" :value="__('End Time')" />
                                <x-text-input id="end_time" name="end_time" type="time" class="mt-1 block w-full" :value="old('end_time', $rosterShiftSetting->end_time->format('H:i'))" required />
                                <x-input-error class="mt-2" :messages="$errors->get('end_time')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('description', $rosterShiftSetting->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button>
                                {{ __('Update Setting') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateShiftTypeOptions() {
            const rosterType = document.getElementById('roster_type').value;
            const shiftTypeSelect = document.getElementById('shift_type');
            const options = shiftTypeSelect.querySelectorAll('option');
            
            // Store the currently selected value if any
            const currentValue = shiftTypeSelect.value;
            let valueStillAvailable = false;
            
            for (const option of options) {
                if (option.value === '') continue; // Skip the placeholder option
                
                if (rosterType === 'oncall') {
                    // For oncall roster, only show oncall and standby shift types
                    if (option.dataset.category === 'oncall') {
                        option.style.display = '';
                        if (option.value === currentValue) valueStillAvailable = true;
                    } else {
                        option.style.display = 'none';
                    }
                } else if (rosterType === 'weekly' || rosterType === 'monthly') {
                    // For regular rosters, hide oncall shift types
                    if (option.dataset.category !== 'oncall') {
                        option.style.display = '';
                        if (option.value === currentValue) valueStillAvailable = true;
                    } else {
                        option.style.display = 'none';
                    }
                } else {
                    // If no roster type selected or any other case, show all options
                    option.style.display = '';
                    if (option.value === currentValue) valueStillAvailable = true;
                }
            }
            
            // If the current value is no longer available, reset the selection
            if (!valueStillAvailable && currentValue !== '') {
                shiftTypeSelect.value = '';
            }
        }
        
        // Run the function once on page load to set initial state
        document.addEventListener('DOMContentLoaded', function() {
            updateShiftTypeOptions();
        });
    </script>
</x-app-layout> 