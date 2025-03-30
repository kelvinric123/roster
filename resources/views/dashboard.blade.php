<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Message Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-indigo-800 mb-2">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-700">
                        Welcome to the Hospital Staff Roster Management System. This dashboard gives you an overview 
                        of the departments and staff distribution across the hospital.
                    </p>
                    
                    @php
                        $user = Auth::user();
                        $isAdmin = $user && $user->role === 'admin';
                        $isTeamLeader = $user && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin';
                        $isDepartmentLeader = false;
                        $userDepartmentId = null;
                        $isRegularStaff = $user && $user->staff && !$isAdmin && !$isTeamLeader;
                        $departmentName = 'Not Assigned';
                        
                        if ($isTeamLeader && $user->staff_id) {
                            // Check if user is a team leader directly from the team_leaders table
                            $teamLeader = App\Models\TeamLeader::where('staff_id', $user->staff_id)
                                ->where(function($query) {
                                    $query->whereNull('end_date')
                                          ->orWhere('end_date', '>=', now());
                                })
                                ->first();
                                
                            if ($teamLeader) {
                                $isDepartmentLeader = true;
                                $userDepartmentId = $teamLeader->department_id;
                                
                                // Load department for team leader
                                if ($teamLeader->department) {
                                    $departmentName = $teamLeader->department->name;
                                } else {
                                    $department = \App\Models\Department::find($teamLeader->department_id);
                                    if ($department) {
                                        $departmentName = $department->name;
                                    }
                                }
                            }
                        } elseif ($isRegularStaff && $user->staff) {
                            // Load department for regular staff
                            if (is_object($user->staff->department)) {
                                $departmentName = $user->staff->department->name;
                            } elseif ($user->staff->department_id) {
                                $department = \App\Models\Department::find($user->staff->department_id);
                                if ($department) {
                                    $departmentName = $department->name;
                                }
                            }
                        }
                        
                        $staffTypeLabels = [
                            'specialist_doctor' => 'Specialist Doctor',
                            'medical_officer' => 'Medical Officer',
                            'houseman_officer' => 'Houseman Officer',
                            'nurse' => 'Nurse'
                        ];
                    @endphp
                    
                    @if($isRegularStaff || $isDepartmentLeader)
                        <div class="mt-4 flex flex-col sm:flex-row gap-4">
                            <div class="bg-white rounded-lg border border-indigo-200 p-4 flex-1">
                                <h4 class="text-sm font-medium text-indigo-600">Your Department</h4>
                                <p class="text-lg font-semibold">{{ $departmentName }}</p>
                            </div>
                            
                            <div class="bg-white rounded-lg border border-indigo-200 p-4 flex-1">
                                <h4 class="text-sm font-medium text-indigo-600">Your Role</h4>
                                <p class="text-lg font-semibold">
                                    @if($isDepartmentLeader)
                                        {{ $staffTypeLabels[$user->staff->type] ?? ucfirst(str_replace('_', ' ', $user->staff->type)) }} Leader
                                    @else
                                        {{ $staffTypeLabels[$user->staff->type] ?? ucfirst(str_replace('_', ' ', $user->staff->type)) }}
                                    @endif
                                </p>
                            </div>
                            
                            <div class="bg-white rounded-lg border border-indigo-200 p-4 flex-1">
                                <h4 class="text-sm font-medium text-indigo-600">Quick Access</h4>
                                <div class="mt-2">
                                    <a href="{{ route('staff.rosters.index') }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        View Rosters
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Display Staff Shift Statistics if available -->
                        @if(isset($staffShiftStats))
                            <x-staff-shift-stats
                                :staff="$user->staff"
                                :totalShifts="$staffShiftStats['totalShifts']"
                                :oncallShifts="$staffShiftStats['oncallShifts']"
                                :weekendShifts="$staffShiftStats['weekendShifts']"
                                :weekdayShifts="$staffShiftStats['weekdayShifts']"
                                :holidayShifts="$staffShiftStats['holidayShifts']" />
                        @endif
                        
                        <!-- Display Team Stats for Team Leaders -->
                        @if($isDepartmentLeader && isset($teamStats))
                            <x-team-shift-stats
                                :departmentName="$departmentName"
                                :staffType="$staffTypeLabel"
                                :teamStats="$teamStats" />
                        @endif
                    @endif
                </div>
            </div>
            
            <!-- Department Statistics and Staff Distribution - Only for Admin -->
            @if($isAdmin)
            <!-- Department Statistics -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Department Statistics</h3>
                        
                        @php
                            $departments = \App\Models\Department::withCount('staff')->get();
                            $totalDepartments = $departments->count();
                            $totalStaff = $departments->sum('staff_count');
                        @endphp
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <p class="text-sm font-medium text-blue-800">Total Departments</p>
                                <p class="text-2xl font-bold text-blue-900">{{ $totalDepartments }}</p>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                <p class="text-sm font-medium text-green-800">Total Staff</p>
                                <p class="text-2xl font-bold text-green-900">{{ $totalStaff }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h4 class="text-md font-medium text-gray-700 mb-2">Departments</h4>
                            <div class="space-y-2">
                                @foreach($departments->take(5) as $department)
                                    <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                        <span class="font-medium">{{ $department->name }}</span>
                                        <span class="bg-indigo-100 text-indigo-800 py-1 px-2 rounded-full text-xs">
                                            {{ $department->staff_count }} staff
                                        </span>
                                    </div>
                                @endforeach
                                
                                @if($departments->count() > 5)
                                    <div class="text-center mt-2">
                                        <a href="{{ route('departments.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                            View all departments
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Staff Categories -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Staff Distribution</h3>
                        
                        @php
                            $staffByType = \App\Models\Staff::selectRaw('type, count(*) as count')
                                ->groupBy('type')
                                ->pluck('count', 'type')
                                ->toArray();
                                
                            $typeLabels = [
                                'specialist_doctor' => 'Specialist Doctors',
                                'medical_officer' => 'Medical Officers',
                                'houseman_officer' => 'Houseman Officers',
                                'nurse' => 'Nurses'
                            ];
                            
                            $colors = [
                                'specialist_doctor' => ['bg-blue-50', 'text-blue-800', 'text-blue-900', 'border-blue-200'],
                                'medical_officer' => ['bg-green-50', 'text-green-800', 'text-green-900', 'border-green-200'],
                                'houseman_officer' => ['bg-purple-50', 'text-purple-800', 'text-purple-900', 'border-purple-200'],
                                'nurse' => ['bg-pink-50', 'text-pink-800', 'text-pink-900', 'border-pink-200']
                            ];
                        @endphp
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($typeLabels as $type => $label)
                                <div class="{{ $colors[$type][0] }} p-4 rounded-lg border {{ $colors[$type][3] }}">
                                    <p class="text-sm font-medium {{ $colors[$type][1] }}">{{ $label }}</p>
                                    <p class="text-2xl font-bold {{ $colors[$type][2] }}">
                                        {{ $staffByType[$type] ?? 0 }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            <div class="w-full mt-4">
                                @foreach($typeLabels as $type => $label)
                                    @php
                                        $count = $staffByType[$type] ?? 0;
                                        $percentage = $totalStaff > 0 ? ($count / $totalStaff) * 100 : 0;
                                    @endphp
                                    <div class="mb-2">
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                            <span class="text-sm font-medium text-gray-700">{{ $count }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="{{ str_replace('bg-', 'bg-', $colors[$type][0]) }} h-2.5 rounded-full" 
                                                style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Notice Board -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Notice Board</h3>
                    
                    <div class="space-y-4">
                        @foreach($announcements as $announcement)
                            @php
                                $bgColor = 'bg-blue-50';
                                $textColor = 'text-blue-700';
                                $iconColor = 'text-blue-600';
                                $borderColor = 'border-blue-200';
                                $headerColor = 'text-blue-800';
                                
                                if ($announcement['type'] === 'important') {
                                    $bgColor = 'bg-yellow-50';
                                    $textColor = 'text-yellow-700';
                                    $iconColor = 'text-yellow-600';
                                    $borderColor = 'border-yellow-200';
                                    $headerColor = 'text-yellow-800';
                                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />';
                                } elseif ($announcement['type'] === 'success') {
                                    $bgColor = 'bg-green-50';
                                    $textColor = 'text-green-700';
                                    $iconColor = 'text-green-600';
                                    $borderColor = 'border-green-200';
                                    $headerColor = 'text-green-800';
                                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                } elseif ($announcement['type'] === 'event') {
                                    $bgColor = 'bg-blue-50';
                                    $textColor = 'text-blue-700';
                                    $iconColor = 'text-blue-600';
                                    $borderColor = 'border-blue-200';
                                    $headerColor = 'text-blue-800';
                                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />';
                                }
                            @endphp
                            <div class="p-4 {{ $bgColor }} border {{ $borderColor }} rounded-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 pt-0.5">
                                        <svg class="h-6 w-6 {{ $iconColor }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            {!! $icon !!}
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium {{ $headerColor }}">{{ $announcement['title'] }}</h4>
                                        <div class="mt-1 text-sm {{ $textColor }}">
                                            <p>{{ $announcement['content'] }}</p>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($announcement['date'])->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
