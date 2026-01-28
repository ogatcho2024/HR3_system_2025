# QR Code Attendance System - Implementation Summary

## Overview
Successfully implemented a daily QR code-based attendance system for the HumanResources3 Laravel application. Employees can generate unique daily QR codes, and authorized personnel (Super Admin, Admin, Staff) can scan them using a webcam to log TIME-IN and TIME-OUT automatically.

## Features Implemented
✅ **Daily QR Code Generation** - Unique, date-bound QR codes using HMAC-SHA256  
✅ **Auto TIME-IN/TIME-OUT Detection** - Automatically determines log type based on existing entries  
✅ **Cooldown System** - 5-minute wait period between scans  
✅ **Max Logs Enforcement** - Maximum 2 logs per day (1 IN, 1 OUT)  
✅ **Webcam Scanner** - Real-time QR scanning using html5-qrcode library  
✅ **Role-Based Access Control** - Scanner restricted to Super Admin, Admin, and Staff  
✅ **Audit Trail** - Detailed logging with IP address and user agent  
✅ **No Cron Jobs Required** - Tokens generated algorithmically on-demand  

---

## Files Created/Modified

### Database Migrations
1. `database/migrations/2026_01_27_000001_add_qr_secret_to_employees_table.php`
   - Adds `qr_secret` column to employees table

2. `database/migrations/2026_01_27_000002_create_qr_attendance_logs_table.php`
   - Creates `qr_attendance_logs` table for detailed scan tracking

### Models
3. `app/Models/Employee.php` (MODIFIED)
   - Added `qr_secret` to fillable array
   - Added `generateDailyQrToken()` method - generates today's HMAC token
   - Added `verifyDailyToken()` method - validates scanned tokens
   - Added auto-generation of qr_secret on employee creation
   - Added relationship to qr_attendance_logs

4. `app/Models/QrAttendanceLog.php` (NEW)
   - Model for individual QR scan logs
   - Includes scopes: today(), onDate(), timeIn(), timeOut(), forEmployee()

### Services
5. `app/Services/QrAttendanceService.php` (NEW)
   - Business logic for cooldown checking
   - Attendance validation (max logs, permissions)
   - Next log type determination (IN vs OUT)
   - Attendance statistics retrieval

### Controllers
6. `app/Http/Controllers/QrAttendanceController.php` (NEW)
   - `showEmployeeQr()` - Display employee's daily QR code
   - `showScanner()` - Display scanner interface for admins/staff
   - `processScan()` - Process QR scan and log attendance

### Middleware
7. `app/Http/Middleware/AdminOrStaffMiddleware.php` (NEW)
   - Restricts access to Super Admin, Admin, and Staff roles
   - Returns 403 Forbidden for unauthorized users

8. `bootstrap/app.php` (MODIFIED)
   - Registered 'admin.or.staff' middleware alias

### Routes
9. `routes/web.php` (MODIFIED)
   - Employee route: `GET /employee/qr-today` → QR display page
   - Scanner route: `GET /attendance/scanner` → Scanner page (Admin/Staff only)
   - API route: `POST /attendance/qr-scan` → Process QR scan

### Views
10. `resources/views/employee/qr-today.blade.php` (NEW)
    - Employee-facing page to display daily QR code
    - Uses qrcode.js library via CDN
    - Auto-refreshes at midnight
    - Shows today's attendance status

11. `resources/views/attendance/scanner.blade.php` (NEW)
    - Admin/Staff scanner interface
    - Uses html5-qrcode library for webcam scanning
    - Real-time scan feedback and recent scans panel
    - Camera selection and controls

### Console Commands
12. `app/Console/Commands/GenerateQrSecrets.php` (NEW)
    - Artisan command to backfill qr_secret for existing employees
    - Usage: `php artisan qr:generate-secrets`
    - Supports `--force` flag to regenerate all secrets

---

## Setup Instructions

### 1. Run Migrations
```bash
php artisan migrate
```

This will:
- Add `qr_secret` column to employees table
- Create `qr_attendance_logs` table

### 2. Generate QR Secrets for Existing Employees
```bash
php artisan qr:generate-secrets
```

This command will:
- Find all employees without a `qr_secret`
- Generate secure random secrets (SHA256 hashed)
- Display progress bar and summary

**Note:** New employees will automatically get a qr_secret when created.

### 3. Verify Routes
Check that routes are properly registered:
```bash
php artisan route:list | grep qr
```

Expected output:
```
GET   employee/qr-today        employee.qr-today
GET   attendance/scanner       attendance.scanner
POST  attendance/qr-scan       attendance.qr-scan
```

### 4. Test Access Permissions
- **Employees**: Can access `/employee/qr-today`
- **Admin/Staff**: Can access `/attendance/scanner`
- **Employees**: Should get 403 when accessing `/attendance/scanner`

---

## Usage Guide

