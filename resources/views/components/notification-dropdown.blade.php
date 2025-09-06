<!-- Notification Dropdown -->
<div class="dropdown">
    <button class="btn btn-link text-decoration-none position-relative p-2" 
            type="button" 
            id="notificationDropdown" 
            data-bs-toggle="dropdown" 
            aria-expanded="false"
            onclick="loadNotifications()">
        <i class="fas fa-bell fs-5 text-muted"></i>
        <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
            0
        </span>
    </button>
    
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" 
         aria-labelledby="notificationDropdown" 
         style="width: 350px; max-height: 400px; overflow-y: auto;">
        
        <!-- Header -->
        <div class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <h6 class="mb-0">Notifications</h6>
            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="markAllAsRead()">
                Mark all read
            </button>
        </div>
        
        <!-- Loading State -->
        <div id="notification-loading" class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        
        <!-- Notifications List -->
        <div id="notification-list" style="display: none;">
            <!-- Notifications will be loaded here via AJAX -->
        </div>
        
        <!-- Empty State -->
        <div id="notification-empty" class="text-center py-4" style="display: none;">
            <i class="fas fa-bell-slash text-muted" style="font-size: 2rem;"></i>
            <p class="text-muted mt-2 mb-0">No notifications</p>
        </div>
        
        <!-- Footer -->
        <div class="dropdown-divider"></div>
        <div class="dropdown-item-text text-center">
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-primary">
                View All Notifications
            </a>
        </div>
    </div>
</div>

<style>
.notification-dropdown {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border: 1px solid #dee2e6;
}

.notification-item-dropdown {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.notification-item-dropdown:hover {
    background-color: #f8f9fa;
}

.notification-item-dropdown.unread {
    background-color: #e3f2fd;
    border-left: 3px solid #2196f3;
}

.notification-title-small {
    font-size: 0.875rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.notification-message-small {
    font-size: 0.8rem;
    color: #666;
    line-height: 1.3;
    margin-bottom: 0.25rem;
}

.notification-time-small {
    font-size: 0.75rem;
    color: #999;
}

.notification-icon-small {
    font-size: 1rem;
    width: 1.5rem;
    height: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(0, 123, 255, 0.1);
}
</style>

<script>
let notificationsLoaded = false;

function loadNotifications() {
    if (notificationsLoaded) return;
    
    fetch('/notifications/recent/json', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('notification-loading').style.display = 'none';
        
        const notificationList = document.getElementById('notification-list');
        const notificationEmpty = document.getElementById('notification-empty');
        const notificationBadge = document.getElementById('notification-badge');
        
        if (data.notifications && data.notifications.length > 0) {
            let notificationHtml = '';
            
            data.notifications.forEach(notification => {
                const unreadClass = notification.read_at ? '' : 'unread';
                const iconClass = getIconClass(notification.type);
                
                notificationHtml += `
                    <div class="notification-item-dropdown ${unreadClass}" onclick="viewNotification(${notification.id})">
                        <div class="d-flex align-items-start">
                            <div class="notification-icon-small me-2">
                                <i class="${iconClass}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="notification-title-small">${notification.title}</div>
                                <div class="notification-message-small">${truncateText(notification.message, 80)}</div>
                                <div class="notification-time-small">${formatTimeAgo(notification.created_at)}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            notificationList.innerHTML = notificationHtml;
            notificationList.style.display = 'block';
            notificationEmpty.style.display = 'none';
        } else {
            notificationList.style.display = 'none';
            notificationEmpty.style.display = 'block';
        }
        
        // Update badge
        if (data.unread_count > 0) {
            notificationBadge.textContent = data.unread_count;
            notificationBadge.style.display = 'block';
        } else {
            notificationBadge.style.display = 'none';
        }
        
        notificationsLoaded = true;
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        document.getElementById('notification-loading').style.display = 'none';
        document.getElementById('notification-empty').style.display = 'block';
    });
}

function getIconClass(type) {
    switch(type) {
        case 'success': return 'fas fa-check-circle text-success';
        case 'warning': return 'fas fa-exclamation-triangle text-warning';
        case 'error': return 'fas fa-times-circle text-danger';
        default: return 'fas fa-info-circle text-info';
    }
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    return `${Math.floor(diffInSeconds / 86400)}d ago`;
}

function viewNotification(notificationId) {
    window.location.href = `/notifications/${notificationId}`;
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
            // Reload notifications
            notificationsLoaded = false;
            loadNotifications();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Update notification count periodically
function updateNotificationCount() {
    fetch('/notifications/count/json', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const notificationBadge = document.getElementById('notification-badge');
        if (data.unread_count > 0) {
            notificationBadge.textContent = data.unread_count;
            notificationBadge.style.display = 'block';
        } else {
            notificationBadge.style.display = 'none';
        }
    })
    .catch(error => console.error('Error updating notification count:', error));
}

// Update count every 30 seconds
setInterval(updateNotificationCount, 30000);

// Initial load of notification count
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationCount();
});
</script>
