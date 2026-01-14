@extends('dashboard')

@section('title', 'Security Report')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div style="width: 100%; padding: 0 1rem;">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Security Report</h1>
                <p class="text-sm text-gray-600 mt-1">Last {{ $days }} days</p>
            </div>

            <!-- Time Range Filter -->
            <div class="bg-white p-4 mb-4">
                <form method="GET" action="{{ route('audit-logs.security-report') }}">
                    <div class="flex gap-3 items-end">
                        <div>
                            <label class="block text-sm mb-1">Days</label>
                            <select name="days" class="px-3 py-2 border rounded">
                                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                                <option value="14" {{ $days == 14 ? 'selected' : '' }}>Last 14 days</option>
                                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Apply
                            </button>
                        </div>
                        <div class="ml-auto">
                            <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50">
                                Back to Audit Logs
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-white p-4">
                    <div class="text-sm text-gray-600">Total Failed Logins</div>
                    <div class="text-2xl font-bold text-red-600">{{ $failedLogins->flatten()->count() }}</div>
                </div>
                <div class="bg-white p-4">
                    <div class="text-sm text-gray-600">Suspicious IPs</div>
                    <div class="text-2xl font-bold text-orange-600">{{ $suspiciousIps->count() }}</div>
                </div>
                <div class="bg-white p-4">
                    <div class="text-sm text-gray-600">OTP Failures</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $otpFailures }}</div>
                </div>
            </div>

            <!-- Failed Logins by IP -->
            <div class="bg-white mb-4">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-bold">Failed Login Attempts by IP Address</h2>
                </div>
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border text-left text-sm font-medium">IP Address</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Attempts</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Last Attempt</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($failedLogins as $ip => $logs)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border text-sm">{{ $ip }}</td>
                            <td class="px-4 py-2 border text-sm">{{ $logs->count() }}</td>
                            <td class="px-4 py-2 border text-sm">{{ $logs->first()->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-2 border text-sm">
                                @if($logs->count() >= 5)
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Suspicious</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">Normal</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 border text-center text-gray-500">
                                No failed login attempts found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suspiciousIps->count() > 0)
            <!-- Suspicious Activity Details -->
            <div class="bg-white">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-bold text-red-600">Suspicious Activity Details</h2>
                    <p class="text-sm text-gray-600 mt-1">IPs with 5 or more failed login attempts</p>
                </div>
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border text-left text-sm font-medium">IP Address</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Attempts</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Time Range</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suspiciousIps as $ip => $logs)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border text-sm font-bold">{{ $ip }}</td>
                            <td class="px-4 py-2 border text-sm">{{ $logs->count() }}</td>
                            <td class="px-4 py-2 border text-sm">
                                {{ $logs->last()->created_at->format('Y-m-d H:i') }} - 
                                {{ $logs->first()->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-2 border text-sm">
                                @php
                                    $emails = $logs->pluck('description')->map(function($desc) {
                                        preg_match('/email:\s*(.+)/', $desc, $matches);
                                        return $matches[1] ?? 'Unknown';
                                    })->unique();
                                @endphp
                                Targeted: {{ $emails->implode(', ') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
