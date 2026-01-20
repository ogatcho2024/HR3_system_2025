# Production OTP Debugging Guide

## 🔴 Problem: OTP Still Bypassed in Production

Since your local environment works but production doesn't, here's what to check:

---

## Step 1: Check Production Configuration

### SSH into production and run:

```bash
cd /path/to/your/production/app

# Check if OTP config is loaded
php artisan tinker
>>> config('auth.otp_enabled');
# Should output: true

>>> config('auth.otp_expiry_minutes');
# Should output: 5

>>> App\Models\User::find(YOUR_USER_ID)->require_2fa;
# Should output: 1 or true, NOT null or 0
```

---

## Step 2: Check Production .env File

```bash
# View your production .env
cat .env | grep OTP

# Should show:
# OTP_ENABLED=true
# OTP_EXPIRY_MINUTES=5
# OTP_RESEND_COOLDOWN=60
# OTP_LENGTH=6
```

**If these are missing, add them to production .env file!**

---

## Step 3: Clear Production Caches (CRITICAL!)

```bash
# MUST run these on production after any code deployment
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Then cache again for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Step 4: Check Production Database

```sql
-- SSH into production and run MySQL query
SELECT id, email, require_2fa, otp_verified 
FROM users 
WHERE email = 'your-production-email@domain.com';
```

**If `require_2fa` is NULL or 0, fix it:**

```sql
UPDATE users 
SET require_2fa = 1 
WHERE email = 'your-production-email@domain.com';

-- Or fix ALL users
UPDATE users 
SET require_2fa = 1 
WHERE require_2fa IS NULL OR require_2fa = 0;
```

---

## Step 5: Check Production Logs

```bash
# View real-time logs during login
tail -f storage/logs/laravel.log

# Then try logging in from another terminal/browser
# Look for these log entries:
```

**What you SHOULD see:**
```
[2026-01-20 15:30:00] local.INFO: === OTP DECISION POINT ===
[2026-01-20 15:30:00] local.INFO: OTP TRIGGERED: Generating OTP for user
[2026-01-20 15:30:01] local.INFO: OTP saved to database
[2026-01-20 15:30:02] local.INFO: OTP email sent successfully
[2026-01-20 15:30:02] local.INFO: REDIRECTING to OTP verification page
```

**What you might see (BAD):**
```
[2026-01-20 15:30:00] local.INFO: === OTP DECISION POINT ===
[2026-01-20 15:30:00] local.INFO: OTP BYPASSED: User does not require 2FA
```

If you see "OTP BYPASSED", check what the log says for:
- `require_2fa_db_value` → Should be `1` not `null` or `0`
- `otp_enabled_config` → Should be `true`

---

## Step 6: Run Debug Command in Production

```bash
# Check OTP status
php artisan debug:otp your-production-email@domain.com
```

**Expected output:**
```
Decision Logic:
  Config OTP Enabled: YES
  User Requires 2FA: YES
  Will Trigger OTP: YES   <-- MUST BE YES!
```

**If it says NO:**
- Check which line says NO
- Follow the solution provided in the output

---

## Step 7: Verify Code Deployment

Make sure your production has the UPDATED AuthController.php:

```bash
# Check if the new code is deployed
grep -n "OTP DECISION POINT" app/Http/Controllers/AuthController.php

# Should show line ~141 with:
# logger()->info('=== OTP DECISION POINT ===', [
```

**If nothing found, the new code isn't deployed!**

Re-deploy from git:

```bash
git pull origin main
# or
git pull origin feature/otp-bypass-fix

composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan cache:clear
```

---

## Step 8: Check Session Configuration

Production sessions might not be persisting. Check:

```bash
# View session config
cat config/session.php | grep "driver"

