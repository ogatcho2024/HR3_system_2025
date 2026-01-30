# Timesheet to Payroll Integration - Sender Documentation

## Overview
This Laravel implementation sends approved timesheet data from HumanResources3 system (hr3.cranecali-ms.com) to the external Payroll system (hr4.cranecali-ms.com) via REST API.

---

## Files Created

### 1. Service Layer
**File:** `app/Services/TimesheetSenderService.php`
- Handles HTTP communication with external payroll API
- Validates timesheet data before sending
- Signs payload with HMAC-SHA256
- Implements retry logic and timeout handling
- Returns structured responses with success/failure status

### 2. Queue Job
**File:** `app/Jobs/SyncTimesheetJob.php`
- Queued job for async timesheet syncing
- 3 retry attempts with 60-second backoff
- 120-second timeout
- Comprehensive logging

### 3. Controller
**File:** `app/Http/Controllers/TimesheetPayrollSyncController.php`
- **sendTimesheet()** - Synchronous single send
- **queueTimesheet()** - Async single send
- **sendBatch()** - Synchronous batch send
- **queueBatch()** - Async batch send
- **autoSyncDateRange()** - Date range sync

### 4. Configuration
**File:** `config/payroll.php`
- Centralized payroll API configuration
- Reads from environment variables

### 5. Routes
**File:** `routes/api.php`
- Added `/api/payroll-sync/*` routes

---

## Environment Variables

Add these to your `.env` file:

```env
# Payroll System Integration
PAYROLL_API_ENDPOINT=https://hr4.cranecali-ms.com/api/payroll/timesheets/import.php
PAYROLL_API_KEY=your-shared-api-key-here
PAYROLL_API_SECRET=your-shared-secret-key-here
PAYROLL_TIMEOUT=30
PAYROLL_RETRIES=3
PAYROLL_RETRY_DELAY=100
PAYROLL_SOURCE_SYSTEM=HumanResources3
PAYROLL_SYNC_ENABLED=true

# Queue Configuration (for async jobs)
QUEUE_CONNECTION=database
```

---

## API Routes

### Base URL
```
https://hr3.cranecali-ms.com/api/payroll-sync
```

### Authentication
All routes require:
- Laravel `auth` middleware (user must be logged in)
- `simple.api.auth` middleware (API token authentication)

---

## Available Endpoints

### 1. Send Single Timesheet (Synchronous)
```http
POST /api/payroll-sync/send/{timesheetId}
```

**Example:**
```bash
curl -X POST https://localhost/dashboard/HumanResources3/public/api/payroll-sync/send/42 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "Timesheet successfully sent to payroll system.",
  "timesheet_id": 42,
  "payroll_response": {
    "status": "success",
    "message": "Timesheet imported"
  }
}
```

---

### 2. Queue Single Timesheet (Async)
```http
POST /api/payroll-sync/queue/{timesheetId}
```

