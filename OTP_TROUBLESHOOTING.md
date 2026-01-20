# OTP System Troubleshooting Guide

## Problem: OTP Bypassed in Production

### Root Cause
The OTP system was being bypassed because the `require_2fa` field in the users table was either:
- NULL (most likely cause)
- FALSE/0 (explicitly disabled)
- Missing OTP_ENABLED configuration

### How to Diagnose

#### 1. Run the Debug Command
```bash
# Check all users
php artisan debug:otp

# Check specific user
php artisan debug:otp user@example.com
```

This will show you:
- OTP configuration status
- User's require_2fa value
- Whether OTP will trigger for that user

#### 2. Check Laravel Logs
```bash
# View logs in real-time
tail -f storage/logs/laravel.log

# Search for OTP decision logs
grep "OTP DECISION POINT" storage/logs/laravel.log
grep "OTP TRIGGERED" storage/logs/laravel.log
grep "OTP BYPASSED" storage/logs/laravel.log
```

Look for log entries like:
```
=== OTP DECISION POINT ===
require_2fa_db_value: null  <-- THIS IS THE PROBLEM
```

#### 3. Direct Database Check
```sql
-- Check specific user
SELECT id, email, require_2fa, otp_verified, otp_code 
FROM users 
WHERE email = 'user@example.com';

-- Check all users
SELECT id, email, require_2fa, 
       CASE 
         WHEN require_2fa IS NULL THEN 'NULL'
         WHEN require_2fa = 1 THEN 'TRUE'
         ELSE 'FALSE'
       END as require_2fa_status
FROM users;
```

---

## Solutions

### Solution 1: Fix Individual User (Quick Fix)
```sql
-- Enable OTP for specific user
UPDATE users 
SET require_2fa = 1 
WHERE email = 'user@example.com';
```

### Solution 2: Fix All Users (Recommended)
```sql
-- Enable OTP for all users where it's NULL or FALSE
UPDATE users 
SET require_2fa = 1 
WHERE require_2fa IS NULL OR require_2fa = 0;
```

### Solution 3: Set Default for New Users
Update your user registration/creation code to always set `require_2fa = true`:

```php
User::create([
    'name' => $name,
    'email' => $email,
    'password' => Hash::make($password),
    'require_2fa' => true,  // <-- ADD THIS
    // ... other fields
]);
```

---

## Configuration Checklist

### 1. .env File
Ensure these are set in your production `.env`:

```env
# OTP Configuration
OTP_ENABLED=true
OTP_EXPIRY_MINUTES=5
OTP_RESEND_COOLDOWN=60
OTP_LENGTH=6

# Mail Configuration (Brevo SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-brevo-smtp-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="YourApp OTP"

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Environment
APP_ENV=production
APP_DEBUG=false
```

### 2. Clear Caches (CRITICAL)
After any config changes, run:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

In production, also run:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Verify Database Migration
Ensure the users table has these columns:
```sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS require_2fa BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS otp_code VARCHAR(6) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS otp_expires_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS otp_verified BOOLEAN DEFAULT FALSE;
```

---

## Understanding the Fixed Logic

### Before (Problematic)
```php
if ($user->require_2fa) {
    // Generate OTP
}
// This fails silently if require_2fa is NULL or 0
```

### After (Fixed)
```php
$otpEnabled = config('auth.otp_enabled', true);
$userRequires2FA = $user->require_2fa ?? true; // NULL becomes true

if ($otpEnabled && $userRequires2FA === true) {
    // Generate OTP with logging
    logger()->info('OTP TRIGGERED');
} else {
    logger()->info('OTP BYPASSED');
}
```

**Key Improvements:**
1. ✅ Defaults NULL to true (secure by default)
2. ✅ Explicit boolean comparison
3. ✅ Global OTP_ENABLED flag
4. ✅ Comprehensive logging
5. ✅ Force session save
6. ✅ Synchronous email sending

---

## Testing the Fix

### Step 1: Verify Configuration
```bash
php artisan debug:otp user@example.com
```

Expected output should show:
```
Will Trigger OTP: YES
```

### Step 2: Test Login Flow
1. Login with credentials
2. Check logs: `tail -f storage/logs/laravel.log`
3. Should see: `OTP TRIGGERED: Generating OTP for user`
4. Should redirect to OTP verification page
5. Check email for OTP code

