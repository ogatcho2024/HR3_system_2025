<?php

use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create notification', function () {
    $user = User::factory()->create();
    $service = app(NotificationService::class);
    
    $notification = $service->create(
        $user,
        'Test Notification',
        'This is a test message',
        'info',
        'general'
    );

    expect($notification)->toBeInstanceOf(Notification::class);
    expect($notification->title)->toBe('Test Notification');
    expect($notification->message)->toBe('This is a test message');
    expect($notification->type)->toBe('info');
    expect($notification->category)->toBe('general');
    expect($notification->isRead())->toBeFalse();
});

test('can mark notification as read', function () {
    $user = User::factory()->create();
    $service = app(NotificationService::class);
    
    $notification = $service->create(
        $user,
        'Test Notification',
        'This is a test message'
    );

    expect($notification->isRead())->toBeFalse();
    
    $notification->markAsRead();
    $notification->refresh();
    
    expect($notification->isRead())->toBeTrue();
    expect($notification->read_at)->not->toBeNull();
});

test('notification routes are accessible', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);

    // Test notification index
    $response = $this->get(route('notifications.index'));
    $response->assertStatus(200);

    // Test notification count API
    $response = $this->get(route('notifications.count'));
    $response->assertStatus(200);
    $response->assertJsonStructure(['unread_count', 'total_count']);

    // Test recent notifications API
    $response = $this->get(route('notifications.recent'));
    $response->assertStatus(200);
    $response->assertJsonStructure(['notifications', 'unread_count']);
});

test('can create leave request notification', function () {
    $user = User::factory()->create();
    $service = app(NotificationService::class);
    
    $notification = $service->createLeaveRequestNotification(
        $user,
        'approved',
        ['leave_type' => 'annual', 'days_requested' => 5]
    );

    expect($notification->title)->toBe('Leave Request Approved');
    expect($notification->category)->toBe('leave');
    expect($notification->type)->toBe('success');
    expect($notification->is_important)->toBeTrue();
});

test('notification scopes work correctly', function () {
    $user = User::factory()->create();
    $service = app(NotificationService::class);
    
    // Create read and unread notifications
    $readNotification = $service->create(
        $user,
        'Read Notification',
        'This is read'
    );
    $readNotification->markAsRead();

    $unreadNotification = $service->create(
        $user,
        'Unread Notification',
        'This is unread'
    );

    // Test scopes
    $unreadCount = $user->notifications()->unread()->count();
    $readCount = $user->notifications()->read()->count();

    expect($unreadCount)->toBe(1);
    expect($readCount)->toBe(1);
});
