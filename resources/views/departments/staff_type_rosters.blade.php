<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Staff Type Roster Settings for ') }} {{ $department->name }}
            </h2>
            <a href="{{ route('departments.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Departments
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <p>Configure roster types for each staff type in <strong>{{ $department->name }}</strong> department.</p>
                        <p class="text-sm text-gray-600 mt-1">These settings will determine the roster type for each staff category when creating rosters for this department.</p>
                    </div>

                    <form method="POST" action="{{ route('departments.staff-type-rosters.update', $department) }}" class="space-y-6">
                        @csrf

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roster Type</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($department->staffTypeRosters as $staffTypeRoster)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $staffTypeRoster->staff_type_label }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-6">
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" class="form-radio text-blue-600" name="roster_types[{{ $staffTypeRoster->staff_type }}]" value="shift" {{ $staffTypeRoster->roster_type == 'shift' ? 'checked' : '' }}>
                                                        <span class="ml-2">Shift</span>
                                                    </label>
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" class="form-radio text-orange-600" name="roster_types[{{ $staffTypeRoster->staff_type }}]" value="oncall" {{ $staffTypeRoster->roster_type == 'oncall' ? 'checked' : '' }}>
                                                        <span class="ml-2">On Call</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 