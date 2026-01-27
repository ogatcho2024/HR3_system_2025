<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    /**
     * Display audit logs with filtering.
     */
    public function index(Request $request)
    {
        // Authorization check
        $this->authorize('viewAny', AuditLog::class);
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Filter by category (overrides individual action_type)
        if ($request->filled('category')) {
            switch ($request->category) {
                case 'failed_logins':
                    $query->failedLoginAttempts();
                    break;
                case 'data_changes':
                    $query->dataActions();
                    break;
                case 'account_changes':
                    $query->accountActions();
                    break;
                case 'authentication':
                    $query->authActions();
                    break;
            }
        }
        // Filter by action type
        elseif ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by IP address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        // Filter by affected table
        if ($request->filled('affected_table')) {
            $query->where('affected_table', $request->affected_table);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        // Search in description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Paginate results
        $logs = $query->paginate(50)->appends($request->except('page'));

        // Get filter options
        $actionTypes = AuditLog::select('action_type')->distinct()->pluck('action_type');
        $users = User::select('id', 'name', 'lastname')->get();
        $affectedTables = AuditLog::select('affected_table')
            ->whereNotNull('affected_table')
            ->distinct()
            ->pluck('affected_table');

        // Get statistics
        $stats = $this->getStatistics($request);

        // Log this view action
        $this->auditLog->logView('audit_logs', 0, 'Viewed audit log list with filters');

        return view('audit-logs.index', compact('logs', 'actionTypes', 'users', 'affectedTables', 'stats'));
    }

    /**
     * Show details of a specific audit log entry.
     */
    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        
        // Authorization check
        $this->authorize('view', $log);

        // Log this view action
        $this->auditLog->logView('audit_logs', $id, "Viewed audit log entry #{$id}");

        return view('audit-logs.show', compact('log'));
    }

    /**
     * Export audit logs.
     */
    public function export(Request $request)
    {
        // Authorization check
        $this->authorize('export', AuditLog::class);
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $logs = $query->get();

        // Log export action
        $this->auditLog->logExport("Exported {$logs->count()} audit log entries", 'audit_logs');

        // Generate CSV
        $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Date/Time',
                'User',
                'Action Type',
                'Description',
                'IP Address',
                'Affected Table',
                'Affected Record ID',
                'Login Attempt Count'
            ]);

            // Add data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user_name,
                    $log->action_label,
                    $log->description,
                    $log->ip_address,
                    $log->affected_table ?? 'N/A',
                    $log->affected_record_id ?? 'N/A',
                    $log->login_attempt_count
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete an audit log entry (Super Admin only).
     */
    public function destroy($id)
    {
        $log = AuditLog::findOrFail($id);
        
        // Authorization check
        $this->authorize('delete', $log);
        
        // Disable audit logging for this operation to prevent self-logging
        AuditLogService::skipLogging();
        
        $log->delete();
        
        // Re-enable audit logging for subsequent operations
        AuditLogService::enableLogging();

        return back()->with('success', 'Audit log entry deleted successfully.');
    }

    /**
     * Get statistics for the current filter.
     */
    private function getStatistics(Request $request)
    {
        $query = AuditLog::query();

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        return [
            'total_entries' => (clone $query)->count(),
            'unique_users' => (clone $query)->distinct('user_id')->count('user_id'),
            'failed_logins' => (clone $query)->where('action_type', 'failed_login')->count(),
            'successful_logins' => (clone $query)->where('action_type', 'login')->count(),
            'data_modifications' => (clone $query)->whereIn('action_type', ['create', 'update', 'delete'])->count(),
            'account_changes' => (clone $query)->whereIn('action_type', ['account_created', 'account_updated', 'account_deleted', 'password_changed', 'email_changed', 'role_changed'])->count(),
        ];
    }

    /**
     * Get user activity timeline.
     */
    public function userActivity($userId)
    {
        // Authorization check
        $this->authorize('viewAny', AuditLog::class);
        
        $user = User::findOrFail($userId);
        $logs = AuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Log this view action
        $this->auditLog->logView('users', $userId, "Viewed activity timeline for user {$user->name} {$user->lastname}");

        return view('audit-logs.user-activity', compact('user', 'logs'));
    }

    /**
     * Get system security report.
     */
    public function securityReport(Request $request)
    {
        // Authorization check
        $this->authorize('viewAny', AuditLog::class);
        $days = $request->input('days', 7);
        $startDate = now()->subDays($days);

        $failedLogins = AuditLog::where('action_type', 'failed_login')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('ip_address');

        $suspiciousIps = $failedLogins->filter(function($logs) {
            return $logs->count() >= 5; // 5 or more failed attempts
        });

        $otpFailures = AuditLog::where('action_type', 'otp_failed')
            ->where('created_at', '>=', $startDate)
            ->count();

        // Log security report view
        $this->auditLog->logOther("Viewed security report for last {$days} days");

        return view('audit-logs.security-report', compact('failedLogins', 'suspiciousIps', 'otpFailures', 'days'));
    }
}
