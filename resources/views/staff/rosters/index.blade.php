<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('View Rosters') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
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
                    
                    <div class="mb-6">
                        @if(isset($isDepartmentLeader) && $isDepartmentLeader)
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                                <h3 class="text-lg font-semibold text-blue-700">Department Leader Access</h3>
                                <p class="text-gray-700">Department: {{ $departmentName ?? 'Not Assigned' }}</p>
                                <p class="text-gray-700">Your Role: {{ ucfirst(str_replace('_', ' ', $staff->type)) }} Leader</p>
                                
                                <div class="mt-2">
                                    <p class="text-gray-600 font-medium">You have access to view rosters for:</p>
                                    <ul class="list-disc ml-5 mt-1">
                                        @php
                                            $accessibleTypes = [];
                                            switch($staff->type) {
                                                case 'specialist_doctor':
                                                    $accessibleTypes = ['specialist_doctor', 'medical_officer', 'houseman_officer', 'nurse'];
                                                    break;
                                                case 'medical_officer':
                                                    $accessibleTypes = ['medical_officer', 'houseman_officer', 'nurse'];
                                                    break;
                                                case 'houseman_officer':
                                                    $accessibleTypes = ['houseman_officer'];
                                                    break;
                                                case 'nurse':
                                                    $accessibleTypes = ['nurse'];
                                                    break;
                                            }
                                        @endphp
                                        
                                        @foreach($accessibleTypes as $type)
                                            <li>{{ ucfirst(str_replace('_', ' ', $type)) }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @else
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                                <h3 class="text-lg font-semibold text-blue-700">Your Staff Information</h3>
                                <p class="text-gray-700">Department: {{ $departmentName ?? $staff->department->name ?? 'Not Assigned' }}</p>
                                @php
                                    $staffTypeLabels = [
                                        'specialist_doctor' => 'Specialist Doctor',
                                        'medical_officer' => 'Medical Officer',
                                        'houseman_officer' => 'Houseman Officer',
                                        'nurse' => 'Nurse'
                                    ];
                                @endphp
                                <p class="text-gray-700">Role: {{ $staffTypeLabels[$staff->type] ?? ucfirst(str_replace('_', ' ', $staff->type)) }}</p>
                                <p class="text-gray-600 mt-2">You can only view rosters for your department and staff type.</p>
                            </div>
                        @endif
                    </div>
                    
                    @if (count($rosters) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">{{ __('Name') }}</th>
                                        @if(isset($isDepartmentLeader) && $isDepartmentLeader)
                                        <th class="py-3 px-4 text-left">{{ __('Staff Type') }}</th>
                                        @endif
                                        <th class="py-3 px-4 text-left">{{ __('Roster Type') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Period') }}</th>
                                        <th class="py-3 px-4 text-center">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rosters as $roster)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                <a href="{{ route('staff.rosters.show', $roster) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $roster->name }}
                                                </a>
                                            </td>
                                            @if(isset($isDepartmentLeader) && $isDepartmentLeader)
                                            <td class="py-3 px-4">
                                                {{ $roster->staff_type_label }}
                                            </td>
                                            @endif
                                            <td class="py-3 px-4">
                                                @if($roster->roster_type)
                                                    <span class="px-2 py-1 {{ $roster->roster_type == 'oncall' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }} rounded-full text-xs">
                                                        {{ $roster->roster_type_label }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500">N/A</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                {{ $roster->start_date->format('d/m/Y') }} - {{ $roster->end_date->format('d/m/Y') }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <a href="{{ route('staff.rosters.show', $roster) }}" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" title="View">
                                                        View Roster
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $rosters->links() }}
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        @if(isset($isDepartmentLeader) && $isDepartmentLeader)
                                            {{ __('No published rosters found for your department with the staff types you can access.') }}
                                        @else
                                            {{ __('No published rosters found for your department and staff type.') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 