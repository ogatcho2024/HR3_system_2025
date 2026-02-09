@extends('dashboard-user')

@section('title', 'My Shift Requests')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="shiftRequestsManagement()">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
                {{ session('success') }}
            </div>
            @endif
            
            @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                {{ session('error') }}
            </div>
            @endif
            
            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">My Shift Requests</h3>
                        <p class="text-gray-600 mt-2">Manage your shift change requests</p>
                    </div>
                    <div>
                        <button @click="showCreateModal = true" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Request
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button 
                            @click="activeTab = 'pending'" 
                            :class="activeTab === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                        >
                            Pending
                            <span class="ml-2 bg-yellow-100 text-yellow-800 py-1 px-2 rounded-full text-xs">
                                {{ $pendingShiftRequests->total() }}
                            </span>
                        </button>
                        <button 
                            @click="activeTab = 'approved'"
                            :class="activeTab === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                        >
                            Approved
                            <span class="ml-2 bg-green-100 text-green-800 py-1 px-2 rounded-full text-xs">
                                {{ $approvedShiftRequests->total() }}
                            </span>
                        </button>
                        <button 
                            @click="activeTab = 'rejected'"
                            :class="activeTab === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                        >
                            Rejected
                            <span class="ml-2 bg-red-100 text-red-800 py-1 px-2 rounded-full text-xs">
                                {{ $rejectedShiftRequests->total() }}
                            </span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Pending Shift Requests -->
            <div x-show="activeTab === 'pending'" class="space-y-6">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">
                            Pending Requests ({{ $pendingShiftRequests->total() }})
                        </h2>
                    </div>
                    
                    @if($pendingShiftRequests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingShiftRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->request_date->format('M j, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ Str::limit($request->reason ?? 'No reason provided', 50) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form action="{{ route('employee.shift-requests.destroy', $request) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this shift request?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn action-btn--reject">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($pendingShiftRequests->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $pendingShiftRequests->appends(request()->query())->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No pending requests</h3>
                        <p class="mt-1 text-sm text-gray-500">Start by creating a new shift request.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Approved Shift Requests -->
            <div x-show="activeTab === 'approved'" class="space-y-6">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">
                            Approved Requests ({{ $approvedShiftRequests->total() }})
                        </h2>
                    </div>
                    
                    @if($approvedShiftRequests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($approvedShiftRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->request_date->format('M j, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $request->approved_at ? $request->approved_at->format('M j, Y') : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="viewRequest({{ $request->id }})" class="text-indigo-600 hover:text-indigo-900">View</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($approvedShiftRequests->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $approvedShiftRequests->appends(request()->query())->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No approved requests</h3>
                        <p class="mt-1 text-sm text-gray-500">Your approved shift requests will appear here.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Rejected Shift Requests -->
            <div x-show="activeTab === 'rejected'" class="space-y-6">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">
                            Rejected Requests ({{ $rejectedShiftRequests->total() }})
                        </h2>
                    </div>
                    
                    @if($rejectedShiftRequests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejection Reason</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($rejectedShiftRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->request_date->format('M j, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ Str::limit($request->rejection_reason ?? 'No reason provided', 50) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="viewRequest({{ $request->id }})" class="text-indigo-600 hover:text-indigo-900">View</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($rejectedShiftRequests->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $rejectedShiftRequests->appends(request()->query())->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No rejected requests</h3>
                        <p class="mt-1 text-sm text-gray-500">Your rejected shift requests will appear here.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Create Shift Request Modal -->
<div x-show="showCreateModal" 
     x-cloak
     class="fixed z-10 inset-0 overflow-y-auto" 
     @keydown.escape.window="showCreateModal = false">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" @click="showCreateModal = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('employee.shift-requests.store') }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">New Shift Request</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Request Type</label>
                            <select name="request_type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select type...</option>
                                <option value="schedule_change">Schedule Change</option>
                                <option value="swap">Shift Swap</option>
                                <option value="cover">Cover Request</option>
                                <option value="overtime">Overtime Request</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="requested_date" required min="{{ date('Y-m-d') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Current Start Time</label>
                                <input type="time" name="current_start_time" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Current End Time</label>
                                <input type="time" name="current_end_time" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Requested Start Time</label>
                                <input type="time" name="requested_start_time" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Requested End Time</label>
                                <input type="time" name="requested_end_time" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason</label>
                            <textarea name="reason" required rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Please provide a reason for your shift request..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-400 ml-2 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Submit Request
                    </button>
                    <button type="button" @click="showCreateModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
function shiftRequestsManagement() {
    return {
        activeTab: 'pending',
        showCreateModal: false,
        
        viewRequest(requestId) {
            // Handle view request logic
            console.log('View request:', requestId);
        },
        
        editRequest(requestId) {
            // Handle edit request logic
            console.log('Edit request:', requestId);
        }
    }
}
</script>
@endsection
