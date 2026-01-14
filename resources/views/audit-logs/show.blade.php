@extends('dashboard')

@section('title', 'Audit Log Details')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div style="width: 100%; padding: 0 1rem;">
            <!-- Header -->
            <div class="mb-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Audit Log #{{ $log->id }}</h1>
                <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50">
                    Back to List
                </a>
            </div>

            <!-- Log Details -->
            <div class="bg-white">
                <table class="min-w-full">
                    <tbody>
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold w-1/4">ID</td>
                            <td class="px-4 py-3">{{ $log->id }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold">Timestamp</td>
                            <td class="px-4 py-3">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold">User</td>
                            <td class="px-4 py-3">
                                @if($log->user)
                                    {{ $log->user_name }} ({{ $log->user->email }})
                                @else
                                    <span class="text-gray-500">Guest</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold">Action Type</td>
                            <td class="px-4 py-3">
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
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold">Description</td>
                            <td class="px-4 py-3">{{ $log->description }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold">IP Address</td>
                            <td class="px-4 py-3">{{ $log->ip_address }}</td>
                        </tr>
                        @if($log->user_agent)
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold">User Agent</td>
                            <td class="px-4 py-3 text-sm">{{ $log->user_agent }}</td>
                        </tr>
                        @endif
                        @if($log->affected_table)
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold">Affected Table</td>
                            <td class="px-4 py-3">{{ $log->affected_table }}</td>
                        </tr>
                        @endif
                        @if($log->affected_record_id)
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold">Affected Record ID</td>
                            <td class="px-4 py-3">{{ $log->affected_record_id }}</td>
                        </tr>
                        @endif
                        @if($log->login_attempt_count > 0)
                        <tr class="border-b">
                            <td class="px-4 py-3 bg-gray-50 font-semibold">Login Attempt Count</td>
                            <td class="px-4 py-3">{{ $log->login_attempt_count }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>

                @if($log->old_values || $log->new_values)
                <div class="p-4 border-t">
                    <h3 class="font-semibold mb-3">Data Changes</h3>
                    
                    @if($log->old_values)
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Old Values:</h4>
                        <pre class="bg-gray-50 p-3 rounded text-sm overflow-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif

                    @if($log->new_values)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">New Values:</h4>
                        <pre class="bg-gray-50 p-3 rounded text-sm overflow-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
