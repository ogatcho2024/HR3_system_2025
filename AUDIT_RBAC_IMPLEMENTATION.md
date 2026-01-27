# Audit Tracking RBAC + Self-Logging Fix Implementation

## Overview
This document describes the implementation of Role-Based Access Control (RBAC) for the Audit Tracking module and the fix for the self-logging issue where deleting audit logs created new log entries.

## Implementation Summary

### Goal A: Access Control (RBAC)
Enforced server-side access control based on `account_type`:
- **Super admin**: Full access (view, export, delete audit logs)
- **Admin**: Read-only access (view, export audit logs)
- **Staff & Employee**: No access (403 Unauthorized)

### Goal B: Fix Self-Logging Issue
Prevented audit log deletion from creating new audit log entries by:
- Adding a request-scoped skip flag in AuditLogService
- Auto-detecting operations on the `audit_logs` table
- Explicitly disabling audit logging when deleting audit logs

---

## Files Created

### 1. Middleware: `app/Http/Middleware/EnsureAccountType.php`
**Purpose**: Enforce access control based on account type.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountType
{
    public function handle(Request $request, Closure $next, string ...$allowedTypes): Response
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized access. Authentication required.');
        }

        $user = Auth::user();

        if (!in_array($user->account_type, $allowedTypes)) {
            abort(403, 'Unauthorized access. Insufficient privileges.');
        }

        return $next($request);
    }
}
```

### 2. Policy: `app/Policies/AuditLogPolicy.php`
**Purpose**: Fine-grained authorization for audit log operations.

```php
<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->account_type, [
            User::ACCOUNT_TYPE_SUPER_ADMIN,
            User::ACCOUNT_TYPE_ADMIN
        ]);
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return in_array($user->account_type, [
            User::ACCOUNT_TYPE_SUPER_ADMIN,
            User::ACCOUNT_TYPE_ADMIN
        ]);
    }

    public function export(User $user): bool
    {
        return in_array($user->account_type, [
            User::ACCOUNT_TYPE_SUPER_ADMIN,
            User::ACCOUNT_TYPE_ADMIN
        ]);
    }

    public function delete(User $user, AuditLog $auditLog): bool
    {
        return $user->account_type === User::ACCOUNT_TYPE_SUPER_ADMIN;
    }

    public function deleteAny(User $user): bool
    {
        return $user->account_type === User::ACCOUNT_TYPE_SUPER_ADMIN;
    }
}
```

### 3. Provider: `app/Providers/AuthServiceProvider.php`
**Purpose**: Register the AuditLog policy.

```php
<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Policies\AuditLogPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AuditLog::class => AuditLogPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
```

---

## Files Modified

### 1. `bootstrap/app.php`
**Changes**:
- Registered `EnsureAccountType` middleware as `account.type`
- Registered `AuthServiceProvider`

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'login.throttle' => \App\Http\Middleware\LoginThrottleMiddleware::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
            'simple.api.auth' => \App\Http\Middleware\SimpleApiAuth::class,
            '2fa.verified' => \App\Http\Middleware\Ensure2FAVerified::class,
            'account.type' => \App\Http\Middleware\EnsureAccountType::class, // NEW
        ]);
        
        $middleware->group('api', [
            \App\Http\Middleware\CorsMiddleware::class,
        ]);
    })
    // ...
```

### 2. `routes/web.php`
**Changes**: Split audit log routes into view-only (Super admin + Admin) and mutating (Super admin only).

```php
// Audit Log Routes (Protected by auth, 2FA, and account type RBAC)
Route::middleware(['auth', \App\Http\Middleware\Ensure2FAVerified::class])->group(function () {
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        // View-only routes: Super Admin and Admin can access
        Route::middleware(['account.type:Super admin,Admin'])->group(function () {
            Route::get('/', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\AuditLogController::class, 'show'])->name('show');
            Route::get('/export/csv', [\App\Http\Controllers\AuditLogController::class, 'export'])->name('export');
            Route::get('/user/{userId}/activity', [\App\Http\Controllers\AuditLogController::class, 'userActivity'])->name('user-activity');
            Route::get('/security/report', [\App\Http\Controllers\AuditLogController::class, 'securityReport'])->name('security-report');
        });
        
        // Mutating routes: Super Admin only
        Route::middleware(['account.type:Super admin'])->group(function () {
            Route::delete('/{id}', [\App\Http\Controllers\AuditLogController::class, 'destroy'])->name('destroy');
        });
    });
});
```

### 3. `app/Services/AuditLogService.php`
**Changes**: Added skip flag to prevent self-logging.

```php
class AuditLogService
{
    protected static bool $skipLogging = false;

    public static function skipLogging(): void
    {
        self::$skipLogging = true;
    }

    public static function enableLogging(): void
    {
        self::$skipLogging = false;
    }

    public static function isLoggingSkipped(): bool
    {
        return self::$skipLogging;
    }

    public function log(
        string $actionType,
        string $description,
        ?int $userId = null,
        ?string $affectedTable = null,
        ?int $affectedRecordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        int $loginAttemptCount = 0
    ): ?AuditLog {
        // Skip logging if flag is set or if this is an audit log operation
        if (self::$skipLogging || $affectedTable === 'audit_logs') {
            return null;
        }

        return AuditLog::create([
            // ... rest of implementation
        ]);
    }
}
```