### For Employees
1. Navigate to **Employee Dashboard** → Click "My QR Today" (or go to `/employee/qr-today`)
2. Your unique QR code for today will be displayed
3. Present this QR code to the attendance scanner
4. **First scan = TIME-IN**, **Second scan = TIME-OUT**
5. QR code automatically refreshes at midnight

### For Admins/Staff (Scanner Operators)
1. Navigate to `/attendance/scanner`
2. Select camera from dropdown
3. Click "Start Scanner" and allow camera permissions
4. Position employee's QR code within the camera frame
5. Scanner automatically detects and processes the QR code
6. View real-time feedback and recent scans panel

---

## Technical Details

### QR Token Generation
```php
// Daily token = HMAC(current_date, employee_qr_secret)
$dailyToken = hash_hmac('sha256', date('Y-m-d'), $employee->qr_secret);
```

**Security Features:**
- Tokens are date-bound and cannot be reused across days
- HMAC ensures tokens cannot be forged without knowing the secret
- Stateless validation (no database lookups required)

### QR Payload Format
```json
{
  "token": "abc123...",
  "emp_id": 42,
  "date": "2026-01-27"
}
```

### Attendance Logic
1. **Validation Checks:**
   - Date must be today
   - Token must match expected HMAC
   - Cooldown period (5 minutes) must have elapsed
   - Max 2 logs per day not exceeded

2. **Log Type Determination:**
   - 0 logs today → **IN**
   - 1 log today (type=IN) → **OUT**
   - 1 log today (type=OUT) → ERROR
   - 2 logs today → ERROR (max reached)

3. **Database Updates:**
   - Creates entry in `qr_attendance_logs` table
   - Updates/creates entry in `attendances` table
   - Sets `clock_in_time` (for IN) or `clock_out_time` (for OUT)

### Database Schema

#### qr_attendance_logs
| Column       | Type         | Description                    |
|--------------|--------------|--------------------------------|
| id           | bigint       | Primary key                    |
| employee_id  | bigint       | FK to employees.id             |
| log_type     | enum         | 'IN' or 'OUT'                  |
| scanned_at   | datetime     | Timestamp of scan              |
| daily_token  | string(64)   | Token used for scan            |
| ip_address   | string(45)   | IP address of scanner          |
| user_agent   | text         | Browser user agent             |
| created_at   | timestamp    | Record creation time           |
| updated_at   | timestamp    | Record update time             |

#### employees (added column)
| Column       | Type         | Description                    |
|--------------|--------------|--------------------------------|
| qr_secret    | string(64)   | Secret for QR token generation |

---

## Demo Testing Checklist

### Preparation
- [ ] Run migrations: `php artisan migrate`
- [ ] Generate QR secrets: `php artisan qr:generate-secrets`
- [ ] Create test users with roles: Employee, Admin, Staff, Super Admin
- [ ] Ensure each test user has an employee record

### Test Cases

#### 1. Employee QR Generation
- [ ] Login as Employee
- [ ] Navigate to `/employee/qr-today`
- [ ] Verify QR code is displayed
- [ ] Verify employee info is correct
- [ ] Verify today's date is shown
- [ ] Check today's attendance status (should show "No Record" initially)

#### 2. Scanner Access Control
- [ ] Login as Employee → Try to access `/attendance/scanner` → Should get **403 Forbidden**
- [ ] Login as Staff → Access `/attendance/scanner` → Should work ✓
- [ ] Login as Admin → Access `/attendance/scanner` → Should work ✓
- [ ] Login as Super Admin → Access `/attendance/scanner` → Should work ✓

#### 3. TIME-IN Scan
- [ ] Login as Admin/Staff and open `/attendance/scanner`
- [ ] Click "Start Scanner" and allow camera access
- [ ] Display employee's QR code from phone/another screen
- [ ] Scan successfully logs **TIME-IN**
- [ ] Verify success message shows employee name and time
- [ ] Check database: `qr_attendance_logs` has 1 entry (type=IN)
- [ ] Check database: `attendances` has entry with `clock_in_time` set

#### 4. TIME-OUT Scan
- [ ] Wait at least 5 minutes (or temporarily reduce cooldown in code for testing)
- [ ] Scan same employee's QR code again
- [ ] Verify **TIME-OUT** is logged
- [ ] Check database: `qr_attendance_logs` has 2 entries (IN + OUT)
- [ ] Check database: `attendances` has `clock_out_time` set

#### 5. Cooldown Enforcement
- [ ] Immediately after scanning, try to scan again (within 5 minutes)
- [ ] Should receive error: **"Please wait X minute(s) before scanning again"**

#### 6. Max Logs Per Day
- [ ] After logging IN and OUT, try to scan a third time
- [ ] Should receive error: **"Maximum attendance logs reached for today"**

#### 7. Invalid QR Code
- [ ] Try scanning a QR code from yesterday's date
- [ ] Should receive error: **"QR code is not valid for today"**
- [ ] Try scanning a QR code with invalid token
- [ ] Should receive error: **"Invalid or expired QR code"**

