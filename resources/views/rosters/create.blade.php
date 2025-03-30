<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create New Roster') }}</h2>
            <div>
                <a href="{{ route('manage.rosters.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                    {{ __('Back to Rosters') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($errors->any())
                        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-medium text-blue-800 mb-2">{{ __('Roster Type Information') }}</h3>
                        <p class="text-blue-700">{{ __('Each department has 4 staff types (Specialist Doctor, Medical Officer, Houseman Officer, and Nurse).') }}</p>
                        <p class="text-blue-700">{{ __('Each staff type has its own roster type (On Call or Shift) which is configured at the department level.') }}</p>
                        <p class="text-blue-700 mt-2">{{ __('The roster type will be automatically set based on the department and staff type selected.') }}</p>
                    </div>
                    
                    <form action="{{ route('manage.rosters.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Roster Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="department_id" :value="__('Department')" />
                                <select id="department_id" name="department_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">{{ __('Select Department') }}</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" 
                                            {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                                <p class="text-sm text-gray-500 mt-1">{{ __('You must select a department first.') }}</p>
                            </div>
                            
                            <div>
                                <x-input-label for="staff_type" :value="__('Staff Type')" />
                                <select id="staff_type" name="staff_type" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                    <option value="">{{ __('Select Staff Type') }}</option>
                                    @foreach ($staffTypes as $value => $label)
                                        <option value="{{ $value }}" {{ old('staff_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('staff_type')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="roster_type_display" :value="__('Roster Type')" />
                                <div id="roster_type_display" class="mt-2 p-3 bg-gray-100 rounded-md border border-gray-300 h-10 flex items-center">
                                    <span id="roster_type_value" class="font-medium">{{ __('Select department and staff type first') }}</span>
                                </div>
                                <p id="roster_type_explanation" class="text-sm text-gray-500 mt-1"></p>
                            </div>
                            
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="date_period" :value="__('Period')" />
                                <select id="date_period" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">{{ __('Custom (Select End Date)') }}</option>
                                    <option value="week">{{ __('1 Week') }}</option>
                                    <option value="2weeks">{{ __('2 Weeks') }}</option>
                                    <option value="month">{{ __('1 Month') }}</option>
                                    <option value="2months">{{ __('2 Months') }}</option>
                                    <option value="3months">{{ __('3 Months') }}</option>
                                    <option value="6months">{{ __('6 Months') }}</option>
                                    <option value="year">{{ __('1 Year') }}</option>
                                </select>
                            </div>
                            
                            <div>
                                <x-input-label for="end_date" :value="__('End Date')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                            
                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description (Optional)')" />
                                <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="is_published" class="inline-flex items-center mt-4">
                                    <input id="is_published" type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Publish roster immediately') }}</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Staff Type Roster Configuration Preview -->
                        <div class="mt-8 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h3 class="font-medium text-gray-700 mb-3">{{ __('Department Staff Type Roster Configuration') }}</h3>
                            <div id="staff_type_roster_config" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div class="bg-white p-3 rounded shadow border border-gray-200">
                                    <h4 class="font-medium text-indigo-800">{{ __('Specialist Doctor') }}</h4>
                                    <p class="text-gray-600 roster-type" data-staff-type="specialist_doctor">{{ __('Not set') }}</p>
                                </div>
                                <div class="bg-white p-3 rounded shadow border border-gray-200">
                                    <h4 class="font-medium text-indigo-800">{{ __('Medical Officer') }}</h4>
                                    <p class="text-gray-600 roster-type" data-staff-type="medical_officer">{{ __('Not set') }}</p>
                                </div>
                                <div class="bg-white p-3 rounded shadow border border-gray-200">
                                    <h4 class="font-medium text-indigo-800">{{ __('Houseman Officer') }}</h4>
                                    <p class="text-gray-600 roster-type" data-staff-type="houseman_officer">{{ __('Not set') }}</p>
                                </div>
                                <div class="bg-white p-3 rounded shadow border border-gray-200">
                                    <h4 class="font-medium text-indigo-800">{{ __('Nurse') }}</h4>
                                    <p class="text-gray-600 roster-type" data-staff-type="nurse">{{ __('Not set') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Create Roster') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Ensure all DOM and resources are fully loaded
        window.onload = function() {
            // Store department roster configurations - ensure it's safely parsed
            const departmentsData = @json($departmentsWithRosterTypes);
            const departmentsWithRosterTypes = JSON.parse(JSON.stringify(departmentsData));
            
            console.log("Departments data loaded:", departmentsWithRosterTypes);
            
            // Default roster types for when no department is selected
            const defaultRosterTypes = {
                'specialist_doctor': 'oncall',
                'medical_officer': 'oncall',
                'houseman_officer': 'shift',
                'nurse': 'shift'
            };
            
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const periodSelect = document.getElementById('date_period');
            const departmentSelect = document.getElementById('department_id');
            const staffTypeSelect = document.getElementById('staff_type');
            const rosterTypeValue = document.getElementById('roster_type_value');
            const rosterTypeExplanation = document.getElementById('roster_type_explanation');
            
            // Handle initial state of staff type dropdown
            staffTypeSelect.disabled = !departmentSelect.value;
            
            // If department and staff type are pre-selected (from old values), update immediately
            if (departmentSelect.value && staffTypeSelect.value) {
                // Force staff type to be enabled if department is selected
                staffTypeSelect.disabled = false;
                updateRosterTypeDisplay();
            }
            
            // Function to update roster type display based on department and staff type
            function updateRosterTypeDisplay() {
                console.log("Updating roster type display");
                const departmentId = departmentSelect.value;
                const staffType = staffTypeSelect.value;
                const rosterTypeLabels = {'oncall': 'On Call', 'shift': 'Shift'};
                
                console.log("Department ID:", departmentId);
                console.log("Staff Type:", staffType);
                
                // Enable/disable staff type dropdown based on department selection
                staffTypeSelect.disabled = !departmentId;
                if (!departmentId) {
                    staffTypeSelect.value = '';
                }
                
                // Reset all roster type displays
                document.querySelectorAll('.roster-type').forEach(element => {
                    element.textContent = 'Not set';
                    element.classList.remove('text-blue-600', 'text-orange-600');
                });
                
                if (departmentId) {
                    // Find the selected department in our configurations
                    const departmentConfig = departmentsWithRosterTypes.find(d => d.id == departmentId);
                    console.log("Department config:", departmentConfig);
                    
                    if (departmentConfig && departmentConfig.staff_type_rosters) {
                        console.log("Staff type rosters:", departmentConfig.staff_type_rosters);
                        
                        // Update all roster types for this department
                        departmentConfig.staff_type_rosters.forEach(staffTypeRoster => {
                            console.log(`Processing staff type: ${staffTypeRoster.staff_type}`, staffTypeRoster);
                            const display = document.querySelector(`.roster-type[data-staff-type="${staffTypeRoster.staff_type}"]`);
                            if (display) {
                                const label = rosterTypeLabels[staffTypeRoster.roster_type] || staffTypeRoster.roster_type;
                                display.textContent = label;
                                display.classList.add(staffTypeRoster.roster_type === 'oncall' ? 'text-orange-600' : 'text-blue-600');
                            }
                        });
                    }
                }
                
                // Update the roster type display for the selected staff type
                if (staffType && departmentId) {
                    const departmentConfig = departmentsWithRosterTypes.find(d => d.id == departmentId);
                    console.log("Looking for staff type:", staffType, "in", departmentConfig);
                    
                    if (departmentConfig && departmentConfig.staff_type_rosters) {
                        // Check if the staff type exists in the roster configurations
                        const staffTypeFound = departmentConfig.staff_type_rosters
                            .some(config => config.staff_type === staffType);
                            
                        console.log("Staff type found in config:", staffTypeFound);
                        
                        // Find the specific staff type roster
                        const staffTypeRoster = departmentConfig.staff_type_rosters
                            .find(config => config.staff_type === staffType);
                            
                        console.log("Staff type roster:", staffTypeRoster);
                        
                        if (staffTypeRoster) {
                            const rosterType = staffTypeRoster.roster_type;
                            console.log("Found roster type:", rosterType);
                            
                            rosterTypeValue.textContent = rosterTypeLabels[rosterType] || rosterType;
                            rosterTypeValue.className = '';
                            rosterTypeValue.classList.add(rosterType === 'oncall' ? 'text-orange-600' : 'text-blue-600', 'font-medium');
                            
                            rosterTypeExplanation.textContent = `This department uses ${rosterTypeLabels[rosterType]} roster type for this staff type.`;
                        } else {
                            rosterTypeValue.textContent = 'Not configured';
                            rosterTypeExplanation.textContent = 'This staff type does not have a roster configuration in this department.';
                        }
                    } else {
                        rosterTypeValue.textContent = 'Department configuration not found';
                        rosterTypeExplanation.textContent = '';
                    }
                } else {
                    rosterTypeValue.textContent = 'Select department and staff type first';
                    rosterTypeExplanation.textContent = '';
                }
            }
            
            // Function to calculate end date based on start date and selected period
            function calculateEndDate() {
                if (!startDateInput.value || !periodSelect.value) return;
                
                const startDate = new Date(startDateInput.value);
                let endDate = new Date(startDate);
                
                switch (periodSelect.value) {
                    case 'week':
                        endDate.setDate(startDate.getDate() + 6); // 7 days total (including start date)
                        break;
                    case '2weeks':
                        endDate.setDate(startDate.getDate() + 13); // 14 days
                        break;
                    case 'month':
                        endDate.setMonth(startDate.getMonth() + 1);
                        endDate.setDate(endDate.getDate() - 1); // Last day of the month
                        break;
                    case '2months':
                        endDate.setMonth(startDate.getMonth() + 2);
                        endDate.setDate(endDate.getDate() - 1);
                        break;
                    case '3months':
                        endDate.setMonth(startDate.getMonth() + 3);
                        endDate.setDate(endDate.getDate() - 1);
                        break;
                    case '6months':
                        endDate.setMonth(startDate.getMonth() + 6);
                        endDate.setDate(endDate.getDate() - 1);
                        break;
                    case 'year':
                        endDate.setFullYear(startDate.getFullYear() + 1);
                        endDate.setDate(endDate.getDate() - 1);
                        break;
                    default:
                        return; // Do nothing for custom option
                }
                
                // Format the date as YYYY-MM-DD
                const yyyy = endDate.getFullYear();
                const mm = String(endDate.getMonth() + 1).padStart(2, '0');
                const dd = String(endDate.getDate()).padStart(2, '0');
                
                endDateInput.value = `${yyyy}-${mm}-${dd}`;
            }
            
            // Add event listeners
            startDateInput.addEventListener('change', calculateEndDate);
            periodSelect.addEventListener('change', calculateEndDate);
            departmentSelect.addEventListener('change', updateRosterTypeDisplay);
            staffTypeSelect.addEventListener('change', updateRosterTypeDisplay);
            
            // Initial update with a delay to ensure everything is ready
            setTimeout(function() {
                // Handle pre-selected values from old() data
                if (departmentSelect.value || staffTypeSelect.value) {
                    updateRosterTypeDisplay();
                }
                
                // Update the roster type display configurations table
                updateRosterTypeDisplay();
            }, 100);
        };
    </script>
</x-app-layout> 