### Step 3: Verify Database
After login, check:
```sql
SELECT otp_code, otp_expires_at, otp_verified 
FROM users 
WHERE email = 'test@example.com';
```

Should show:
- `otp_code`: 6-digit number
- `otp_expires_at`: timestamp 5 minutes in future
- `otp_verified`: 0 (false)

---

## Common Issues & Solutions

### Issue 1: Still Bypassing After Fix
**Symptoms:** OTP still doesn't trigger

**Check:**
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear

# Verify config loaded
php artisan tinker
>>> config('auth.otp_enabled');
=> true
```

**Solution:** Make sure you cleared config cache in production!

### Issue 2: Session Not Persisting
**Symptoms:** Redirects to dashboard instead of OTP page

**Check:**
- SESSION_DRIVER in .env matches config/session.php
- Sessions table exists if using database driver
- Session cookie settings (SESSION_SECURE_COOKIE, SESSION_DOMAIN)

**Solution:**
```bash
# Recreate sessions table
php artisan session:table
php artisan migrate

# Test session
php artisan tinker
>>> session()->put('test', 'value');
>>> session()->save();
>>> session()->get('test');
```

### Issue 3: OTP Email Not Sending
**Symptoms:** Redirect works but no email

**Check logs:**
```bash
grep "OTP email sent" storage/logs/laravel.log
grep "Failed to send OTP" storage/logs/laravel.log
```

**Test SMTP:**
```bash
php artisan test:brevo-email test@example.com
```

**Common Causes:**
- Wrong Brevo credentials
- Sender email not verified in Brevo
- Firewall blocking port 587
- Using queue() instead of send()

### Issue 4: Config Cache Issues
**Symptoms:** Changes not taking effect

**Solution:**
```bash
# Development
php artisan config:clear

# Production (after config:clear)
php artisan config:cache
```

**Never run config:cache in development!**

---

## Monitoring in Production

### 1. Add to Monitoring Dashboard
Track these metrics:
- OTP generation rate
- OTP verification success rate
- OTP email failures
- OTP bypass rate (should be 0%)

### 2. Set Up Alerts
Alert on:
- High OTP failure rate
- Email send failures
- Unexpected OTP bypass

### 3. Regular Checks
```bash
# Daily: Check for users without 2FA
mysql -u user -p -e "SELECT COUNT(*) as users_without_2fa FROM humanresources3.users WHERE require_2fa IS NULL OR require_2fa = 0;"

# Weekly: Review OTP logs
grep "OTP BYPASSED" storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

## Environment-Specific Settings

### Local Development (.env)
```env
APP_ENV=local
APP_DEBUG=true
OTP_ENABLED=true
```

### Production (.env)
```env
APP_ENV=production
APP_DEBUG=false
OTP_ENABLED=true
SESSION_SECURE_COOKIE=true
```

---

## Quick Reference Commands

```bash
# Debug OTP status
php artisan debug:otp user@example.com

# Test email sending
php artisan test:brevo-email user@example.com

# View logs
tail -f storage/logs/laravel.log | grep OTP

# Clear caches
php artisan config:clear && php artisan cache:clear

# Enable 2FA for all users
php artisan tinker
>>> \App\Models\User::whereNull('require_2fa')->update(['require_2fa' => true]);

# Check OTP config
php artisan tinker
>>> config('auth.otp_enabled');
>>> config('auth.otp_expiry_minutes');
```

---

## Support & Documentation

- **Main Documentation:** OTP_SYSTEM_DOCUMENTATION.md
- **Brevo Setup:** BREVO_SETUP_GUIDE.md
- **Laravel Logs:** storage/logs/laravel.log
- **Audit Logs:** Check `audit_logs` table in database

---

## Checklist for Production Deployment

Before deploying OTP system to production:

- [ ] Set OTP_ENABLED=true in production .env
- [ ] Verify all users have require_2fa set
- [ ] Test email sending with test:brevo-email command
- [ ] Clear all caches after deployment
- [ ] Verify SESSION_DRIVER is set correctly
- [ ] Check Brevo sender email is verified
- [ ] Test complete login flow with real user
- [ ] Monitor logs for first 24 hours
- [ ] Set up email failure alerts
- [ ] Document recovery procedure for locked-out users
- [ ] Train support team on OTP troubleshooting

---

**Last Updated:** January 20, 2026
**Version:** 2.0 (Production-Ready)