# Check .env
cat .env | grep SESSION_DRIVER
```

**Should be:**
```env
SESSION_DRIVER=database
```

**Check sessions table exists:**
```bash
php artisan tinker
>>> DB::table('sessions')->count();
# Should return a number, not an error
```

**If sessions table missing:**
```bash
php artisan session:table
php artisan migrate
```

---

## Step 9: Test Email Sending in Production

```bash
# Test if emails work in production
php artisan test:brevo-email your-email@domain.com
```

**If this fails:**
- Check MAIL_* settings in production .env
- Verify Brevo credentials are correct
- Check if production server can reach smtp-relay.brevo.com:587

---

## Step 10: Check File Permissions

```bash
# Storage must be writable
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Common Production Issues

### Issue 1: Cached Config
**Symptom:** Changes to .env not taking effect

**Solution:**
```bash
php artisan config:clear
# Verify it worked:
php artisan tinker
>>> config('auth.otp_enabled');
```

### Issue 2: require_2fa is NULL in Production DB
**Symptom:** Logs show "require_2fa_db_value: null"

**Solution:**
```sql
UPDATE users SET require_2fa = 1;
```

### Issue 3: Code Not Deployed
**Symptom:** No "OTP DECISION POINT" in logs

**Solution:**
```bash
git pull
composer install
php artisan config:clear
```

### Issue 4: Different .env in Production
**Symptom:** OTP_ENABLED not set

**Solution:**
Add to production .env:
```env
OTP_ENABLED=true
OTP_EXPIRY_MINUTES=5
```

---

## Quick Production Fix Checklist

Run these commands in production:

```bash
# 1. Update database
php artisan tinker
>>> \App\Models\User::whereNull('require_2fa')->update(['require_2fa' => true]);
>>> exit

# 2. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 3. Verify OTP config
php artisan debug:otp

# 4. Test login and watch logs
tail -f storage/logs/laravel.log | grep OTP
```

---

## Emergency Debug Route

Add this TEMPORARY route to your production `routes/web.php`:

```php
// TEMPORARY DEBUG - REMOVE AFTER FIXING!
Route::get('/debug-otp-config', function() {
    $user = Auth::user();
    return response()->json([
        'environment' => app()->environment(),
        'otp_enabled_config' => config('auth.otp_enabled'),
        'otp_enabled_env' => env('OTP_ENABLED'),
        'user_id' => $user?->id,
        'user_email' => $user?->email,
        'user_require_2fa' => $user?->require_2fa,
        'user_otp_verified' => $user?->otp_verified,
        'session_driver' => config('session.driver'),
        'mail_host' => config('mail.mailers.smtp.host'),
    ]);
})->middleware('auth');
```

Then visit: `https://your-production-domain.com/debug-otp-config`

This will show you exactly what's configured in production.

**REMOVE THIS ROUTE AFTER DEBUGGING!**

---

## Still Not Working?

If after all these steps it still bypasses OTP:

1. **Copy production logs and send them:**
   ```bash
   tail -100 storage/logs/laravel.log | grep -A 5 -B 5 "OTP"
   ```

2. **Check exact database values:**
   ```sql
   SELECT id, email, require_2fa, otp_verified, otp_code, otp_expires_at 
   FROM users 
   WHERE email = 'your-email';
   ```

3. **Verify code version:**
   ```bash
   git log -1 --oneline
   # Should show: "Fixing the OTP" or similar recent commit
   ```

4. **Check if AuthController has been modified:**
   ```bash
   md5sum app/Http/Controllers/AuthController.php
   # Compare with local version
   ```

---

## Production vs Local Differences

| Check | Local | Production | Fix |
|-------|-------|------------|-----|
| APP_ENV | local | production | Normal |
| OTP_ENABLED | true | ??? | Add to prod .env |
| require_2fa in DB | 1 | null? | Run UPDATE query |
| Cached config | cleared | cached? | Run config:clear |
| Code version | latest | old? | git pull |
| Sessions table | exists | missing? | Run migration |

---

**Remember:** Production behaves differently from local!
- Config might be cached
- Database might have different data
- .env file is different
- Code might not be deployed

**Always check production-specific issues!**
