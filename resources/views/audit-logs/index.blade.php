@extends('dashboard')

@section('title', 'Audit Logs')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div style="width: 100%; padding: 0 1rem;">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 mb-4">
                <form method="GET" action="{{ route('audit-logs.index') }}">
                    <div class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search..."
                                   class="w-full px-3 py-2 border rounded">
                        </div>
                        <div class="min-w-[150px]">
                            <label class="block text-sm mb-1">Category</label>
                            <select name="category" class="w-full px-3 py-2 border rounded">
                                <option value="">All</option>
                                <option value="failed_logins" {{ request('category') == 'failed_logins' ? 'selected' : '' }}>Failed Login Attempts</option>
                                <option value="data_changes" {{ request('category') == 'data_changes' ? 'selected' : '' }}>Data Changes</option>
                                <option value="account_changes" {{ request('category') == 'account_changes' ? 'selected' : '' }}>Account Changes</option>
                                <option value="authentication" {{ request('category') == 'authentication' ? 'selected' : '' }}>Authentication</option>
                            </select>
                        </div>
                        <div class="min-w-[150px]">
                            <label class="block text-sm mb-1">Action Type</label>
                            <select name="action_type" class="w-full px-3 py-2 border rounded">
                                <option value="">All</option>
                                @foreach($actionTypes as $type)
                                    <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $type)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-w-[150px]">
                            <label class="block text-sm mb-1">User</label>
                            <select name="user_id" class="w-full px-3 py-2 border rounded">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} {{ $user->lastname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Start Date</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                                   class="px-3 py-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">End Date</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                                   class="px-3 py-2 border rounded">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Filter
                            </button>
                            <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50">
                                Clear
                            </a>
                            @if(Auth::user()->isSuperAdmin())
                                <a href="{{ route('audit-logs.export', request()->all()) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                    Export
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border text-left text-sm font-medium">ID</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Date/Time</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">User</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Action</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">Description</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium">IP Address</th>
                            @if(Auth::user()->isSuperAdmin())
                            <th class="px-4 py-2 border text-left text-sm font-medium">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border text-sm">{{ $log->id }}</td>
                            <td class="px-4 py-2 border text-sm">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-2 border text-sm">
                                @if($log->user)
                                    {{ $log->user_name }}
                                @else
                                    <span class="text-gray-500">Guest</span>
                                @endif
                            </td>
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
                            @if(Auth::user()->isSuperAdmin())
                            <td class="px-4 py-2 border text-sm">
                                <form action="{{ route('audit-logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Delete this log?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn action-btn--delete">
                                        Delete
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ Auth::user()->isSuperAdmin() ? '7' : '6' }}" class="px-4 py-8 border text-center text-gray-500">
                                No audit logs found.
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

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('{{ session('success') }}');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('{{ session('error') }}');
    });
</script>
@endif
@endsection
