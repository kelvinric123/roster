<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Rosters') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('manage.rosters.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    + {{ __('Create New Roster') }}
                </a>
                <a href="{{ route('manage.sortable-rosters.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                    + {{ __('Create Oncall Roster') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(isset($isDepartmentLeader) && $isDepartmentLeader)
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                            <h3 class="text-lg font-semibold text-blue-700">Department Leader Access</h3>
                            <p class="text-gray-700">Department: {{ $departmentName ?? 'Not Assigned' }}</p>
                            <p class="text-gray-700">Your Role: {{ ucfirst(str_replace('_', ' ', auth()->user()->staff->type)) }} Leader</p>
                            
                            <div class="mt-2">
                                <p class="text-gray-600 font-medium">You can manage rosters for:</p>
                                <ul class="list-disc ml-5 mt-1">
                                    @php
                                        $accessibleTypes = [];
                                        $staffType = auth()->user()->staff->type ?? '';
                                        switch($staffType) {
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
                    @endif
                    
                    <div class="mb-4">
                        <div class="flex items-center overflow-x-auto space-x-4 py-2 px-1">
                            <a href="{{ route('manage.rosters.index') }}" 
                               class="inline-flex items-center px-3 py-1 {{ !request()->has('type') ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }} rounded-full text-sm font-medium">
                                All
                            </a>
                            
                            @php
                                $staffType = auth()->user()->staff->type ?? '';
                                $showTypes = ['specialist_doctor', 'medical_officer', 'houseman_officer', 'nurse'];
                                
                                if(isset($isDepartmentLeader) && $isDepartmentLeader) {
                                    switch($staffType) {
                                        case 'specialist_doctor':
                                            // Show all types
                                            break;
                                        case 'medical_officer':
                                            // Remove specialist_doctor
                                            $showTypes = array_diff($showTypes, ['specialist_doctor']);
                                            break;
                                        case 'houseman_officer':
                                            // Only houseman_officer
                                            $showTypes = ['houseman_officer'];
                                            break;
                                        case 'nurse':
                                            // Only nurse
                                            $showTypes = ['nurse'];
                                            break;
                                    }
                                }
                            @endphp
                            
                            @if(in_array('specialist_doctor', $showTypes))
                                <a href="{{ route('manage.rosters.index', ['type' => 'specialist_doctor']) }}" 
                                   class="inline-flex items-center px-3 py-1 {{ request()->get('type') === 'specialist_doctor' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }} rounded-full text-sm font-medium">
                                    {{ __('Specialist Doctor') }}
                                </a>
                            @endif
                            
                            @if(in_array('medical_officer', $showTypes))
                                <a href="{{ route('manage.rosters.index', ['type' => 'medical_officer']) }}" 
                                   class="inline-flex items-center px-3 py-1 {{ request()->get('type') === 'medical_officer' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }} rounded-full text-sm font-medium">
                                    {{ __('Medical Officer') }}
                                </a>
                            @endif
                            
                            @if(in_array('houseman_officer', $showTypes))
                                <a href="{{ route('manage.rosters.index', ['type' => 'houseman_officer']) }}" 
                                   class="inline-flex items-center px-3 py-1 {{ request()->get('type') === 'houseman_officer' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }} rounded-full text-sm font-medium">
                                    {{ __('Houseman Officer') }}
                                </a>
                            @endif
                            
                            @if(in_array('nurse', $showTypes))
                                <a href="{{ route('manage.rosters.index', ['type' => 'nurse']) }}" 
                                   class="inline-flex items-center px-3 py-1 {{ request()->get('type') === 'nurse' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }} rounded-full text-sm font-medium">
                                    {{ __('Nurse') }}
                                </a>
                            @endif
                        </div>
                    </div>
                    
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
                    
                    @if (count($rosters) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">{{ __('Name') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Staff Type') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Department') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Roster Type') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Period') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Status') }}</th>
                                        <th class="py-3 px-4 text-center">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rosters as $roster)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                <a href="{{ route('manage.sortable-rosters.show', $roster) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $roster->name }}
                                                </a>
                                            </td>
                                            <td class="py-3 px-4">{{ $roster->staff_type_label }}</td>
                                            <td class="py-3 px-4">{{ $roster->department->name ?? 'Not Assigned' }}</td>
                                            <td class="py-3 px-4">
                                                @if($roster->roster_type)
                                                    <span class="px-2 py-1 {{ $roster->roster_type == 'oncall' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }} rounded-full text-xs">
                                                        {{ $roster->roster_type_label }} ({{ $roster->staff_type_label }})
                                                    </span>
                                                @else
                                                    <span class="text-gray-500">N/A</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                {{ $roster->start_date->format('d/m/Y') }} - {{ $roster->end_date->format('d/m/Y') }}
                                            </td>
                                            <td class="py-3 px-4">
                                                @if ($roster->is_published)
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Published</span>
                                                @else
                                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Draft</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <a href="{{ route('manage.sortable-rosters.show', $roster) }}" class="text-blue-600 hover:text-blue-900" title="View">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </a>
                                                    <a href="{{ route('manage.rosters.edit', $roster) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </a>
                                                    <form action="{{ route('manage.rosters.toggle_publish', $roster) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="{{ $roster->is_published ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}" title="{{ $roster->is_published ? 'Unpublish' : 'Publish' }}">
                                                            @if ($roster->is_published)
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                                </svg>
                                                            @else
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @endif
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="{{ route('manage.rosters.destroy', $roster) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this roster?') }}')">
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
                                        {{ __('No rosters found.') }}
                                        <a href="{{ route('manage.rosters.create') }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                            {{ __('Create your first roster.') }}
                                        </a>
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