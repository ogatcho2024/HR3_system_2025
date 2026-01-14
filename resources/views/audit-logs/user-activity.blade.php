@extends('dashboard')

@section('title', 'User Activity')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div style="width: 100%; padding: 0 1rem;">
            <!-- Header -->
            <div class="mb-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Activity Timeline</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ $user->name }} {{ $user->lastname }} ({{ $user->email }})</p>
                </div>
                <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50">
                    Back to Audit Logs
                </a>
            </div>

            <!-- Activity Logs Table -->
            <div class="bg-white">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Date/Time</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Action</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Description</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border text-sm">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-2 border text-sm">
                                <span class="px-2 py-1 text-xs rounded
                                    @if($log->action_type === 'login') bg-green-100 text-green-800
                                    @elseif($log->action_type === 'logout') bg-gray-200 text-gray-800
                                    @elseif($log->action_type === 'failed_login') bg-red-100 text-red-800
                                    @elseif($log->action_type === 'otp_verified') bg-blue-100 text-blue-800
                                    @elseif($log->action_type === 'otp_failed') bg-orange-100 text-orange-800
                                    @elseif($log->action_type === 'account_created') bg-green-100 text-green-800
                                    @elseif($log->action_type === 'account_updated') bg-yellow-100 text-yellow-800
                                    @elseif($log->action_type === 'account_deleted') bg-red-100 text-red-800
                                    @elseif($log->action_type === 'password_changed') bg-purple-100 text-purple-800
                                    @elseif($log->action_type === 'email_changed') bg-indigo-100 text-indigo-800
                                    @elseif($log->action_type === 'role_changed') bg-pink-100 text-pink-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border text-sm">{{ $log->description }}</td>
                            <td class="px-4 py-2 border text-sm">{{ $log->ip_address }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 border text-center text-gray-500">
                                No activity found for this user.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if($logs->hasPages())
                <div class="px-4 py-3 border-t">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
