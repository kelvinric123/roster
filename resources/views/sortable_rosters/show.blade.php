<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $sortableRoster->name }} (Oncall)
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('manage.rosters.index') }}" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    {{ __('Back to Rosters') }}
                </a>
                @if(!$sortableRoster->is_published)
                    <form action="{{ route('manage.sortable-rosters.generate-assignments', $sortableRoster) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm bg-orange-500 text-white rounded-md hover:bg-orange-600 transition">
                            {{ __('Generate Oncall Assignments') }}
                        </button>
                    </form>
                    <form action="{{ route('manage.sortable-rosters.publish', $sortableRoster) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                            {{ __('Publish Roster') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            <!-- Roster Information Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <h3 class="text-gray-600 text-sm">{{ __('Department') }}</h3>
                            <p class="font-medium">{{ $sortableRoster->department->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-gray-600 text-sm">{{ __('Staff Type') }}</h3>
                            <p class="font-medium">{{ $sortableRoster->staff_type_label }}</p>
                        </div>
                        <div>
                            <h3 class="text-gray-600 text-sm">{{ __('Period') }}</h3>
                            <p class="font-medium">{{ $sortableRoster->start_date->format('d/m/Y') }} - {{ $sortableRoster->end_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-gray-600 text-sm">{{ __('Description') }}</h3>
                        <p>{{ $sortableRoster->description ?? 'No description provided.' }}</p>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-gray-600 text-sm">{{ __('Status') }}</h3>
                        <p>
                            @if($sortableRoster->is_published)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Published</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Draft</span>
                            @endif
                        </p>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-gray-600 text-sm">{{ __('Oncall Staff Count') }}</h3>
                        <p class="font-medium">{{ $oncallStaffCount }} staff per day</p>
                    </div>
                </div>
            </div>
            
            <!-- Success notification below the header -->
            <div id="saveNotification" class="hidden mb-4 p-3 bg-green-100 text-green-800 rounded-md border border-green-300 flex items-center justify-between">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span id="notificationMessage">{{ __('Assignments saved successfully!') }}</span>
                </div>
                <button id="closeNotification" class="text-green-600 hover:text-green-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            
            <!-- Interactive Staff Assignment -->
            <div class="mt-10 bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">{{ __('Interactive Staff Assignment') }}</h2>
                    <div class="flex items-center space-x-2">
                        <button id="saveAssignments" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h1a2 2 0 012 2v7a2 2 0 01-2 2H8a2 2 0 01-2-2v-7a2 2 0 012-2h1v5.586l-1.293-1.293z" />
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5z" />
                            </svg>
                            {{ __('Save Assignments') }}
                        </button>
                        <span class="text-sm text-gray-600 italic">{{ __('(Saves changes without publishing)') }}</span>
                    </div>
                </div>
                
                <p class="mb-6 text-gray-600">
                    {{ __('Assign staff by dragging them from the left panel to the appropriate slot in the calendar. You can save your changes at any time without publishing.') }}
                </p>
                
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Staff Panel -->
                    <div class="lg:col-span-1 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-700 mb-3">{{ __('Available Staff') }}</h4>
                        <div id="staff-source" class="space-y-2">
                            @foreach($staff as $member)
                            <div class="staff-item bg-white p-3 rounded shadow-sm border border-gray-200 cursor-move" 
                                 data-id="{{ $member->id }}" data-name="{{ $member->name }}">
                                <span class="block font-medium text-sm">{{ $member->name }}</span>
                                <span class="block text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $member->type)) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Calendar Panel -->
                    <div class="lg:col-span-3">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-700 mb-3">{{ __('Oncall Schedule') }}</h4>
                            
                            <div id="staff-assignments" class="space-y-4">
                                @php
                                    $assignments = $sortableRoster->metadata['oncall_assignments'] ?? [];
                                    if (!empty($assignments)) {
                                        ksort($assignments);
                                    }
                                    $staffMap = $staff->keyBy('id');
                                    $currentDate = clone $sortableRoster->start_date;
                                    $endDate = clone $sortableRoster->end_date;
                                @endphp
                                
                                @while($currentDate <= $endDate)
                                    @php
                                        $dateStr = $currentDate->format('Y-m-d');
                                        $assignedStaff = $assignments[$dateStr] ?? [];
                                    @endphp
                                    <div class="assignment-day bg-white p-3 rounded shadow-sm" data-date="{{ $dateStr }}">
                                        <div class="flex justify-between items-center mb-2">
                                            <h5 class="font-medium">{{ $currentDate->format('d M Y (D)') }}</h5>
                                            <button type="button" class="clear-day-btn text-xs text-red-600 hover:text-red-800">
                                                {{ __('Clear') }}
                                            </button>
                                        </div>
                                        
                                        <!-- Oncall roles based on staff titles -->
                                        <div class="mb-2 space-y-2">
                                            @foreach($oncallStaffTitles as $index => $title)
                                                <div class="role-slot">
                                                    <div class="text-xs font-medium text-gray-500 mb-1">{{ $title }}</div>
                                                    <div class="assignment-slots border border-dashed border-gray-300 p-2 rounded min-h-[60px]" 
                                                         data-role="{{ $index }}" data-title="{{ $title }}">
                                                        @if(isset($assignedStaff[$index]) && isset($staffMap[$assignedStaff[$index]]))
                                                            <div class="assigned-staff bg-orange-100 text-orange-800 rounded p-2 mb-1 flex justify-between items-center" 
                                                                 data-id="{{ $assignedStaff[$index] }}" data-date="{{ $dateStr }}" data-role="{{ $index }}">
                                                                <span>{{ $staffMap[$assignedStaff[$index]]->name }}</span>
                                                                <button type="button" class="remove-staff-btn text-xs text-orange-800 hover:text-orange-900">
                                                                    &times;
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @php $currentDate->addDay(); @endphp
                                @endwhile
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Sortable for staff source panel
            const staffSource = document.getElementById('staff-source');
            if (staffSource) {
                new Sortable(staffSource, {
                    animation: 150,
                    group: {
                        name: 'shared',
                        pull: 'clone',
                        put: false
                    },
                    sort: false,
                    ghostClass: 'bg-gray-100',
                });
            }
            
            // Initialize Sortable for each assignment role slot
            const assignmentSlots = document.querySelectorAll('.assignment-slots');
            assignmentSlots.forEach(slot => {
                new Sortable(slot, {
                    animation: 150,
                    group: {
                        name: 'shared',
                        pull: true,
                        put: true
                    },
                    // Allow only one item per role slot
                    sort: false,
                    ghostClass: 'bg-gray-100',
                    onAdd: function(evt) {
                        // When item is dropped in, set the data attributes
                        const item = evt.item;
                        const slotElement = evt.to;
                        const dateStr = slotElement.closest('.assignment-day').dataset.date;
                        const roleIndex = slotElement.dataset.role;
                        const roleTitle = slotElement.dataset.title;
                        
                        // Clear existing items in this slot if any
                        const existingItems = slotElement.querySelectorAll('.assigned-staff');
                        existingItems.forEach(existingItem => {
                            if (existingItem !== item) {
                                existingItem.remove();
                            }
                        });
                        
                        // Make sure we preserve the original staff ID
                        const staffId = item.dataset.id;
                        
                        // Reset all classes and add the new ones for consistency
                        item.className = 'staff-item assigned-staff bg-orange-100 text-orange-800 rounded p-2 mb-1 flex justify-between items-center';
                        item.dataset.date = dateStr;
                        item.dataset.role = roleIndex;
                        item.dataset.id = staffId; // Ensure ID is preserved
                        
                        // Add role title as a data attribute
                        item.setAttribute('title', roleTitle);
                        
                        // Remove any existing remove button to avoid duplicates
                        const existingBtn = item.querySelector('.remove-staff-btn');
                        if (existingBtn) {
                            existingBtn.remove();
                        }
                        
                        // Add remove button
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'remove-staff-btn text-xs text-orange-800 hover:text-orange-900';
                        removeBtn.textContent = '×';
                        removeBtn.addEventListener('click', function() {
                            item.remove();
                        });
                        item.appendChild(removeBtn);
                    }
                });
            });
            
            // Add event listeners for clear day buttons
            document.querySelectorAll('.clear-day-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const day = this.closest('.assignment-day');
                    const slots = day.querySelectorAll('.assignment-slots');
                    slots.forEach(slot => {
                        slot.innerHTML = '';
                    });
                });
            });
            
            // Add event listener for remove staff buttons
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-staff-btn')) {
                    e.target.closest('.assigned-staff').remove();
                }
            });
            
            // Handle save assignments button click
            const saveAssignmentsButton = document.getElementById('saveAssignments');

            if (saveAssignmentsButton) {
                saveAssignmentsButton.addEventListener('click', function() {
                    // Disable the button and show loading state
                    saveAssignmentsButton.disabled = true;
                    saveAssignmentsButton.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg> {{ __('Saving...') }}`;

                    const assignments = {};

                    // Gather all assignments from the roster
                    document.querySelectorAll('.assignment-day').forEach(day => {
                        const dateStr = day.getAttribute('data-date');
                        const dayAssignments = {};
                        
                        // Get all role slots in this day
                        day.querySelectorAll('.assignment-slots').forEach(slot => {
                            const roleIndex = slot.getAttribute('data-role');
                            const staffCard = slot.querySelector('.assigned-staff');
                            
                            if (staffCard) {
                                dayAssignments[roleIndex] = staffCard.getAttribute('data-id');
                            }
                        });
                        
                        // Add this day's assignments
                        if (Object.keys(dayAssignments).length > 0) {
                            assignments[dateStr] = dayAssignments;
                        }
                    });

                    // Send the assignments to the server via AJAX
                    fetch('{{ route('manage.sortable-rosters.save-assignments', $sortableRoster) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ assignments })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Re-enable the button and restore original text
                        saveAssignmentsButton.disabled = false;
                        saveAssignmentsButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h1a2 2 0 012 2v7a2 2 0 01-2 2H8a2 2 0 01-2-2v-7a2 2 0 012-2h1v5.586l-1.293-1.293z" />
                            <path d="M5 3a2 2 0 012-2h6a2 2 0 012 2v2H5V3z" />
                        </svg> {{ __('Save Assignments') }}`;
                        
                        // Show the success notification
                        const notification = document.getElementById('saveNotification');
                        if (notification) {
                            notification.classList.remove('hidden');
                            
                            // Auto-hide the notification after 5 seconds
                            setTimeout(() => {
                                notification.classList.add('hidden');
                            }, 5000);
                        }
                        
                        console.log('Assignments saved!', data);
                    })
                    .catch(error => {
                        // Re-enable the button and restore original text
                        saveAssignmentsButton.disabled = false;
                        saveAssignmentsButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h1a2 2 0 012 2v7a2 2 0 01-2 2H8a2 2 0 01-2-2v-7a2 2 0 012-2h1v5.586l-1.293-1.293z" />
                            <path d="M5 3a2 2 0 012-2h6a2 2 0 012 2v2H5V3z" />
                        </svg> {{ __('Save Assignments') }}`;
                        
                        console.error('Error saving assignments:', error);
                    });
                });
            }
            
            // Add event listener for close notification button
            const closeNotificationBtn = document.getElementById('closeNotification');
            if (closeNotificationBtn) {
                closeNotificationBtn.addEventListener('click', function() {
                    const notification = document.getElementById('saveNotification');
                    if (notification) {
                        notification.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</x-app-layout> 