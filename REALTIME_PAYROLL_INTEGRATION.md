# Real-Time Payroll Integration Implementation Guide

## Overview
This system automatically sends timesheets to the external payroll system (HR4) **immediately** when they are approved, with no manual triggers or cron delays.

---

## 🗄️ Step 1: Run the Migration

```bash
php artisan migrate
```

This adds the following columns to the `timesheets` table:
- `sent_to_payroll` (boolean) - Tracks if sent
- `payroll_sent_at` (datetime) - When it was sent
- `payroll_send_attempts` (integer) - Number of attempts
- `payroll_last_error` (text) - Last error message

---

## 🔧 Step 2: Update Timesheet Model

Add the new fields to the `$fillable` array in `app/Models/Timesheet.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    protected $fillable = [
        // ... existing fields ...
        'status',
        'sent_to_payroll',
        'payroll_sent_at',
        'payroll_send_attempts',
        'payroll_last_error',
    ];

    protected $casts = [
        // ... existing casts ...
        'sent_to_payroll' => 'boolean',
        'payroll_sent_at' => 'datetime',
        'payroll_send_attempts' => 'integer',
    ];
}
```

---

## 🚀 Step 3: Dispatch the Job on Approval

### Option A: In Your Timesheet Controller

**File:** `app/Http/Controllers/TimesheetController.php` (or wherever you approve timesheets)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Jobs\SendTimesheetToPayrollJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TimesheetController extends Controller
{
    /**
     * Approve a timesheet and automatically send to payroll.
     */
    public function approve(Request $request, $timesheetId)
    {
        $timesheet = Timesheet::findOrFail($timesheetId);
        
        // Get the OLD status before updating
        $oldStatus = $timesheet->status;
        
        // Update status to approved
        $timesheet->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        
        Log::info('[Timesheet] Timesheet approved', [
            'timesheet_id' => $timesheet->id,
            'old_status' => $oldStatus,
            'new_status' => 'approved',
            'approved_by' => auth()->id(),
        ]);
        
        // ============================================================
        // REAL-TIME PAYROLL SYNC: Dispatch job immediately
        // ============================================================
        if ($oldStatus !== 'approved' && $timesheet->status === 'approved') {
            // Only dispatch if status CHANGED to approved (not already approved)
            if (!$timesheet->sent_to_payroll) {
                Log::info('[Timesheet] Dispatching payroll sync job', [
                    'timesheet_id' => $timesheet->id,
                ]);
                
                // Dispatch the job to the queue (non-blocking)
                SendTimesheetToPayrollJob::dispatch($timesheet->id);
                
                // Alternative: Dispatch with delay
                // SendTimesheetToPayrollJob::dispatch($timesheet->id)->delay(now()->addSeconds(5));
                
                // Alternative: Dispatch to specific queue
                // SendTimesheetToPayrollJob::dispatch($timesheet->id)->onQueue('payroll-sync');
            } else {
                Log::info('[Timesheet] Skipping payroll sync - already sent', [
                    'timesheet_id' => $timesheet->id,
                    'payroll_sent_at' => $timesheet->payroll_sent_at,
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Timesheet approved successfully. Payroll sync initiated.',
            'timesheet' => $timesheet,
        ]);
    }
    
    /**
     * Bulk approve timesheets.
     */
    public function bulkApprove(Request $request)
    {
        $timesheetIds = $request->input('timesheet_ids', []);
        
        $approvedCount = 0;
        $syncedCount = 0;
        
        foreach ($timesheetIds as $timesheetId) {
            $timesheet = Timesheet::find($timesheetId);
            
            if (!$timesheet) {
                continue;
            }
            
            $oldStatus = $timesheet->status;
            
            $timesheet->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            
            $approvedCount++;
            
            // Dispatch payroll sync job if status changed to approved
            if ($oldStatus !== 'approved' && !$timesheet->sent_to_payroll) {
                SendTimesheetToPayrollJob::dispatch($timesheet->id);
                $syncedCount++;
                
                Log::info('[Timesheet] Bulk approve - dispatched payroll sync', [
                    'timesheet_id' => $timesheet->id,
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Approved {$approvedCount} timesheets. {$syncedCount} queued for payroll sync.",
            'approved_count' => $approvedCount,
            'synced_count' => $syncedCount,
        ]);
    }
}
```

---

### Option B: Using Model Observer (Automatic)

**File:** `app/Observers/TimesheetObserver.php` (create this file)

```php
<?php

namespace App\Observers;

use App\Models\Timesheet;
use App\Jobs\SendTimesheetToPayrollJob;
use Illuminate\Support\Facades\Log;

class TimesheetObserver
{
    /**
     * Handle the Timesheet "updated" event.
     *
     * @param Timesheet $timesheet
     * @return void
     */
    public function updated(Timesheet $timesheet): void
    {
        // Check if status changed to "approved"
        if ($timesheet->isDirty('status') && $timesheet->status === 'approved') {
            $oldStatus = $timesheet->getOriginal('status');
            
            Log::info('[TimesheetObserver] Status changed to approved', [
                'timesheet_id' => $timesheet->id,
                'old_status' => $oldStatus,
                'new_status' => $timesheet->status,
                'sent_to_payroll' => $timesheet->sent_to_payroll,
            ]);
            
            // Only dispatch if not already sent
            if (!$timesheet->sent_to_payroll) {
                Log::info('[TimesheetObserver] Dispatching payroll sync job', [
                    'timesheet_id' => $timesheet->id,
                ]);
                
                SendTimesheetToPayrollJob::dispatch($timesheet->id);
            } else {
                Log::info('[TimesheetObserver] Skipping sync - already sent to payroll', [
                    'timesheet_id' => $timesheet->id,
                    'payroll_sent_at' => $timesheet->payroll_sent_at,
                ]);
            }
        }
    }
}
```

**Then register the observer in:** `app/Providers/EventServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Models\Timesheet;
use App\Observers\TimesheetObserver;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register the Timesheet observer
        Timesheet::observe(TimesheetObserver::class);
    }
}
```

---

## ⚙️ Step 4: Configure Queue Worker

### Option A: Database Queue (Easiest for XAMPP)

1. **Update `.env`:**
```env
QUEUE_CONNECTION=database
```

2. **Create jobs table:**
```bash
php artisan queue:table
php artisan migrate
```

3. **Run queue worker:**
```bash
php artisan queue:work --queue=default --tries=5 --timeout=90
```

### Option B: Redis Queue (Recommended for Production)

1. **Install Redis and predis:**
```bash
composer require predis/predis
```

2. **Update `.env`:**
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

3. **Run queue worker:**
```bash
php artisan queue:work redis --queue=default --tries=5 --timeout=90
```

### Keep Queue Worker Running (Production)

Use Supervisor (Linux) or Windows Task Scheduler:

**supervisor.conf:**
```ini
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php C:\xampp\htdocs\dashboard\HumanResources3\artisan queue:work --queue=default --tries=5 --timeout=90 --sleep=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=C:\xampp\htdocs\dashboard\HumanResources3\storage\logs\queue-worker.log
```

---

## 🧪 Step 5: Testing

### Test 1: Manual Approval
```php
// In tinker or controller
php artisan tinker

$timesheet = \App\Models\Timesheet::find(1);
$timesheet->update(['status' => 'approved']);

// Check logs
tail -f storage/logs/laravel.log
```

### Test 2: Check Job Queue
```bash
# List pending jobs
php artisan queue:monitor

# Process jobs manually
php artisan queue:work --once
```

### Test 3: Check Database
```sql
-- Check timesheet status
SELECT id, status, sent_to_payroll, payroll_sent_at, payroll_send_attempts, payroll_last_error
FROM timesheets
WHERE id = 1;

-- Check job queue
SELECT * FROM jobs ORDER BY id DESC LIMIT 10;

-- Check failed jobs
SELECT * FROM failed_jobs ORDER BY id DESC LIMIT 10;
```

---

## 📊 Monitoring & Debugging

### View Queue Status
```bash
# Monitor queue in real-time
php artisan queue:monitor

# List failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry [job-id]

# Retry all failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### Check Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log | grep PayrollSync

# Filter for specific timesheet
tail -f storage/logs/laravel.log | grep "timesheet_id.*123"
```

### Database Queries
```sql
-- Find unsent approved timesheets
SELECT * FROM timesheets 
WHERE status = 'approved' 
AND sent_to_payroll = 0;

-- Find timesheets with errors
SELECT id, status, payroll_send_attempts, payroll_last_error
FROM timesheets
WHERE payroll_last_error IS NOT NULL
ORDER BY updated_at DESC;

-- Success rate
SELECT 
    COUNT(*) as total_approved,
    SUM(sent_to_payroll) as sent_count,
    SUM(CASE WHEN sent_to_payroll = 0 THEN 1 ELSE 0 END) as unsent_count
FROM timesheets
WHERE status = 'approved';
```

---

## 🔒 Safety Features

### 1. **Idempotency**
- Job checks `sent_to_payroll` flag before sending
- Prevents duplicate sends even if job runs multiple times

### 2. **Status Validation**
- Only sends timesheets with `status = 'approved'`
- Skips if status changed after approval

### 3. **Retry Logic**
- 5 attempts with exponential backoff: 30s, 1m, 2m, 5m, 10m
- Each attempt tracked in `payroll_send_attempts`

### 4. **Error Tracking**
- All errors stored in `payroll_last_error` as JSON
- Includes timestamp, attempt number, and error details

### 5. **Failure Handling**
- After 5 failed attempts, job is moved to `failed_jobs` table
- Can be retried manually or automatically

---

## 🚨 Troubleshooting

### Issue: Jobs not processing
**Solution:**
```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Start queue worker
php artisan queue:work
```

### Issue: "Class 'SendTimesheetToPayrollJob' not found"
**Solution:**
```bash
composer dump-autoload
```

### Issue: HR4 database connection error
**Solution:**
- The error is on HR4 side (receiver)
- Check HR4's `.env` file has correct `DB_PASSWORD`
- Your HR3 system is working correctly

### Issue: Jobs stuck in queue
**Solution:**
```bash
# Clear stuck jobs
php artisan queue:flush

# Restart queue worker
php artisan queue:restart
```

---

## 📈 Performance Considerations

### Queue Optimization
```env
# Process multiple jobs in parallel
QUEUE_WORKERS=4

# Limit memory usage
QUEUE_MEMORY_LIMIT=512

# Auto-restart after 1 hour
QUEUE_MAX_TIME=3600
```

### Batch Processing
If you have many timesheets to approve at once, the system will:
1. Accept all approvals instantly (non-blocking)
2. Queue all payroll sync jobs
3. Process them in background with retry logic

---

## 📝 Summary

✅ **Immediate dispatch** - Job dispatched the moment status changes to "approved"  
✅ **Non-blocking** - UI responds immediately, sync happens in background  
✅ **Idempotent** - Safe to retry, won't send duplicates  
✅ **Resilient** - 5 retry attempts with exponential backoff  
✅ **Trackable** - Full audit trail of attempts and errors  
✅ **Safe** - Multiple validation checks prevent invalid sends  

**The integration is now REAL-TIME with NO manual intervention required!** 🎉