**Example:**
```bash
curl -X POST https://localhost/dashboard/HumanResources3/public/api/payroll-sync/queue/42 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Response:**
```json
{
  "success": true,
  "message": "Timesheet queued for payroll sync.",
  "timesheet_id": 42
}
```

---

### 3. Send Batch (Synchronous)
```http
POST /api/payroll-sync/send-batch
```

**Request Body:**
```json
{
  "timesheet_ids": [42, 43, 44]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Batch sync completed.",
  "summary": {
    "total": 3,
    "successful": 2,
    "failed": 1
  },
  "results": [
    {
      "success": true,
      "status_code": 200,
      "timesheet_id": 42
    },
    {
      "success": true,
      "status_code": 200,
      "timesheet_id": 43
    },
    {
      "success": false,
      "status_code": 422,
      "timesheet_id": 44,
      "message": "Validation failed"
    }
  ]
}
```

---

### 4. Queue Batch (Async)
```http
POST /api/payroll-sync/queue-batch
```

**Request Body:**
```json
{
  "timesheet_ids": [42, 43, 44]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Timesheets queued for payroll sync.",
  "count": 3
}
```

---

### 5. Auto-Sync Date Range
```http
POST /api/payroll-sync/sync-date-range
```

**Request Body:**
```json
{
  "start_date": "2026-01-01",
  "end_date": "2026-01-31",
  "async": true
}
```

**Response (async):**
```json
{
  "success": true,
  "message": "Timesheets queued for payroll sync.",
  "count": 15
}
```

**Response (sync when async=false):**
```json
{
  "success": true,
  "message": "Batch sync completed.",
  "summary": {
    "total": 15,
    "successful": 14,
    "failed": 1
  },
  "results": [...]
}
```

---

## Payload Structure

### Outgoing Payload to Payroll System
```json
{
  "id": 42,
  "user_id": 5,
  "work_date": "2026-01-27",
  "time_in": "08:30:00",
  "time_out": "17:30:00",
  "total_hours": 8.5,
  "overtime_hours": 0.5,
  "status": "approved",
  "source_system": "HumanResources3",
  "project_name": "Website Redesign",
  "work_description": "Implemented responsive design",
  "submitted_at": "2026-01-27T10:00:00+00:00",
  "approved_at": "2026-01-27T14:00:00+00:00",
  "approved_by": 1,
  "timestamp": "2026-01-29T06:14:53+00:00"
}
```

### HTTP Headers Sent
```
Content-Type: application/json
Accept: application/json
X-API-KEY: your-shared-api-key-here
X-Signature: hmac_sha256_signature_of_payload
```

---

## Security

### HMAC-SHA256 Signature

Every request payload is signed with HMAC-SHA256:

```php
$secret = config('payroll.api_secret');
$data = json_encode($payload, JSON_UNESCAPED_SLASHES);
$signature = hash_hmac('sha256', $data, $secret);
```

The signature is sent in the `X-Signature` header.

### Validation Rules

Before sending, the service validates:
- ✅ `status` must be "approved"
- ✅ `id` is required
- ✅ `user_id` is required
- ✅ `work_date` is required
- ✅ `clock_in_time` is required
- ✅ `clock_out_time` is required
- ✅ `hours_worked` is required

---

## Retry Logic

### HTTP Client Retries
- **Retries:** 3 attempts (configurable)
- **Delay:** 100ms between retries (configurable)
- **Timeout:** 30 seconds (configurable)

### Queue Job Retries
- **Attempts:** 3 times
- **Backoff:** 60 seconds between attempts
- **Timeout:** 120 seconds per attempt

---

## Logging

All operations are logged to Laravel's log system:

### Success Log
```
[INFO] Timesheet successfully sent to payroll
{
  "timesheet_id": 42,
  "status_code": 200,
  "response": {...}
}
```

### Failure Log
```
[ERROR] Failed to send timesheet to payroll
{
  "timesheet_id": 42,
  "status_code": 422,
  "error": "Validation failed"
}
```

### Job Logs
```
[INFO] SyncTimesheetJob started
{
  "timesheet_id": 42,
  "attempt": 1
}
```

---

## Usage Examples

### Example 1: Send Approved Timesheet Immediately
```php
use App\Services\TimesheetSenderService;
use App\Models\Timesheet;

$timesheet = Timesheet::find(42);
$service = new TimesheetSenderService();
$result = $service->sendToPayroll($timesheet);

if ($result['success']) {
    echo "Sent successfully!";
} else {
    echo "Failed: " . $result['message'];
}
```

### Example 2: Queue Timesheet for Async Sending
```php
use App\Jobs\SyncTimesheetJob;
use App\Models\Timesheet;

$timesheet = Timesheet::find(42);
SyncTimesheetJob::dispatch($timesheet);
```

### Example 3: Batch Send via API
```bash
curl -X POST https://localhost/dashboard/HumanResources3/public/api/payroll-sync/send-batch \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "timesheet_ids": [42, 43, 44, 45]
  }'
