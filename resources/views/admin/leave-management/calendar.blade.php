@extends('dashboard')

@section('title', 'Leave Calendar')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="py-6">
        <div style="width: 100%; padding: 0 1rem;">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Leave Calendar</h1>
                    <p class="text-gray-600 mt-1">View approved leaves for the selected month</p>
                </div>
                <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                    <span class="text-sm text-gray-600" id="current-time"></span>
                </div>
            </div>
            <form method="GET" action="{{ route('leave-management.calendar') }}" class="mb-6 flex items-center space-x-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <select name="month" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ (int)$month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0,0,0,$m,1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                    <select name="year" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="pt-6">
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium">Apply</button>
                </div>
            </form>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Approved Leaves for {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</h3>
                </div>

            <div class="p-6">
                @if($leaveRequests->isEmpty())
                    <div class="text-center text-gray-500">No approved leaves for the selected period.</div>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach($leaveRequests as $request)
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</div>
                                    <div class="text-sm text-gray-600">{{ $request->leave_type }} • {{ $request->start_date->format('M d, Y') }} → {{ $request->end_date->format('M d, Y') }}</div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">Approved</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleString();
}
updateTime();
setInterval(updateTime, 1000);
</script>
@endsection
