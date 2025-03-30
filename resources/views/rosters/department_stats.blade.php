<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $department->name }} Dashboard
            </h2>
            <a href="{{ route('roster.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-indigo-600">{{ $rosters->count() }}</div>
                    <div class="text-sm text-gray-600">Total {{ $department->name }} Rosters</div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-green-600">{{ $staff->count() }}</div>
                    <div class="text-sm text-gray-600">{{ $department->name }} Staff</div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-blue-600">{{ $entries->count() }}</div>
                    <div class="text-sm text-gray-600">Total Shift Entries</div>
                </div>
            </div>
            
            <!-- Staff Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Staff Summary</h3>
                    
                    @php
                        $typeLabels = [
                            'specialist_doctor' => 'Specialist Doctor',
                            'medical_officer' => 'Medical Officer',
                            'houseman_officer' => 'Houseman Officer',
                            'nurse' => 'Nurse'
                        ];
                        
                        $staffCounts = $staff->groupBy('type')->map->count();
                    @endphp
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($staffCounts as $type => $count)
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                <div class="flex justify-between items-center">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}
                                    </div>
                                    <span class="ml-2 text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-800">
                                        {{ $count }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Roster Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-semibold">Rosters</h3>
                        <a href="{{ route('manage.rosters.create') }}" class="inline-flex items-center px-3 py-1 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            New Roster
                        </a>
                    </div>
                    
                    @if($rosters->count() > 0)
                        @php
                            $typeLabels = [
                                'specialist_doctor' => 'Specialist Doctor',
                                'medical_officer' => 'Medical Officer',
                                'houseman_officer' => 'Houseman Officer',
                                'nurse' => 'Nurse'
                            ];
                        @endphp
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entries</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($rosters as $roster)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                    <a href="{{ route('manage.rosters.show', $roster) }}">{{ $roster->name }}</a>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $typeLabels[$roster->staff_type] ?? $roster->staff_type }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $roster->roster_type_label }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $roster->start_date->format('M d') }} - {{ $roster->end_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $roster->is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $roster->is_published ? 'Published' : 'Draft' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $roster->entries_count ?? 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('manage.rosters.show', $roster) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                                <a href="{{ route('manage.rosters.edit', $roster) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <p class="text-gray-500 mb-3">No rosters found for this department.</p>
                            <a href="{{ route('manage.rosters.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create First Roster
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Roster Distribution Chart -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Roster Distribution by Staff Type</h3>
                    
                    @php
                        $typeLabels = [
                            'specialist_doctor' => 'Specialist Doctor',
                            'medical_officer' => 'Medical Officer',
                            'houseman_officer' => 'Houseman Officer',
                            'nurse' => 'Nurse'
                        ];
                        
                        $rostersByType = $rosters->groupBy('staff_type');
                        $rosterCounts = $rostersByType->map->count();
                    @endphp
                    
                    <div id="roster-distribution-chart" style="height: 300px;"></div>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const rosterData = @json($rosterCounts);
                            const types = Object.keys(rosterData);
                            const counts = Object.values(rosterData);
                            const typeLabels = @json(array_map(function($type) use ($typeLabels) {
                                return $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type));
                            }, array_keys($rosterCounts->toArray())));
                            
                            if (types.length > 0) {
                                const ctx = document.getElementById('roster-distribution-chart');
                                new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: typeLabels,
                                        datasets: [{
                                            data: counts,
                                            backgroundColor: [
                                                'rgba(99, 102, 241, 0.7)', // indigo
                                                'rgba(249, 115, 22, 0.7)', // orange
                                                'rgba(16, 185, 129, 0.7)', // green
                                                'rgba(236, 72, 153, 0.7)' // pink
                                            ],
                                            borderColor: [
                                                'rgba(99, 102, 241, 1)',
                                                'rgba(249, 115, 22, 1)',
                                                'rgba(16, 185, 129, 1)',
                                                'rgba(236, 72, 153, 1)'
                                            ],
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'right',
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        const label = context.label || '';
                                                        const value = context.raw || 0;
                                                        const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                                        const percentage = ((value / total) * 100).toFixed(1);
                                                        return `${label}: ${value} (${percentage}%)`;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            } else {
                                document.getElementById('roster-distribution-chart').innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">No data available for chart</p></div>';
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 