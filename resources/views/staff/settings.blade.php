<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Staff Settings') }}
            </h2>
            <a href="{{ route('staff.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Staff List
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

            <!-- Shift System Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Shift System Settings') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-800 mb-2">{{ __('Shift Types') }}</h4>
                            
                            <div class="mt-4">
                                <a href="{{ route('staff.shift-settings') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('Manage Shift Types') }}
                                </a>
                            </div>
                            
                            <p class="text-sm text-gray-600 mt-2">
                                {{ __('Configure different shift types, including morning, evening, and night shifts.') }}
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-800 mb-2">{{ __('Department Shift Assignments') }}</h4>
                            
                            <div class="mt-4">
                                <a href="{{ route('staff.department-shifts') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('Manage Department Shifts') }}
                                </a>
                            </div>
                            
                            <p class="text-sm text-gray-600 mt-2">
                                {{ __('Assign specific shift types to departments and set their schedules.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- On-Call System Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('On-Call System Settings') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-800 mb-2">{{ __('On-Call Schedules') }}</h4>
                            
                            <div class="mt-4">
                                <a href="{{ route('staff.oncall-schedules') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('Manage On-Call Schedules') }}
                                </a>
                            </div>
                            
                            <p class="text-sm text-gray-600 mt-2">
                                {{ __('Configure on-call rotation schedules for medical staff.') }}
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-800 mb-2">{{ __('On-Call Staff Assignments') }}</h4>
                            
                            <div class="mt-4">
                                <a href="{{ route('staff.oncall-assignments') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('Manage Staff Assignments') }}
                                </a>
                            </div>
                            
                            <p class="text-sm text-gray-600 mt-2">
                                {{ __('Assign on-call duties to staff members based on department and specialization.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Type Configurations -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Staff Type Configurations') }}</h3>
                    
                    <div class="bg-gray-50 p-4 rounded">
                        <p class="mb-4">{{ __('Configure specific settings for each staff type:') }}</p>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-48 font-medium">{{ __('Specialist Doctors:') }}</div>
                                <div>{{ __('On-call system, custom schedules') }}</div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="w-48 font-medium">{{ __('Medical Officers:') }}</div>
                                <div>{{ __('Shift system, on-call rotations') }}</div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="w-48 font-medium">{{ __('Houseman Officers:') }}</div>
                                <div>{{ __('Shift system, rotation schedules') }}</div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="w-48 font-medium">{{ __('Nurses:') }}</div>
                                <div>{{ __('Shift system only') }}</div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ route('staff.type-settings') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                {{ __('Configure Staff Types') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 