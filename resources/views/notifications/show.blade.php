@extends('layouts.app')

@section('title', 'Notification Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="page-title mb-0">
                    <i class="fas fa-bell me-2"></i>Notification Details
                </h2>
                <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Notifications
                </a>
            </div>
        </div>
    </div>

    <!-- Notification Details -->
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="{{ $notification->icon }} me-2"></i>
                            <h5 class="mb-0">{{ $notification->title }}</h5>
                            @if($notification->is_important)
                                <i class="fas fa-star text-warning ms-2" title="Important"></i>
                            @endif
                        </div>
                        <div class="notification-badges">
                            <span class="badge {{ $notification->badge_class }} me-2">{{ ucfirst($notification->type) }}</span>
                            <span class="badge badge-outline-secondary">{{ ucfirst($notification->category) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Message -->
                    <div class="notification-message mb-4">
                        <p class="lead">{{ $notification->message }}</p>
                    </div>

                    <!-- Additional Data -->
                    @if($notification->data && count($notification->data) > 0)
                        <div class="notification-data mb-4">
                            <h6 class="text-muted mb-3">Additional Information</h6>
                            <div class="bg-light p-3 rounded">
                                @foreach($notification->data as $key => $value)
                                    <div class="row mb-2">
                                        <div class="col-md-4">
                                            <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                                        </div>
                                        <div class="col-md-8">
                                            @if(is_array($value))
                                                {{ json_encode($value) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="notification-metadata">
                        <h6 class="text-muted mb-3">Notification Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Created:</strong> {{ $notification->created_at->format('M d, Y \a\t g:i A') }}</p>
                                <p><strong>Status:</strong> 
                                    @if($notification->isRead())
                                        <span class="text-success">Read on {{ $notification->read_at->format('M d, Y \a\t g:i A') }}</span>
                                    @else
                                        <span class="text-warning">Unread</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Type:</strong> {{ ucfirst($notification->type) }}</p>
                                <p><strong>Category:</strong> {{ ucfirst($notification->category) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="notification-actions">
                            @if($notification->action_url)
                                <a href="{{ $notification->action_url }}" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    {{ $notification->action_text ?? 'Take Action' }}
                                </a>
                            @endif
                        </div>
                        
                        <div class="notification-controls">
                            @if(!$notification->isRead())
                                <button type="button" 
                                        class="btn btn-outline-success me-2" 
                                        onclick="markAsRead({{ $notification->id }})">
                                    <i class="fas fa-check me-1"></i>Mark as Read
                                </button>
                            @else
                                <button type="button" 
                                        class="btn btn-outline-warning me-2" 
                                        onclick="markAsUnread({{ $notification->id }})">
                                    <i class="fas fa-undo me-1"></i>Mark as Unread
                                </button>
                            @endif
                            
                            <button type="button" 
                                    class="btn btn-outline-danger" 
                                    onclick="deleteNotification({{ $notification->id }})">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-info { background-color: #17a2b8; }
.badge-success { background-color: #28a745; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-danger { background-color: #dc3545; }
.badge-outline-secondary { 
    background-color: transparent; 
    border: 1px solid #6c757d; 
    color: #6c757d; 
}

.notification-message {
    font-size: 1.1rem;
    line-height: 1.6;
}

.notification-data {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
}

.notification-metadata {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
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
                window.location.href = '{{ route("notifications.index") }}';
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endsection
