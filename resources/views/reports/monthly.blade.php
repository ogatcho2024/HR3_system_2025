@extends('dashboard')

@section('title', 'Monthly Reports')

@section('content')
<div class="w-full p-3 sm:px-4">

    <!-- Breadcrumbs -->
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        ['label' => 'Reports', 'url' => route('reports.index')],
        ['label' => 'Monthly Reports', 'url' => route('reports.monthly')]
    ]])

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold text-gray-900">Monthly Reports</h3>
                <p class="text-gray-600 mt-2">Monthly trends and key performance metrics</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    ← Back to Reports
                </a>
                <button onclick="window.print()" class="px-4 py-2 mr-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                    Print Report
                </button>
            </div>
        </div>
    </div>

    <!-- Month Selection -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Select Month</h4>
        <form method="GET" action="{{ route('reports.monthly') }}" class="flex items-end space-x-4">
            <div>
                <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                <input type="month" name="month" id="month" 
                       value="{{ $month ?? \Carbon\Carbon::now()->format('Y-m') }}" 
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                Generate Report
            </button>
        </form>
    </div>

    @php
        $selectedMonth = $month ?? \Carbon\Carbon::now()->format('Y-m');
        $monthName = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');
    @endphp

    <!-- Current Month Statistics -->
    <div class="mb-8">
        <h4 class="text-xl font-semibold text-gray-900 mb-4">{{ $monthName }} Overview</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- New Employees -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">New Employees</p>
                        <p class="text-2xl font-bold">{{ $stats['new_employees'] ?? 0 }}</p>
                    </div>
                    <div class="p-2 bg-blue-400 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Leave Requests -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Leave Requests</p>
                        <p class="text-2xl font-bold">{{ $stats['leave_requests'] ?? 0 }}</p>
                    </div>
                    <div class="p-2 bg-green-400 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Approved Leaves -->
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm">Approved Leaves</p>
                        <p class="text-2xl font-bold">{{ $stats['approved_leaves'] ?? 0 }}</p>
                    </div>
                    <div class="p-2 bg-purple-400 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Timesheets Submitted -->
            <div class="bg-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm">Timesheets Submitted</p>
                        <p class="text-2xl font-bold">{{ $stats['timesheets_submitted'] ?? 0 }}</p>
                    </div>
                    <div class="p-2 bg-orange-400 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($monthlyTrends) && count($monthlyTrends) > 0)
        <!-- Monthly Trends -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">12-Month Trends</h4>
            
            <!-- Trends Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Employees</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Requests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($monthlyTrends as $trend)
                            @php
                                $isCurrentMonth = $trend['month'] == $selectedMonth;
                            @endphp
                            <tr class="{{ $isCurrentMonth ? 'bg-orange-50' : 'hover:bg-gray-50' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium {{ $isCurrentMonth ? 'text-orange-900' : 'text-gray-900' }}">
                                        {{ $trend['month_name'] }}
                                        @if($isCurrentMonth)
                                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-orange-200 text-orange-800">Current</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-lg font-semibold {{ $isCurrentMonth ? 'text-orange-600' : 'text-blue-600' }}">
                                            {{ $trend['employees'] }}
                                        </span>
                                        @if($trend['employees'] > 0)
                                            <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full" 
                                                     style="width: {{ min(100, ($trend['employees'] / 10) * 100) }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-lg font-semibold {{ $isCurrentMonth ? 'text-orange-600' : 'text-green-600' }}">
                                            {{ $trend['leave_requests'] }}
                                        </span>
                                        @if($trend['leave_requests'] > 0)
                                            <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full" 
                                                     style="width: {{ min(100, ($trend['leave_requests'] / 20) * 100) }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $total_activity = $trend['employees'] + $trend['leave_requests'];
                                    @endphp
                                    @if($total_activity > 15)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">High</span>
                                    @elseif($total_activity > 5)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Medium</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Low</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Trend Summary -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-6">Trend Analysis</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total New Employees (12 months) -->
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ collect($monthlyTrends)->sum('employees') }}
                    </div>
                    <div class="text-sm text-gray-600">Total New Employees (12 months)</div>
                </div>

                <!-- Total Leave Requests (12 months) -->
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">
                        {{ collect($monthlyTrends)->sum('leave_requests') }}
                    </div>
                    <div class="text-sm text-gray-600">Total Leave Requests (12 months)</div>
                </div>

                <!-- Average Monthly Activity -->
                <div class="text-center p-4 bg-orange-50 rounded-lg">
                    <div class="text-2xl font-bold text-orange-600">
                        {{ round((collect($monthlyTrends)->sum('employees') + collect($monthlyTrends)->sum('leave_requests')) / 12, 1) }}
                    </div>
                    <div class="text-sm text-gray-600">Avg Monthly Activity</div>
                </div>
            </div>

            <!-- Key Insights -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h5 class="text-md font-semibold text-gray-900 mb-2">Key Insights</h5>
                <div class="space-y-2 text-sm text-gray-700">
                    @php
                        $bestMonth = collect($monthlyTrends)->sortByDesc(function($trend) {
                            return $trend['employees'] + $trend['leave_requests'];
                        })->first();
                        $quietMonth = collect($monthlyTrends)->sortBy(function($trend) {
                            return $trend['employees'] + $trend['leave_requests'];
                        })->first();
                    @endphp
                    @if($bestMonth)
                        <p>• <strong>Most Active Month:</strong> {{ $bestMonth['month_name'] }} with {{ $bestMonth['employees'] + $bestMonth['leave_requests'] }} total activities</p>
                    @endif
                    @if($quietMonth)
                        <p>• <strong>Quietest Month:</strong> {{ $quietMonth['month_name'] }} with {{ $quietMonth['employees'] + $quietMonth['leave_requests'] }} total activities</p>
                    @endif
                    @if(collect($monthlyTrends)->avg('employees') > 0)
                        <p>• <strong>Average New Hires per Month:</strong> {{ round(collect($monthlyTrends)->avg('employees'), 1) }}</p>
                    @endif
                    @if(collect($monthlyTrends)->avg('leave_requests') > 0)
                        <p>• <strong>Average Leave Requests per Month:</strong> {{ round(collect($monthlyTrends)->avg('leave_requests'), 1) }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
