<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Department Details') }}: {{ $department->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('departments.edit', $department) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Edit Department
                </a>
                <a href="{{ route('departments.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Department Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Department Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Code</p>
                            <p class="text-base text-gray-900">{{ $department->code }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-600">Status</p>
                            <p class="text-base">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $department->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                        
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-600">Description</p>
                            <p class="text-base text-gray-900">{{ $department->description ?: 'No description provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Department Staff List Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Staff in {{ $department->name }}</h3>
                        <a href="{{ route('staff.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-1 px-3 rounded">
                            Add New Staff
                        </a>
                    </div>
                    
                    @if($department->staff->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($department->staff as $member)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $member->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $member->type_label }}</div>
                                                @if($member->type == 'specialist_doctor')
                                                    <div class="text-xs text-gray-500">{{ $member->specialization }}</div>
                                                @elseif($member->type == 'medical_officer')
                                                    <div class="text-xs text-gray-500">{{ $member->department }}</div>
                                                @elseif($member->type == 'houseman_officer')
                                                    <div class="text-xs text-gray-500">Rotation: {{ $member->current_rotation }}</div>
                                                @elseif($member->type == 'nurse')
                                                    <div class="text-xs text-gray-500">{{ $member->nurse_level }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $member->phone }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $member->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('staff.edit', $member) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No staff members found in this department.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Department Staff Type Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Staff Distribution</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <p class="text-sm font-medium text-blue-800">Specialist Doctors</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $department->staff->where('type', 'specialist_doctor')->count() }}</p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <p class="text-sm font-medium text-green-800">Medical Officers</p>
                            <p class="text-2xl font-bold text-green-900">{{ $department->staff->where('type', 'medical_officer')->count() }}</p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <p class="text-sm font-medium text-purple-800">Houseman Officers</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $department->staff->where('type', 'houseman_officer')->count() }}</p>
                        </div>
                        
                        <div class="bg-pink-50 p-4 rounded-lg border border-pink-200">
                            <p class="text-sm font-medium text-pink-800">Nurses</p>
                            <p class="text-2xl font-bold text-pink-900">{{ $department->staff->where('type', 'nurse')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 