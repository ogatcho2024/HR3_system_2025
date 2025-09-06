@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="page-title mb-0">
                    <i class="fas fa-bell me-2"></i>Notifications
                    @if($unreadCount > 0)
                        <span class="badge bg-danger ms-2">{{ $unreadCount }}</span>
                    @endif
                </h2>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="markAllAsRead()">
                        <i class="fas fa-check-double me-1"></i>Mark All Read
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearReadNotifications()">
                        <i class="fas fa-trash me-1"></i>Clear Read
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('notifications.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All</option>
                                <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
                                <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>Info</option>
                                <option value="success" {{ request('type') === 'success' ? 'selected' : '' }}>Success</option>
                                <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="error" {{ request('type') === 'error' ? 'selected' : '' }}>Error</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" id="category" class="form-select">
                                <option value="">All Categories</option>
                                <option value="leave" {{ request('category') === 'leave' ? 'selected' : '' }}>Leave</option>
                                <option value="timesheet" {{ request('category') === 'timesheet' ? 'selected' : '' }}>Timesheet</option>
                                <option value="shift" {{ request('category') === 'shift' ? 'selected' : '' }}>Shift</option>
                                <option value="system" {{ request('category') === 'system' ? 'selected' : '' }}>System</option>
                                <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>General</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            @if($notifications->count() > 0)
                <div class="card">
                    <div class="card-body">
                        <div class="notifications-list">
                            @foreach($notifications as $notification)
                                <div class="notification-item {{ !$notification->isRead() ? 'unread' : '' }}" 
                                     data-id="{{ $notification->id }}">
                                    <div class="d-flex align-items-start">
                                        <!-- Icon -->
                                        <div class="notification-icon me-3">
                                            <i class="{{ $notification->icon }}"></i>
                                        </div>

                                        <!-- Content -->
                                        <div class="notification-content flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h6 class="notification-title mb-0">
                                                    {{ $notification->title }}
                                                    @if($notification->is_important)
                                                        <i class="fas fa-star text-warning ms-1" title="Important"></i>
                                                    @endif
                                                </h6>
                                                <small class="text-muted">{{ $notification->time_ago }}</small>
                                            </div>
                                            
                                            <p class="notification-message mb-2">{{ $notification->message }}</p>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="notification-meta">
                                                    <span class="badge {{ $notification->badge_class }} me-2">{{ ucfirst($notification->type) }}</span>
                                                    <span class="badge badge-outline-secondary">{{ ucfirst($notification->category) }}</span>
                                                </div>
                                                
                                                <div class="notification-actions">
                                                    @if($notification->action_url)
                                                        <a href="{{ route('notifications.show', $notification) }}" 
                                                           class="btn btn-sm btn-primary me-2">
                                                            {{ $notification->action_text ?? 'View' }}
                                                        </a>
                                                    @endif
                                                    
                                                    @if(!$notification->isRead())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success me-1" 
                                                                onclick="markAsRead({{ $notification->id }})"
                                                                title="Mark as read">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-warning me-1" 
                                                                onclick="markAsUnread({{ $notification->id }})"
                                                                title="Mark as unread">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteNotification({{ $notification->id }})"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->appends(request()->query())->links() }}
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-bell-slash text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No notifications found</h5>
                        <p class="text-muted">You don't have any notifications matching the current filters.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.notification-item {
    padding: 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.notification-item.unread {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-icon i {
    font-size: 1.2rem;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(0, 123, 255, 0.1);
}

.notification-title {
    font-weight: 600;
    color: #333;
}

.notification-message {
    color: #666;
    line-height: 1.4;
}

.badge-info { background-color: #17a2b8; }
.badge-success { background-color: #28a745; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-danger { background-color: #dc3545; }
.badge-outline-secondary { 
    background-color: transparent; 
    border: 1px solid #6c757d; 
    color: #6c757d; 
}
</style>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAsUnread(notificationId) {
    fetch(`/notifications/${notificationId}/unread`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function clearReadNotifications() {
    if (confirm('Are you sure you want to clear all read notifications?')) {
        fetch('/notifications/clear/read', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endsection
