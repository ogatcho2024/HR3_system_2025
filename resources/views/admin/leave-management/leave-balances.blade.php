@extends('dashboard')

@section('title', 'Leave Balances Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="py-6">
        <div style="width: 100%; padding: 0 0.5rem;">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Leave Balances Management</h1>
                    <p class="text-gray-600 mt-1">Monitor and adjust employee leave balances</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Time Widget -->
                    <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm text-gray-600" id="current-time"></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Year Filter -->
            <div class="mb-6 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <label for="year-select" class="text-sm font-medium text-gray-700">Select Year:</label>
                    <select id="year-select" name="year" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                            onchange="window.location.href = '{{ route('leave-management.leave-balances') }}?year=' + this.value">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                
                <button type="button" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium"
                        onclick="openAdjustBalanceModal()">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adjust Balance
                </button>
            </div>

            <!-- Leave Balances Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200" style="width: 100%;">
                <div class="px-4 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Employee Leave Balances ({{ $year }})</h3>
                </div>
            
            <div class="overflow-x-auto" style="width: 100%;">
                <table class="min-w-full divide-y divide-gray-200" style="width: 100%;">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entitled</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carried Forward</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($leaveBalances as $userId => $userBalances)
                            @php
                                $user = $userBalances->first()->user;
                                $employee = $user->employee;
                            @endphp
                            @foreach($userBalances as $balance)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">
                                                        {{ strtoupper(substr($user->name, 0, 1) . substr($user->lastname ?? '', 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }} {{ $user->lastname }}</div>
                                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $employee->department ?? 'N/A' }}
                                    </td>
                                    <td class="px-2 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($balance->leave_type === 'Annual') bg-green-100 text-green-800
                                            @elseif($balance->leave_type === 'Sick') bg-red-100 text-red-800
                                            @elseif($balance->leave_type === 'Personal') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $balance->leave_type }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($balance->total_entitled ?? 0, 2) }}
                                    </td>
                                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($balance->used ?? 0, 2) }}
                                    </td>
                                    <td class="px-2 py-4 whitespace-nowrap text-sm text-yellow-600">
                                        {{ number_format($balance->pending ?? 0, 2) }}
                                    </td>
                                    <td class="px-2 py-4 whitespace-nowrap text-sm font-medium 
                                        @if(($balance->available ?? 0) <= 5) text-red-600 @else text-green-600 @endif">
                                        {{ number_format($balance->available ?? 0, 2) }}
                                    </td>
                                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($balance->carried_forward ?? 0, 2) }}
                                    </td>
                                    <td class="px-2 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" class="text-indigo-600 hover:text-indigo-900"
                                                onclick="openAdjustBalanceModal({{ $balance->user_id }}, '{{ $balance->leave_type }}', {{ $year }})">
                                            Adjust
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-lg font-medium">No leave balances found</p>
                                        <p class="text-sm">Leave balances will appear here once employees start using leave or when balances are manually set.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</div>

<!-- Adjust Balance Modal -->
<div id="adjustBalanceModal" class="fixed inset-0 bg-white/30 backdrop-blur-sm hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeAdjustBalanceModal()">
            <div class="absolute inset-0 bg-white/30"></div>
        </div>

        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto my-8 z-10">
            <form id="adjustBalanceForm" method="POST" action="{{ route('leave-management.adjust-balance') }}">
                @csrf
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-indigo-100">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <h3 class="ml-4 text-lg leading-6 font-medium text-gray-900">Adjust Leave Balance</h3>
                        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-600 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" onclick="closeAdjustBalanceModal()">
                            <span class="sr-only">Close</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                            <select name="user_id" id="modal_user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select Employee</option>
                                @foreach($leaveBalances as $userId => $userBalances)
                                    @php $user = $userBalances->first()->user; @endphp
                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
                            <select name="leave_type" id="modal_leave_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select Leave Type</option>
                                <option value="Annual">Annual Leave</option>
                                <option value="Sick">Sick Leave</option>
                                <option value="Personal">Personal Leave</option>
                                <option value="Maternity">Maternity Leave</option>
                                <option value="Paternity">Paternity Leave</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <input type="number" name="year" id="modal_year" min="2020" max="2030" value="{{ $year }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Type</label>
                            <select name="adjustment_type" id="modal_adjustment_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select Adjustment Type</option>
                                <option value="total_entitled">Total Entitled Days</option>
                                <option value="used">Used Days</option>
                                <option value="carried_forward">Carried Forward Days</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Value</label>
                            <input type="number" name="adjustment_value" id="modal_adjustment_value" min="0" step="0.01"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Adjustment</label>
                            <textarea name="reason" id="modal_reason" rows="3" 
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                      placeholder="Enter reason for this adjustment..." required></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 space-y-2 space-y-reverse sm:space-y-0">
                        <button type="button" onclick="closeAdjustBalanceModal()" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </button>
                        <button type="submit" class="w-full ml-2 sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Adjust Balance
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Time Display Function
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleString('en-US', { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });
    document.getElementById('current-time').textContent = timeString;
}

// Update time immediately and then every second
updateTime();
setInterval(updateTime, 1000);

// Modal Functions
function openAdjustBalanceModal(userId = null, leaveType = null, year = null) {
    document.getElementById('adjustBalanceModal').classList.remove('hidden');
    
    if (userId) {
        document.getElementById('modal_user_id').value = userId;
    }
    if (leaveType) {
        document.getElementById('modal_leave_type').value = leaveType;
    }
    if (year) {
        document.getElementById('modal_year').value = year;
    }
}

function closeAdjustBalanceModal() {
    document.getElementById('adjustBalanceModal').classList.add('hidden');
    document.getElementById('adjustBalanceForm').reset();
}

// Close modal when clicking outside or pressing Escape
document.getElementById('adjustBalanceModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAdjustBalanceModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('adjustBalanceModal').classList.contains('hidden')) {
        closeAdjustBalanceModal();
    }
});
</script>
@endsection
