# Payroll Integration - Quick Start Guide

## 🚀 Setup (3 steps)

### 1. Add to `.env`
```env
PAYROLL_API_ENDPOINT=https://hr4.cranecali-ms.com/api/payroll/timesheets/import.php
PAYROLL_API_KEY=your-shared-api-key-here
PAYROLL_API_SECRET=your-shared-secret-key-here
```

### 2. Setup Queue (if using async)
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

### 3. Test
```bash
curl -X POST http://localhost/dashboard/HumanResources3/public/api/payroll-sync/send/42 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📡 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/payroll-sync/send/{id}` | Send single (sync) |
| POST | `/api/payroll-sync/queue/{id}` | Queue single (async) |
| POST | `/api/payroll-sync/send-batch` | Send multiple (sync) |
| POST | `/api/payroll-sync/queue-batch` | Queue multiple (async) |
| POST | `/api/payroll-sync/sync-date-range` | Auto-sync by date |

---

## 📝 Quick Examples

### Send One Timesheet
```bash
POST /api/payroll-sync/send/42
```

### Send Multiple
```bash
POST /api/payroll-sync/send-batch
Body: {"timesheet_ids": [42, 43, 44]}
```

### Sync Last Month
```bash
POST /api/payroll-sync/sync-date-range
Body: {
  "start_date": "2026-01-01",
  "end_date": "2026-01-31",
  "async": true
}
```

---

## 📦 Files Created

- `app/Services/TimesheetSenderService.php` - HTTP sender
- `app/Jobs/SyncTimesheetJob.php` - Queue job
- `app/Http/Controllers/TimesheetPayrollSyncController.php` - API controller
- `config/payroll.php` - Configuration
- `routes/api.php` - Routes added

---

## ✅ Requirements

- Only **approved** timesheets are sent
- Payload signed with **HMAC-SHA256**
- **3 retries** with 30s timeout
- All operations **logged**

---

## 🔐 Security

### Outgoing Headers
```
X-API-KEY: shared-api-key
X-Signature: hmac_sha256_signature
```

### Payload Structure
```json
{
  "id": 42,
  "user_id": 5,
  "work_date": "2026-01-27",
  "time_in": "08:30:00",
  "time_out": "17:30:00",
  "total_hours": 8.5,
  "status": "approved",
  "source_system": "HumanResources3"
}
```

---

## 🐛 Troubleshooting

**Jobs not running?**
```bash
php artisan queue:work
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log | grep payroll
```

**View failed jobs:**
```bash
php artisan queue:failed
```

---

## 📚 Full Documentation

See `PAYROLL_INTEGRATION_DOCUMENTATION.md` for complete details.

**Sender:** hr3.cranecali-ms.com  
**Receiver:** hr4.cranecali-ms.com
