@extends('dashboard')

@section('title', 'Leave Management Reports & Analytics')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Leave Reports & Analytics</h1>
                <p class="text-gray-600 mt-2">Comprehensive reporting system for leave management analysis</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.leave-management.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    ← Back to Dashboard
                </a>
                <button onclick="exportReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Report Filters</h3>
        </div>
        <form method="GET" action="{{ route('leave-management.reports') }}" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Report Type</label>
                    <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="employee" {{ $reportType == 'employee' ? 'selected' : '' }}>Employee Report</option>
                        <option value="department" {{ $reportType == 'department' ? 'selected' : '' }}>Department Report</option>
                        <option value="leave_type" {{ $reportType == 'leave_type' ? 'selected' : '' }}>Leave Type Report</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="department" name="department" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $department == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="leave_type" class="block text-sm font-medium text-gray-700">Leave Type</label>
                    <select id="leave_type" name="leave_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Leave Types</option>
                        @foreach($leaveTypes as $type)
                        <option value="{{ $type }}" {{ $leaveType == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                    <select id="employee_id" name="employee_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }} {{ $emp->lastname }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium mr-3">
                        Generate Report
                    </button>
                    <button type="button" onclick="clearFilters()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium">
                        Clear Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Report Content -->
    @if(isset($reportData))
    <div class="bg-white rounded-lg shadow-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ ucfirst(str_replace('_', ' ', $reportType)) }} Report
                    <span class="text-sm font-normal text-gray-500">
                        ({{ \Carbon\Carbon::parse($dateFrom)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M j, Y') }})
                    </span>
                </h3>
                <div class="flex space-x-2">
                    <button onclick="exportReport('pdf')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                        PDF
                    </button>
                    <button onclick="exportReport('excel')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                        Excel
                    </button>
                    <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                        Print
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($reportType == 'employee')
                <!-- Employee Report -->
                <div class="space-y-6">
                    @foreach($reportData as $userId => $data)
                    <div class="border border-gray-200 rounded-lg">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-900">
                                {{ $data['employee']->name }} {{ $data['employee']->lastname }}
                            </h4>
                            <p class="text-sm text-gray-600">{{ $data['employee']->employee->department ?? 'N/A' }} • Employee ID: {{ $data['employee']->employee->employee_id ?? 'N/A' }}</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-blue-600">{{ $data['total_requests'] }}</p>
                                    <p class="text-sm text-gray-600">Total Requests</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-green-600">{{ $data['approved'] }}</p>
                                    <p class="text-sm text-gray-600">Approved</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-yellow-600">{{ $data['pending'] }}</p>
                                    <p class="text-sm text-gray-600">Pending</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-red-600">{{ $data['rejected'] }}</p>
                                    <p class="text-sm text-gray-600">Rejected</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-purple-600">{{ $data['total_days'] }}</p>
                                    <p class="text-sm text-gray-600">Total Days</p>
                                </div>
                            </div>

                            <!-- Individual Requests -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leave Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applied On</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($data['requests'] as $request)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $request->leave_type }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $request->start_date->format('M j, Y') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $request->end_date->format('M j, Y') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $request->days_requested }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    {{ $request->status == 'approved' ? 'bg-green-100 text-green-800' : 
                                                       ($request->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $request->created_at->format('M j, Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

            @elseif($reportType == 'department')
                <!-- Department Report -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Requests</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pending</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rejected</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employees</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Days/Request</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reportData as $dept => $data)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $data['department'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['total_requests']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['total_days']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">{{ number_format($data['approved']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">{{ number_format($data['pending']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">{{ number_format($data['rejected']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['employees']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $data['total_requests'] > 0 ? round($data['total_days'] / $data['total_requests'], 1) : 0 }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @elseif($reportType == 'leave_type')
                <!-- Leave Type Report -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leave Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Requests</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pending</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rejected</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approval Rate</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reportData as $type => $data)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $data['leave_type'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['total_requests']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['total_days']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">{{ number_format($data['approved']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">{{ number_format($data['pending']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">{{ number_format($data['rejected']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['average_duration'] }} days</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $approvalRate = ($data['approved'] + $data['rejected']) > 0 ? 
                                            round(($data['approved'] / ($data['approved'] + $data['rejected'])) * 100, 1) : 0;
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $approvalRate >= 80 ? 'bg-green-100 text-green-800' : 
                                           ($approvalRate >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $approvalRate }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Report Summary -->
            <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Report Summary</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @php
                        $totalRequests = collect($reportData)->sum('total_requests');
                        $totalDays = collect($reportData)->sum('total_days');
                        $totalApproved = collect($reportData)->sum('approved');
                        $totalPending = collect($reportData)->sum('pending');
                        $totalRejected = collect($reportData)->sum('rejected');
                        $approvalRate = ($totalApproved + $totalRejected) > 0 ? 
                            round(($totalApproved / ($totalApproved + $totalRejected)) * 100, 1) : 0;
                    @endphp
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($totalRequests) }}</p>
                        <p class="text-sm text-gray-600">Total Requests</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ number_format($totalDays) }}</p>
                        <p class="text-sm text-gray-600">Total Leave Days</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $approvalRate }}%</p>
                        <p class="text-sm text-gray-600">Approval Rate</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-orange-600">
                            {{ $totalRequests > 0 ? round($totalDays / $totalRequests, 1) : 0 }}
                        </p>
                        <p class="text-sm text-gray-600">Avg Days per Request</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow-lg p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Generate a Report</h3>
        <p class="text-gray-600 mb-6">Select your filters and click "Generate Report" to view leave analytics</p>
        <button onclick="document.querySelector('form button[type=submit]').click()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
            Generate Report
        </button>
    </div>
    @endif
        </div>
    </div>
</div>

<script>
function clearFilters() {
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.type === 'date') {
            input.value = '';
        } else if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
        }
    });
}

async function exportReport(format = 'pdf') {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (format === 'pdf') {
        try {
            // Use the dedicated PDF export route with proper error handling
            const pdfUrl = `{{ route('leave-management.export-pdf') }}?${urlParams.toString()}`;
            const response = await fetch(pdfUrl);
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/pdf')) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'leave-management-report-' + new Date().toISOString().split('T')[0] + '.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                } else {
                    const errorText = await response.text();
                    console.error('PDF Error Response:', errorText);
                    alert('Failed to generate PDF: ' + errorText);
                }
            } else {
                const errorText = await response.text();
                console.error('HTTP Error:', response.status, errorText);
                alert('Failed to generate PDF report. HTTP ' + response.status + ': ' + errorText);
            }
        } catch (error) {
            console.error('Error downloading PDF:', error);
            alert('Error downloading PDF: ' + error.message);
        }
    } else {
        // Use the generic export route for other formats
        urlParams.set('format', format);
        
        fetch(`{{ route('leave-management.export-report') }}?${urlParams.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.download_url) {
                    // Simulate file download
                    const link = document.createElement('a');
                    link.href = data.download_url;
                    link.download = `leave-report-${format}-${new Date().getTime()}`;
                    link.click();
                }
                alert(data.message);
            })
            .catch(error => {
                alert('Error exporting report: ' + error.message);
            });
    }
}

// Print styles
const printStyles = `
@media print {
    body * {
        visibility: hidden;
    }
    .bg-white, .bg-white * {
        visibility: visible;
    }
    .bg-white {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    button, .no-print {
        display: none !important;
    }
}
`;

// Add print styles to document
const style = document.createElement('style');
style.textContent = printStyles;
document.head.appendChild(style);
</script>
@endsection
