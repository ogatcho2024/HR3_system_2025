@extends('dashboard')

@section('title', 'Leave Reports & Analytics')

@section('content')
<div class="min-h-screen bg-gray-300">
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
                        <button onclick="exportReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            Export Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Requests -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Requests</p>
                            <p class="text-2xl font-semibold text-blue-600">
                                @php
                                    $totalRequests = \App\Models\LeaveRequest::count();
                                @endphp
                                {{ $totalRequests }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pending Requests</p>
                            <p class="text-2xl font-semibold text-yellow-600">
                                @php
                                    $pendingRequests = \App\Models\LeaveRequest::where('status', 'pending')->count();
                                @endphp
                                {{ $pendingRequests }}
                            </p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Approved Requests -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Approved Requests</p>
                            <p class="text-2xl font-semibold text-green-600">
                                @php
                                    $approvedRequests = \App\Models\LeaveRequest::where('status', 'approved')->count();
                                @endphp
                                {{ $approvedRequests }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Rejected Requests -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Rejected Requests</p>
                            <p class="text-2xl font-semibold text-red-600">
                                @php
                                    $rejectedRequests = \App\Models\LeaveRequest::where('status', 'rejected')->count();
                                @endphp
                                {{ $rejectedRequests }}
                            </p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Leave Requests Table -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Leave Requests</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white-500 uppercase tracking-wider">Leave Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white-500 uppercase tracking-wider">Date Applied</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $recentRequests = \App\Models\LeaveRequest::with(['user'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get();
                            @endphp
                            
                            @forelse($recentRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $request->user->name ?? 'N/A' }} {{ $request->user->lastname ?? '' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $request->user->email ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                            {{ $request->leave_type ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($request->start_date && $request->end_date)
                                            {{ \Carbon\Carbon::parse($request->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                                            <div class="text-xs text-gray-500">
                                                {{ $request->days_requested ?? 0 }} days
                                            </div>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($request->status == 'approved') bg-green-100 text-green-800
                                            @elseif($request->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($request->status ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $request->created_at ? $request->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z"></path>
                                            </svg>
                                            <p class="text-lg font-medium">No leave requests found</p>
                                            <p class="text-sm">Leave requests will appear here once employees start submitting them.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Report Summary -->
            <div class="mt-8 bg-white rounded-xl shadow p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Analytics Summary</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @php
                        $totalDays = \App\Models\LeaveRequest::sum('days_requested') ?? 0;
                        $avgDays = \App\Models\LeaveRequest::avg('days_requested') ?? 0;
                        $approvalRate = $totalRequests > 0 ? round(($approvedRequests / $totalRequests) * 100, 1) : 0;
                    @endphp
                    
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ number_format($totalDays) }}</p>
                        <p class="text-sm text-gray-600">Total Leave Days</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $approvalRate }}%</p>
                        <p class="text-sm text-gray-600">Approval Rate</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-orange-600">{{ round($avgDays, 1) }}</p>
                        <p class="text-sm text-gray-600">Avg Days per Request</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    const status = document.getElementById('status').value;
    
    // For now, just show an alert. You can implement actual filtering later
    let message = 'Generating report...';
    if (dateFrom || dateTo || status) {
        message += '\nFilters applied:';
        if (dateFrom) message += '\n- From: ' + dateFrom;
        if (dateTo) message += '\n- To: ' + dateTo;
        if (status) message += '\n- Status: ' + status;
    }
    
    alert(message);
    
    // You can add actual AJAX call here to filter data
    // For now, we'll just reload the page
    setTimeout(() => {
        location.reload();
    }, 1000);
}

async function exportReport() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    const status = document.getElementById('status').value;
    
    try {
        // Build query parameters
        const params = new URLSearchParams();
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);
        if (status) params.set('status', status);
        
        // Export to PDF with proper error handling
        const pdfUrl = `{{ route('leave-management.export-pdf') }}?${params.toString()}`;
        const response = await fetch(pdfUrl);
        
        if (response.ok) {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/pdf')) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'leave-reports-analytics-' + new Date().toISOString().split('T')[0] + '.pdf';
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
}

// Auto-update time if needed
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleString();
    
    // Add current time display if you want
    console.log('Page loaded at:', timeString);
}

document.addEventListener('DOMContentLoaded', function() {
    updateTime();
});
</script>
@endsection