### 4. `app/Http/Controllers/AuditLogController.php`
**Changes**:
- Removed manual middleware checks in constructor
- Added policy authorization checks in each method
- Explicitly disabled audit logging for delete operations

```php
class AuditLogController extends Controller
{
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);
        // ... rest of implementation
    }

    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        $this->authorize('view', $log);
        // ... rest of implementation
    }

    public function export(Request $request)
    {
        $this->authorize('export', AuditLog::class);
        // ... rest of implementation
    }

    public function destroy($id)
    {
        $log = AuditLog::findOrFail($id);
        $this->authorize('delete', $log);
        
        // Disable audit logging to prevent self-logging
        AuditLogService::skipLogging();
        $log->delete();
        AuditLogService::enableLogging();

        return back()->with('success', 'Audit log entry deleted successfully.');
    }

    public function userActivity($userId)
    {
        $this->authorize('viewAny', AuditLog::class);
        // ... rest of implementation
    }

    public function securityReport(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);
        // ... rest of implementation
    }
}
```

### 5. `app/Models/AuditLog.php`
**Changes**: Added event listeners to document self-logging prevention (no actual logging needed in these events).

```php
protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (!in_array($model->action_type, self::ACTION_TYPES)) {
            throw new \InvalidArgumentException("Invalid action type: {$model->action_type}");
        }
    });

    static::updating(function ($model) {
        if (!$model->canBeModified()) {
            return false;
        }
    });

    // Prevent self-logging: Do not log operations on audit_logs table itself
    static::created(function ($model) {
        // No need to log the creation of an audit log
    });

    static::deleted(function ($model) {
        // No need to log the deletion of an audit log
    });
}
```

---

## UI Changes (Blade Templates)

### Sample Blade Code for Hiding Delete Button

The existing `resources/views/audit-logs/index.blade.php` already correctly hides the delete button:

```blade
@if(Auth::user()->isSuperAdmin())
<td class="px-4 py-2 border text-sm">
    <form action="{{ route('audit-logs.destroy', $log->id) }}" method="POST" 
          onsubmit="return confirm('Delete this log?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
            Delete
        </button>
    </form>
</td>
@endif
```

**Additional examples for other views:**

```blade
{{-- Check if user is Super Admin --}}
@if(Auth::user()->isSuperAdmin())
    <button class="btn btn-danger">Delete</button>
@endif

{{-- Check if user is Admin or Super Admin --}}
@if(Auth::user()->isAdmin())
    <a href="{{ route('audit-logs.index') }}">View Audit Logs</a>
@endif

{{-- Check account type directly --}}
@if(Auth::user()->account_type === 'Super admin')
    <button>Super Admin Only Action</button>
@endif

{{-- Multiple conditions --}}
@if(in_array(Auth::user()->account_type, ['Super admin', 'Admin']))
    <a href="{{ route('audit-logs.export') }}">Export Logs</a>
@endif
```

---

## Testing the Implementation

### Test Access Control

1. **Test Super Admin Access**:
   - Login as Super admin
   - Navigate to `/audit-logs`
   - Verify you can view, export, and delete audit logs

2. **Test Admin Access (Read-Only)**:
   - Login as Admin
   - Navigate to `/audit-logs`
   - Verify you can view and export audit logs
   - Verify delete button is hidden
   - Try accessing DELETE route directly → Should return 403

3. **Test Staff/Employee Access**:
   - Login as Staff or Employee
   - Try accessing `/audit-logs` → Should return 403
   - Verify no audit log links visible in UI

### Test Self-Logging Prevention

1. **Test Delete Operation**:
   - Login as Super admin
   - Delete an audit log entry
   - Check `audit_logs` table
   - Verify NO new entry was created for the deletion

2. **Test Other Operations Still Log**:
   - Perform other operations (create user, update data, etc.)
   - Verify those operations ARE still logged in `audit_logs` table

---

## Security Considerations

### Defense in Depth
This implementation uses multiple layers of security:
1. **Route Middleware**: First line of defense at routing level
2. **Policy Authorization**: Fine-grained checks in controller methods
3. **UI Hiding**: Prevents accidental exposure (but not relied upon for security)

### 403 vs Redirect
- Current implementation returns **403 Forbidden** for unauthorized access
- To redirect with a message instead:

```php
// In EnsureAccountType middleware
if (!in_array($user->account_type, $allowedTypes)) {
    return redirect()->route('dashboard')
        ->with('error', 'You do not have permission to access this resource.');
}
```

---

## Additional Notes

### How to Use the Skip Flag in Other Controllers

If you need to skip audit logging for specific operations elsewhere:

```php
use App\Services\AuditLogService;

// Disable logging
AuditLogService::skipLogging();

// Perform operations without logging
// ...

// Re-enable logging
AuditLogService::enableLogging();
```

### Auto-Skip for audit_logs Table

The service automatically skips logging for any operation where `$affectedTable === 'audit_logs'`, so you don't need to manually call `skipLogging()` if you're using the service methods with the correct table name.

---

## Summary

✅ **RBAC Implemented**: Server-side enforcement via middleware + policies
✅ **Self-Logging Fixed**: Audit log deletions no longer create new logs
✅ **Clean Architecture**: Follows Laravel best practices (middleware, policies, gates)
✅ **UI Updated**: Buttons hidden based on account_type (already in place)
✅ **Secure**: Multiple layers of defense (routes, controllers, policies)

All requirements have been successfully implemented with clean, maintainable Laravel code.