#### 8. Recent Scans Panel
- [ ] Verify recent scans appear in the right panel
- [ ] Check that IN scans are green and OUT scans are blue
- [ ] Verify employee name and time are displayed

---

## Defense Talking Points

### Why Daily QR Codes?
- **Security**: Prevents QR code reuse across days
- **Simplicity**: No need to manually regenerate codes
- **Automatic**: Tokens expire at midnight without cron jobs

### Why Separate qr_attendance_logs Table?
- **Audit Trail**: Detailed record of every scan (IP, user agent, exact time)
- **Flexibility**: Doesn't interfere with existing attendance table structure
- **Debugging**: Easy to track scan history and troubleshoot issues

### Why HMAC-SHA256?
- **Cryptographic Security**: Impossible to forge tokens without the secret
- **Stateless**: No need to store tokens in database
- **Fast**: No database lookups for validation

### Why 5-Minute Cooldown?
- **Prevents Accidental Double-Scans**: User won't accidentally scan twice
- **Allows Corrections**: If scan fails, can retry after 5 minutes
- **Balance**: Not too restrictive, but prevents abuse

### Why 2 Logs Per Day Max?
- **Simplicity**: Enforces clear IN/OUT pattern
- **Realistic**: Covers 99% of attendance scenarios
- **Prevents Abuse**: Users can't spam the system

### Why Role-Based Scanner Access?
- **Security**: Prevents employees from logging their own attendance
- **Accountability**: Only authorized personnel can operate scanner
- **Audit**: Logs show who operated the scanner (via auth user)

---

## Troubleshooting

### Issue: QR Code Not Displaying
- Check if employee has `qr_secret`: `SELECT qr_secret FROM employees WHERE id=?`
- Run: `php artisan qr:generate-secrets` if missing
- Check browser console for JavaScript errors

### Issue: Scanner Not Starting
- Verify camera permissions are granted in browser
- Check browser console for errors
- Try a different browser (Chrome/Edge recommended)
- Ensure HTTPS is used (required for camera access in most browsers)

### Issue: "Invalid QR Code" Error
- Verify QR code date matches today
- Check employee's qr_secret exists in database
- Ensure QR code is generated from the correct environment

### Issue: 403 Forbidden on Scanner Page
- Verify user has Admin, Staff, or Super Admin role
- Check `account_type` column in users table
- Ensure middleware is registered in `bootstrap/app.php`

### Issue: Attendance Not Logging
- Check `qr_attendance_logs` table for entries
- Verify database transaction didn't fail (check Laravel logs)
- Ensure CSRF token is included in POST request
- Check network tab in browser DevTools for API errors

---

## API Endpoints

### POST /attendance/qr-scan
**Access:** Super Admin, Admin, Staff only  
**Authentication:** Required (session + 2FA)  
**CSRF Protection:** Required

**Request Body:**
```json
{
  "token": "abc123...",
  "emp_id": 42,
  "date": "2026-01-27"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "type": "IN",
  "employee": {
    "id": 42,
    "name": "John Doe",
    "employee_id": "EMP-001"
  },
  "time": "09:15:30",
  "message": "Successfully logged IN at 9:15 AM"
}
```

**Error Responses:**
- **422 Unprocessable Entity**: Invalid QR code, wrong date, max logs reached
- **429 Too Many Requests**: Cooldown period active
- **404 Not Found**: Employee not found
- **500 Internal Server Error**: Database or system error

---

## Future Enhancements (Optional)

1. **Geolocation Verification**: Ensure scans occur at specific locations
2. **Shift-Based Attendance**: Validate against assigned shifts
3. **Offline QR Storage**: Cache QR codes for offline scenarios
4. **SMS Notifications**: Send alerts on TIME-IN/TIME-OUT
5. **Analytics Dashboard**: Track attendance patterns and trends
6. **Export Functionality**: Download attendance logs as CSV/PDF
7. **Multi-Day QR Codes**: For overnight shifts spanning midnight
8. **Biometric Verification**: Combine QR with face recognition

---

## Key Files Reference

| Purpose | File Path |
|---------|-----------|
| Employee QR Page | `/employee/qr-today` |
| Scanner Page | `/attendance/scanner` |
| API Endpoint | `POST /attendance/qr-scan` |
| Migration Command | `php artisan qr:generate-secrets` |
| Employee Model | `app/Models/Employee.php` |
| Service Layer | `app/Services/QrAttendanceService.php` |
| Controller | `app/Http/Controllers/QrAttendanceController.php` |

---

## Credits
- **QR Code Generation**: qrcode.js (https://davidshimjs.github.io/qrcodejs/)
- **QR Code Scanning**: html5-qrcode (https://github.com/mebjas/html5-qrcode)
- **Framework**: Laravel 12 with PHP 8.2

---

## License
This implementation is part of the HumanResources3 Laravel application and follows the same license terms.

---

**Implementation Date**: January 27, 2026  
**Version**: 1.0.0  
**Status**: ✅ Complete and Ready for Demo