```

### Example 4: Auto-Sync Last Month's Timesheets
```bash
curl -X POST https://localhost/dashboard/HumanResources3/public/api/payroll-sync/sync-date-range \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2026-01-01",
    "end_date": "2026-01-31",
    "async": true
  }'
```

---

## Error Handling

### Common Error Responses

#### 400 - Not Approved
```json
{
  "success": false,
  "status_code": 400,
  "message": "Only approved timesheets can be sent to payroll system.",
  "timesheet_id": 42
}
```

#### 404 - Not Found
```json
{
  "success": false,
  "message": "No approved timesheets found with provided IDs."
}
```

#### 422 - Validation Failed
```json
{
  "success": false,
  "status_code": 422,
  "message": "Validation failed: time_in is required, time_out is required",
  "timesheet_id": 42
}
```

#### 500 - Exception
```json
{
  "success": false,
  "status_code": 500,
  "message": "Exception occurred: Connection timeout",
  "timesheet_id": 42
}
```

---

## Queue Configuration

### Setup Queue Worker

For async jobs to work, you need a queue worker running:

```bash
# Run queue worker
php artisan queue:work

# Or use supervisor for production
php artisan queue:work --daemon --tries=3
```

### Create Queue Tables (if using database driver)
```bash
php artisan queue:table
php artisan migrate
```

---

## Testing

### Test with Postman

1. **Set Authorization Header**
   ```
   Authorization: Bearer YOUR_API_TOKEN
   ```

2. **Test Single Send**
   ```
   POST /api/payroll-sync/send/42
   ```

3. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Test Payload Signature Locally

```php
// In tinker: php artisan tinker
$payload = ['id' => 42, 'user_id' => 5];
$secret = 'test-secret';
$data = json_encode($payload, JSON_UNESCAPED_SLASHES);
$signature = hash_hmac('sha256', $data, $secret);
echo $signature;
```

---

## Receiver Side Requirements

The payroll system at **hr4.cranecali-ms.com** must:

1. Accept `POST` requests to `/api/payroll/timesheets/import.php`
2. Validate `X-API-KEY` header
3. Verify `X-Signature` header (HMAC-SHA256)
4. Accept JSON payload with structure above
5. Return JSON response:
   ```json
   {
     "status": "success",
     "message": "Timesheet imported",
     "timesheet_id": 42
   }
   ```

---

## Production Checklist

- [ ] Set `PAYROLL_API_KEY` in `.env`
- [ ] Set `PAYROLL_API_SECRET` in `.env`
- [ ] Verify `PAYROLL_API_ENDPOINT` points to `https://hr4.cranecali-ms.com/api/payroll/timesheets/import.php`
- [ ] Configure queue driver (`QUEUE_CONNECTION=database` or `redis`)
- [ ] Run queue migrations if using database
- [ ] Start queue worker (`php artisan queue:work`)
- [ ] Test with a single approved timesheet
- [ ] Monitor logs for errors
- [ ] Coordinate API key and secret with payroll system admin
- [ ] Test signature verification on receiver side

---

## Troubleshooting

### Issue: Jobs Not Processing
**Solution:** Ensure queue worker is running
```bash
php artisan queue:work
```

### Issue: Connection Timeout
**Solution:** Increase timeout in `.env`
```env
PAYROLL_TIMEOUT=60
```

### Issue: Signature Mismatch
**Solution:** Ensure both systems use same `PAYROLL_API_SECRET`

### Issue: 401 Unauthorized
**Solution:** Verify `PAYROLL_API_KEY` matches on both systems

### Issue: Invalid Payload
**Solution:** Check logs for validation errors
```bash
tail -f storage/logs/laravel.log | grep payroll
```

---

## Support

For questions or issues, check:
- Laravel logs: `storage/logs/laravel.log`
- Queue failed jobs: `php artisan queue:failed`
- Retry failed job: `php artisan queue:retry {job-id}`

---

**Last Updated:** January 29, 2026  
**Version:** 1.0.0  
**Receiver Domain:** hr4.cranecali-ms.com
