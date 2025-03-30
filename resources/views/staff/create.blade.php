<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Staff Member') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('staff.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="type" :value="__('Staff Type')" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select Type</option>
                                @foreach($staffTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="department_id" :value="__('Department')" />
                            <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }} ({{ $department->code }})</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('department_id')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <!-- Login Information Section -->
                        <div class="p-4 mt-4 bg-gray-50 rounded-md border border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-2">Login Information</h3>
                            <div class="text-sm text-gray-700">
                                <p class="mb-1"><span class="font-semibold">Login Email:</span> Same as staff email</p>
                                <p><span class="font-semibold">Default Password:</span> qmed.asia</p>
                                <p class="mt-2 text-xs text-gray-500">A user account will be automatically created for this staff member using these credentials.</p>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <!-- Specialist Doctor Fields -->
                        <div id="specialist_doctor_fields" class="space-y-6" style="display: none;">
                            <div>
                                <x-input-label for="specialization" :value="__('Specialization')" />
                                <select id="specialization" name="specialization" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Specialization</option>
                                    <option value="Cardiology" {{ old('specialization') == 'Cardiology' ? 'selected' : '' }}>Cardiology</option>
                                    <option value="Neurology" {{ old('specialization') == 'Neurology' ? 'selected' : '' }}>Neurology</option>
                                    <option value="Orthopedics" {{ old('specialization') == 'Orthopedics' ? 'selected' : '' }}>Orthopedics</option>
                                    <option value="Pediatrics" {{ old('specialization') == 'Pediatrics' ? 'selected' : '' }}>Pediatrics</option>
                                    <option value="Psychiatry" {{ old('specialization') == 'Psychiatry' ? 'selected' : '' }}>Psychiatry</option>
                                    <option value="Radiology" {{ old('specialization') == 'Radiology' ? 'selected' : '' }}>Radiology</option>
                                    <option value="Surgery" {{ old('specialization') == 'Surgery' ? 'selected' : '' }}>Surgery</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('specialization')" />
                            </div>
                            <div>
                                <x-input-label for="qualification" :value="__('Qualification')" />
                                <x-text-input id="qualification" name="qualification" type="text" class="mt-1 block w-full" :value="old('qualification')" />
                                <x-input-error class="mt-2" :messages="$errors->get('qualification')" />
                            </div>
                        </div>

                        <!-- Medical Officer Fields -->
                        <div id="medical_officer_fields" class="space-y-6" style="display: none;">
                            <div>
                                <x-input-label for="mo_specialization" :value="__('Specialization')" />
                                <select id="mo_specialization" name="specialization" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Specialization</option>
                                    <option value="General Medicine" {{ old('specialization') == 'General Medicine' ? 'selected' : '' }}>General Medicine</option>
                                    <option value="Accident & Emergency" {{ old('specialization') == 'Accident & Emergency' ? 'selected' : '' }}>Accident & Emergency</option>
                                    <option value="Anesthesiology" {{ old('specialization') == 'Anesthesiology' ? 'selected' : '' }}>Anesthesiology</option>
                                    <option value="Obstetrics & Gynecology" {{ old('specialization') == 'Obstetrics & Gynecology' ? 'selected' : '' }}>Obstetrics & Gynecology</option>
                                    <option value="Pediatrics" {{ old('specialization') == 'Pediatrics' ? 'selected' : '' }}>Pediatrics</option>
                                    <option value="Surgery" {{ old('specialization') == 'Surgery' ? 'selected' : '' }}>Surgery</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('specialization')" />
                            </div>
                        </div>

                        <!-- Houseman Officer Fields -->
                        <div id="houseman_officer_fields" class="space-y-6" style="display: none;">
                            <div>
                                <x-input-label for="current_rotation" :value="__('Current Rotation')" />
                                <select id="current_rotation" name="current_rotation" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select Current Rotation</option>
                                    <option value="Medicine" {{ old('current_rotation') == 'Medicine' ? 'selected' : '' }}>Medicine</option>
                                    <option value="Surgery" {{ old('current_rotation') == 'Surgery' ? 'selected' : '' }}>Surgery</option>
                                    <option value="Pediatrics" {{ old('current_rotation') == 'Pediatrics' ? 'selected' : '' }}>Pediatrics</option>
                                    <option value="Obstetrics" {{ old('current_rotation') == 'Obstetrics' ? 'selected' : '' }}>Obstetrics</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('current_rotation')" />
                            </div>
                            <div>
                                <x-input-label for="graduation_year" :value="__('Graduation Year')" />
                                <x-text-input id="graduation_year" name="graduation_year" type="number" min="2000" max="2030" class="mt-1 block w-full" :value="old('graduation_year')" />
                                <x-input-error class="mt-2" :messages="$errors->get('graduation_year')" />
                            </div>
                        </div>

                        <!-- Nurse Fields -->
                        <div id="nurse_fields" class="space-y-6" style="display: none;">
                            <div>
                                <x-input-label for="nursing_unit" :value="__('Nursing Unit')" />
                                <select id="nursing_unit" name="nursing_unit" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select Nursing Unit</option>
                                    <option value="General" {{ old('nursing_unit') == 'General' ? 'selected' : '' }}>General</option>
                                    <option value="ICU" {{ old('nursing_unit') == 'ICU' ? 'selected' : '' }}>ICU</option>
                                    <option value="ER" {{ old('nursing_unit') == 'ER' ? 'selected' : '' }}>ER</option>
                                    <option value="Pediatric" {{ old('nursing_unit') == 'Pediatric' ? 'selected' : '' }}>Pediatric</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('nursing_unit')" />
                            </div>
                            <div>
                                <x-input-label for="nurse_level" :value="__('Nurse Level')" />
                                <select id="nurse_level" name="nurse_level" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select Nurse Level</option>
                                    <option value="Junior" {{ old('nurse_level') == 'Junior' ? 'selected' : '' }}>Junior</option>
                                    <option value="Senior" {{ old('nurse_level') == 'Senior' ? 'selected' : '' }}>Senior</option>
                                    <option value="Head Nurse" {{ old('nurse_level') == 'Head Nurse' ? 'selected' : '' }}>Head Nurse</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('nurse_level')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="joining_date" :value="__('Joining Date')" />
                            <x-text-input id="joining_date" name="joining_date" type="date" class="mt-1 block w-full" :value="old('joining_date')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('joining_date')" />
                        </div>

                        <div>
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('notes') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <a href="{{ route('staff.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const specialistFields = document.getElementById('specialist_doctor_fields');
            const moFields = document.getElementById('medical_officer_fields');
            const hoFields = document.getElementById('houseman_officer_fields');
            const nurseFields = document.getElementById('nurse_fields');

            // Get references to form fields that have conditional requirements
            const specialization = document.getElementById('specialization');
            const moSpecialization = document.getElementById('mo_specialization');
            const qualification = document.getElementById('qualification');
            const currentRotation = document.getElementById('current_rotation');
            const graduationYear = document.getElementById('graduation_year');
            const nursingUnit = document.getElementById('nursing_unit');
            const nurseLevel = document.getElementById('nurse_level');

            // Initial check for page load (especially for validation errors)
            showHideFields(typeSelect.value);

            // Add event listener for change
            typeSelect.addEventListener('change', function() {
                showHideFields(this.value);
            });

            function showHideFields(type) {
                // Hide all fields first
                specialistFields.style.display = 'none';
                moFields.style.display = 'none';
                hoFields.style.display = 'none';
                nurseFields.style.display = 'none';

                // Remove required attribute from all type-specific fields
                if (specialization) specialization.removeAttribute('required');
                if (moSpecialization) moSpecialization.removeAttribute('required');
                if (qualification) qualification.removeAttribute('required');
                if (currentRotation) currentRotation.removeAttribute('required');
                if (graduationYear) graduationYear.removeAttribute('required');
                if (nursingUnit) nursingUnit.removeAttribute('required');
                if (nurseLevel) nurseLevel.removeAttribute('required');

                // Show the relevant fields based on type and set required attributes
                if (type === 'specialist_doctor') {
                    specialistFields.style.display = 'block';
                    if (specialization) specialization.setAttribute('required', 'required');
                    if (qualification) qualification.setAttribute('required', 'required');
                } else if (type === 'medical_officer') {
                    moFields.style.display = 'block';
                    if (moSpecialization) moSpecialization.setAttribute('required', 'required');
                } else if (type === 'houseman_officer') {
                    hoFields.style.display = 'block';
                    if (currentRotation) currentRotation.setAttribute('required', 'required');
                    if (graduationYear) graduationYear.setAttribute('required', 'required');
                } else if (type === 'nurse') {
                    nurseFields.style.display = 'block';
                    if (nursingUnit) nursingUnit.setAttribute('required', 'required');
                    if (nurseLevel) nurseLevel.setAttribute('required', 'required');
                }
                // For admin, no additional fields are shown
            }
        });
    </script>
</x-app-layout> 