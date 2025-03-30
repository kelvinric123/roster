<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Staff Type Configurations') }}
            </h2>
            <a href="{{ route('staff.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Staff
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Staff Type Settings') }}</h3>
                    <p class="text-gray-600 mb-6">{{ __('Configure settings for different staff types') }}</p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Display Name</th>
                                    <th class="px-4 py-2 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffTypes as $key => $displayName)
                                <tr>
                                    <td class="px-4 py-2 border-b border-gray-200">{{ $key }}</td>
                                    <td class="px-4 py-2 border-b border-gray-200">{{ $displayName }}</td>
                                    <td class="px-4 py-2 border-b border-gray-200">
                                        <button type="button" class="inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-md" 
                                                onclick="openEditModal('{{ $key }}')">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    @foreach($staffTypes as $key => $displayName)
    <div id="editTypeModal{{ $key }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Edit {{ $displayName }} Settings</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeEditModal('{{ $key }}')">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('staff.updateTypeSettings', ['type' => $key]) }}">
                @csrf
                @method('PUT')
                <div class="mt-4">
                    <label for="displayName{{ $key }}" class="block text-sm font-medium text-gray-700">Display Name</label>
                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                           id="displayName{{ $key }}" name="display_name" value="{{ $displayName }}">
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Required Fields</label>
                    
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" 
                                   id="field_specialization{{ $key }}" name="required_fields[]" value="specialization" 
                                   {{ $key == 'specialist_doctor' ? 'checked' : '' }}>
                            <label for="field_specialization{{ $key }}" class="ml-2 block text-sm text-gray-700">
                                Specialization
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" 
                                   id="field_qualification{{ $key }}" name="required_fields[]" value="qualification"
                                   {{ $key == 'specialist_doctor' ? 'checked' : '' }}>
                            <label for="field_qualification{{ $key }}" class="ml-2 block text-sm text-gray-700">
                                Qualification
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" 
                                   id="field_current_rotation{{ $key }}" name="required_fields[]" value="current_rotation"
                                   {{ $key == 'houseman_officer' ? 'checked' : '' }}>
                            <label for="field_current_rotation{{ $key }}" class="ml-2 block text-sm text-gray-700">
                                Current Rotation
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" 
                                   id="field_graduation_year{{ $key }}" name="required_fields[]" value="graduation_year"
                                   {{ $key == 'houseman_officer' ? 'checked' : '' }}>
                            <label for="field_graduation_year{{ $key }}" class="ml-2 block text-sm text-gray-700">
                                Graduation Year
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" 
                                   id="field_nursing_unit{{ $key }}" name="required_fields[]" value="nursing_unit"
                                   {{ $key == 'nurse' ? 'checked' : '' }}>
                            <label for="field_nursing_unit{{ $key }}" class="ml-2 block text-sm text-gray-700">
                                Nursing Unit
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" 
                                   id="field_nurse_level{{ $key }}" name="required_fields[]" value="nurse_level"
                                   {{ $key == 'nurse' ? 'checked' : '' }}>
                            <label for="field_nurse_level{{ $key }}" class="ml-2 block text-sm text-gray-700">
                                Nurse Level
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md" 
                            onclick="closeEditModal('{{ $key }}')">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <script>
        function openEditModal(key) {
            document.getElementById('editTypeModal' + key).classList.remove('hidden');
        }
        
        function closeEditModal(key) {
            document.getElementById('editTypeModal' + key).classList.add('hidden');
        }
    </script>
</x-app-layout> 