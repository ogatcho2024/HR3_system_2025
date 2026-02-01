# Employee OTP Bypass Issue - Fix Documentation

## Problem Description

**Issue:** Employee accounts are bypassing OTP verification in production, while other account types (Admin, Super admin, Staff) are working correctly. This allows Employee users to access protected routes (like `/employee/qr-today`) without completing 2FA verification, resulting in 404 errors or unexpected behavior.

**Affected:** Production environment only (localhost works correctly)

## Root Cause

The issue is caused by the `require_2fa` field in the database having incorrect values for Employee accounts. This can happen due to:

1. **Database migration not running properly** in production
2. **Manual database changes** that set `require_2fa` to `false`, `0`, or `NULL`
3. **Environment-specific data seeding** differences between localhost and production
4. **OTP_ENABLED environment variable** being set differently in production `.env` file

## How OTP Verification Works

In `AuthController.php` (line 152), the system checks:

```php
if ($otpEnabled && $userRequires2FA === true) {
    // Generate and send OTP
    // Redirect to OTP verification page
}
```

The check uses **strict boolean comparison** (`=== true`), which means:
- ✅ `require_2fa = 1` (database) → casts to `true` → OTP REQUIRED
- ✅ `require_2fa = true` → OTP REQUIRED
- ❌ `require_2fa = 0` → casts to `false` → **OTP BYPASSED**
- ❌ `require_2fa = NULL` → casts to `null` → **OTP BYPASSED**
- ❌ `require_2fa = false` → **OTP BYPASSED**

## Diagnosis

### Step 1: Check Employee Account Settings

Run the diagnostic command on your **production server**:

```bash
php artisan otp:check-employees
```

Or check a specific employee:

```bash
php artisan otp:check-employees --email=employee@example.com
```

This will show:
- Raw database values for `require_2fa`
- How the values are being cast by the model
- Current OTP verification status
- Any configuration issues

### Expected Output (Healthy):
```
=== OTP/2FA Employee Account Diagnostic ===

Environment: production
OTP Config Enabled: YES
Session Driver: database

Found 5 Employee account(s)

+----+----------------------+---------------+-----------------+------------------+--------------+--------------+------------------+
| ID | Email                | Account Type  | require_2fa (DB)| require_2fa Cast | otp_verified | Has OTP Code | OTP Expires At   |
+----+----------------------+---------------+-----------------+------------------+--------------+--------------+------------------+
| 10 | employee@example.com | Employee      | TRUE (1)        | TRUE (1)         | NO           | NO           | NULL             |
+----+----------------------+---------------+-----------------+------------------+--------------+--------------+------------------+

✓ No issues found - all employees have require_2fa = true
```

### Problem Output (Needs Fix):
```
=== ISSUES FOUND ===
⚠ User employee@example.com: require_2fa is FALSE in database
⚠ User employee@example.com: require_2fa casts to NON-TRUE value (type: boolean, value: false)
```

## Fix Instructions

### Option 1: Automated Fix (Recommended)

1. **Dry run first** (see what would be changed):
```bash
php artisan otp:fix-employees --dry-run
```

2. **Apply the fix**:
```bash
php artisan otp:fix-employees
```

This will:
- Set `require_2fa = true` for all Employee accounts
- Reset OTP state (`otp_verified = false`, clear OTP code)
- Force OTP verification on next login

### Option 2: Manual Database Fix

Run this SQL query in your production database:

```sql
-- Check current state
SELECT id, email, account_type, require_2fa, otp_verified 
FROM users 
WHERE account_type IN ('Employee', 'employee');

-- Fix Employee accounts
UPDATE users 
SET 
    require_2fa = 1,
    otp_verified = 0,
    otp_code = NULL,
    otp_expires_at = NULL,
    updated_at = NOW()
WHERE account_type IN ('Employee', 'employee')
  AND (require_2fa IS NULL OR require_2fa = 0);
```

### Option 3: Run Migration Again

If the migration never ran in production:

```bash
# Check migration status
php artisan migrate:status

# Run specific migration
php artisan migrate --path=/database/migrations/2026_01_20_155500_enable_2fa_for_all_users.php --force
```

## Verification

After applying the fix:

1. **Re-run the diagnostic**:
```bash
php artisan otp:check-employees
```

2. **Test Employee login**:
   - Log in with an Employee account
   - Should receive OTP email
   - Should be redirected to `/otp/verify` page
   - After OTP verification, should access `/employee/qr-today` successfully

3. **Check logs** (production):
```bash
tail -f storage/logs/laravel.log | grep "OTP DECISION POINT"
```

Expected log output:
```
[production] OTP DECISION POINT: {"user_id":10,"require_2fa_db_value":true,"require_2fa_resolved":true}
[production] OTP TRIGGERED: Generating OTP for user {"user_id":10}
```

## Prevention

### 1. Add Database Constraint

Add this migration to prevent future issues:

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('require_2fa')->default(true)->change();
});
```

### 2. Monitor Environment Variables

Ensure `.env` file in production has:
```env
OTP_ENABLED=true
```

### 3. Add Test Coverage

Create a test to ensure Employee accounts always require 2FA:

```php
public function test_employee_accounts_require_2fa()
{
    $employee = User::factory()->create(['account_type' => 'Employee']);
    $this->assertTrue($employee->require_2fa);
}
```

## Additional Checks

### Check Session Configuration

Verify production session configuration:
```bash
php artisan config:show session
```

Ensure session driver is not `array` (which doesn't persist):
```env
SESSION_DRIVER=database  # or file, redis
```

### Check .env File Differences

Compare localhost vs production:
```bash
# Localhost
grep OTP .env

# Production (via SSH)
grep OTP .env
```

## Related Files

- `app/Http/Controllers/AuthController.php` - OTP decision logic (line 152)
- `app/Http/Middleware/Ensure2FAVerified.php` - 2FA enforcement middleware
- `app/Models/User.php` - Model with `require_2fa` cast to boolean
- `database/migrations/2026_01_11_014655_add_two_factor_fields_to_users_table.php` - Initial 2FA migration
- `database/migrations/2026_01_20_155500_enable_2fa_for_all_users.php` - Migration to enable 2FA for all users
- `config/auth.php` - OTP configuration (line 162)

## Summary

The Employee OTP bypass is caused by `require_2fa` database field having incorrect values (NULL, 0, or false) for Employee accounts in production. The strict boolean comparison in the authentication logic requires this field to be exactly `true` (or 1 in database) for OTP to be triggered.

**Quick Fix:**
```bash
ssh production-server
cd /path/to/application
php artisan otp:fix-employees
```

This will immediately resolve the issue and force Employee users to verify OTP on their next login.
