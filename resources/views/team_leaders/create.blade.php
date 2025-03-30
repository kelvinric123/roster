<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Team Leader') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('team-leaders.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="department_id" :value="__('Department')" />
                            <select id="department_id" name="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="staff_type" :value="__('Staff Type')" />
                            <select id="staff_type" name="staff_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Staff Type</option>
                                @foreach($staffTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('staff_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('staff_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="staff_id" :value="__('Staff Member')" />
                            <select id="staff_id" name="staff_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Staff Member</option>
                                @foreach($staffMembers as $staff)
                                    <option 
                                        value="{{ $staff->id }}" 
                                        data-department="{{ $staff->department_id }}" 
                                        data-type="{{ $staff->type }}"
                                        class="staff-option hidden"
                                        {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }} ({{ $staff->type_label }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('staff_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="start_date" :value="__('Start Date')" />
                            <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', now()->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <div>
                            <div class="flex items-center space-x-2 mb-2">
                                <x-input-label for="is_permanent" :value="__('Position Type')" />
                            </div>
                            <div class="flex items-center space-x-2">
                                <input id="is_permanent" type="checkbox" name="is_permanent" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_permanent') ? 'checked' : '' }} />
                                <label for="is_permanent" class="text-sm text-gray-700">Permanent Position (no end date)</label>
                            </div>
                        </div>

                        <div id="end_date_container" class="{{ old('is_permanent') ? 'hidden' : '' }}">
                            <x-input-label for="end_date" :value="__('End Date')" />
                            <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <a href="{{ route('team-leaders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <x-primary-button>{{ __('Create Team Leader') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const departmentSelect = document.getElementById('department_id');
            const staffTypeSelect = document.getElementById('staff_type');
            const staffSelect = document.getElementById('staff_id');
            const isPermanentCheckbox = document.getElementById('is_permanent');
            const endDateContainer = document.getElementById('end_date_container');
            const endDateInput = document.getElementById('end_date');
            
            // Filter staff members based on department and staff type
            function filterStaffMembers() {
                const selectedDepartment = departmentSelect.value;
                const selectedStaffType = staffTypeSelect.value;
                
                // Reset staff selection
                staffSelect.value = '';
                
                // Hide all options first
                Array.from(staffSelect.querySelectorAll('.staff-option')).forEach(option => {
                    option.classList.add('hidden');
                    option.disabled = true;
                });
                
                // Show only matching staff members
                if (selectedDepartment && selectedStaffType) {
                    Array.from(staffSelect.querySelectorAll('.staff-option')).forEach(option => {
                        const staffDepartment = option.getAttribute('data-department');
                        const staffType = option.getAttribute('data-type');
                        
                        if (staffDepartment === selectedDepartment && staffType === selectedStaffType) {
                            option.classList.remove('hidden');
                            option.disabled = false;
                        }
                    });
                }
            }
            
            // Department select change event
            departmentSelect.addEventListener('change', filterStaffMembers);
            
            // Staff type select change event
            staffTypeSelect.addEventListener('change', filterStaffMembers);

            // Handle permanent checkbox change
            isPermanentCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    endDateContainer.classList.add('hidden');
                    endDateInput.value = ''; // Clear the end date
                } else {
                    endDateContainer.classList.remove('hidden');
                }
            });

            // Initialize filters
            filterStaffMembers();
            isPermanentCheckbox.dispatchEvent(new Event('change'));
        });
    </script>
</x-app-layout> 