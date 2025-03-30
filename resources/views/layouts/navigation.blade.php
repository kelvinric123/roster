<nav x-data="{ open: false, rostersOpen: false, settingsOpen: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    
                    @php
                        $user = Auth::user();
                        $isAdmin = $user && $user->role === 'admin';
                        $isTeamLeader = $user && $user->role && str_contains($user->role, '_leader') && $user->role !== 'admin';
                        $isDepartmentLeader = false;
                        $userDepartmentId = null;
                        $isRegularStaff = $user && $user->staff && !$isAdmin && !$isTeamLeader;
                        
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
                            }
                        }
                    @endphp
                    
                    @if($isAdmin || $isDepartmentLeader)
                    <x-nav-link :href="route('staff.index')" :active="request()->routeIs('staff.index')">
                        {{ __('Staff') }}
                    </x-nav-link>
                    @endif
                    
                    @if($isAdmin)
                    <x-nav-link :href="route('team-leaders.index')" :active="request()->routeIs('team-leaders.*')">
                        {{ __('Team Leaders') }}
                    </x-nav-link>
                    @endif
                    
                    @if($isAdmin || $isDepartmentLeader)
                    <x-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                        {{ __('Departments') }}
                    </x-nav-link>
                    @endif
                    
                    <!-- Rosters Nav Link with Dropdown -->
                    <div class="hidden sm:flex sm:items-center" @click.away="rostersOpen = false">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="rostersOpen = !rostersOpen" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('manage.rosters.*') || request()->routeIs('staff.rosters.*') ? 'text-gray-900 border-indigo-500' : '' }}">
                                {{ __('Rosters') }}
                                <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="rostersOpen" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right right-0"
                                 style="display: none;">
                                <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                    @if($isAdmin || $isDepartmentLeader)
                                    <a href="{{ route('manage.rosters.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('manage.rosters.*') ? 'bg-gray-100' : '' }}">
                                        {{ __('Manage Rosters') }}
                                    </a>
                                    @endif
                                    <a href="{{ route('staff.rosters.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('staff.rosters.*') ? 'bg-gray-100' : '' }}">
                                        {{ __('View Rosters') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Settings Nav Link with Dropdown -->
                    @if($isAdmin)
                    <div class="hidden sm:flex sm:items-center" @click.away="settingsOpen = false">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="settingsOpen = !settingsOpen" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('settings.*') ? 'text-gray-900 border-indigo-500' : '' }}">
                                {{ __('Settings') }}
                                <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="settingsOpen" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right right-0"
                                 style="display: none;">
                                <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                    <a href="{{ route('settings.calendar.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('settings.calendar.*') ? 'bg-gray-100' : '' }}">
                                        {{ __('Calendar Management') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- System Settings Dropdown - Removed since it's empty -->

                <!-- User Profile Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            @if($isAdmin || $isDepartmentLeader)
            <x-responsive-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                {{ __('Departments') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('staff.index')" :active="request()->routeIs('staff.index')">
                {{ __('Staff Management') }}
            </x-responsive-nav-link>
            @endif
            
            @if($isAdmin)
            <x-responsive-nav-link :href="route('team-leaders.index')" :active="request()->routeIs('team-leaders.*')">
                {{ __('Team Leaders') }}
            </x-responsive-nav-link>
            @endif
            
            <!-- Responsive Rosters Dropdown -->
            <div @click.away="rostersOpen = false" class="relative">
                <button @click="rostersOpen = !rostersOpen" class="flex w-full items-center pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('manage.rosters.*') || request()->routeIs('staff.rosters.*') ? 'border-indigo-400 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium focus:outline-none transition duration-150 ease-in-out">
                    <div>{{ __('Rosters') }}</div>
                    <div class="ml-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
                
                <div x-show="rostersOpen" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="mt-2 space-y-1" style="display: none;">
                    @if($isAdmin || $isDepartmentLeader)
                    <x-responsive-nav-link :href="route('manage.rosters.index')" :active="request()->routeIs('manage.rosters.*')">
                        <div class="pl-3">{{ __('Manage Rosters') }}</div>
                    </x-responsive-nav-link>
                    @endif
                    
                    <x-responsive-nav-link :href="route('staff.rosters.index')" :active="request()->routeIs('staff.rosters.*')">
                        <div class="pl-3">{{ __('View Rosters') }}</div>
                    </x-responsive-nav-link>
                </div>
            </div>
            
            <!-- Responsive Settings Dropdown -->
            @if($isAdmin)
            <div @click.away="settingsOpen = false" class="relative">
                <button @click="settingsOpen = !settingsOpen" class="flex w-full items-center pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('settings.*') ? 'border-indigo-400 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }} text-base font-medium focus:outline-none transition duration-150 ease-in-out">
                    <div>{{ __('Settings') }}</div>
                    <div class="ml-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
                
                <div x-show="settingsOpen" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="mt-2 space-y-1" style="display: none;">
                    <x-responsive-nav-link :href="route('settings.calendar.index')" :active="request()->routeIs('settings.calendar.*')">
                        <div class="pl-3">{{ __('Calendar Management') }}</div>
                    </x-responsive-nav-link>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Responsive Settings Section - Removed since it's empty -->

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
