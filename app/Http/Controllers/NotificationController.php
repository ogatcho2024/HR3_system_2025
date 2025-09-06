<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request): View
    {
        $query = Auth::user()->notifications()->latest();

        // Filter by type if specified
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category if specified
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->unread();
            } elseif ($request->status === 'read') {
                $query->read();
            }
        }

        $notifications = $query->paginate(20);
        $unreadCount = Auth::user()->notifications()->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get notifications for dropdown (AJAX)
     */
    public function getRecent(): JsonResponse
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->take(10)
            ->get();

        $unreadCount = Auth::user()->notifications()->unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Ensure user can only mark their own notifications
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'unread_count' => Auth::user()->notifications()->unread()->count(),
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Notification $notification): JsonResponse
    {
        // Ensure user can only mark their own notifications
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsUnread();

        return response()->json([
            'message' => 'Notification marked as unread',
            'unread_count' => Auth::user()->notifications()->unread()->count(),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read',
            'unread_count' => 0,
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Ensure user can only delete their own notifications
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully',
            'unread_count' => Auth::user()->notifications()->unread()->count(),
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function clearRead(): JsonResponse
    {
        $deletedCount = Auth::user()->notifications()
            ->read()
            ->delete();

        return response()->json([
            'message' => "$deletedCount read notifications cleared",
            'unread_count' => Auth::user()->notifications()->unread()->count(),
        ]);
    }

    /**
     * Show single notification
     */
    public function show(Notification $notification): View|RedirectResponse
    {
        // Ensure user can only view their own notifications
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        // Mark as read when viewed
        $notification->markAsRead();

        // If there's an action URL, redirect there
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return view('notifications.show', compact('notification'));
    }

    /**
     * Get notification count
     */
    public function getCount(): JsonResponse
    {
        $unreadCount = Auth::user()->notifications()->unread()->count();
        $totalCount = Auth::user()->notifications()->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'total_count' => $totalCount,
        ]);
    }
}
