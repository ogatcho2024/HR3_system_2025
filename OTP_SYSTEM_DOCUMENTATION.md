# Laravel OTP System with Brevo SMTP

## Complete Implementation Documentation

This document describes the complete Laravel OTP (One-Time Password) system integrated with Brevo SMTP for email delivery.

---

## Table of Contents
1. [System Overview](#system-overview)
2. [Configuration](#configuration)
3. [Components](#components)
4. [Testing](#testing)
5. [Usage Flow](#usage-flow)
6. [Troubleshooting](#troubleshooting)

---

## System Overview

The OTP system provides two-factor authentication (2FA) for user logins. When a user logs in with valid credentials:

1. An OTP code is generated (6-digit number)
2. The code is sent to the user's email via Brevo SMTP
3. User must enter the OTP within 5 minutes
4. Upon successful verification, user gains access to the system

### Key Features
- ✅ 6-digit OTP codes
- ✅ 5-minute expiration
- ✅ Email delivery via Brevo SMTP
- ✅ Resend OTP functionality with 60-second cooldown
- ✅ Audit logging for all OTP events
- ✅ Beautiful HTML email template
- ✅ Role-based dashboard redirection

---

## Configuration

### 1. Environment Variables (.env)

```env
# Brevo SMTP Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your_brevo_smtp_key_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="MyApp OTP"
```

### 2. Database Schema

The system uses the following fields in the `users` table:

```sql
otp_code VARCHAR(6)           -- Stores the 6-digit OTP
otp_expires_at TIMESTAMP      -- Expiration time (5 minutes from generation)
otp_verified BOOLEAN          -- Whether user has verified OTP
require_2fa BOOLEAN           -- Whether user requires 2FA (default: true)
```

Migration file: `database/migrations/2026_01_11_014655_add_two_factor_fields_to_users_table.php`

---

## Components

### 1. Mailable Class: `OtpMail.php`

**Location:** `app/Mail/OtpMail.php`

**Purpose:** Defines the OTP email structure

```php
public function __construct($otp, $userName, $expiresAt)
{
    $this->otp = $otp;
    $this->userName = $userName;
    $this->expiresAt = $expiresAt;
}
```

**Subject:** "Your Login OTP Code - CaliCrane HR System"

### 2. Email Template

**Location:** `resources/views/emails/otp.blade.php`

**Features:**
- Professional HTML design with inline CSS
- Large, readable OTP code display
- Expiration timer
- Security instructions
- Responsive design

### 3. OTP Controller

**Location:** `app/Http/Controllers/OTPController.php`

**Methods:**

#### `showForm()`
- Displays OTP verification page
- Checks if user is authenticated
- Redirects if already verified

#### `verify(Request $request)`
- Validates the OTP code
- Checks expiration
- Logs verification attempts (success/failure)
- Redirects to appropriate dashboard

#### `resend()`
- Generates new OTP
- Updates expiration time
- Sends new email
- Returns JSON response

### 4. Auth Controller Integration

**Location:** `app/Http/Controllers/AuthController.php`

**OTP Generation Flow (in login method):**

```php
if ($user->require_2fa) {
    // Generate OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = Carbon::now()->addMinutes(5);
    
    // Save to database
    $user->update([
        'otp_code' => $otp,
        'otp_expires_at' => $expiresAt,
        'otp_verified' => false,
    ]);
    
    // Send email
    Mail::to($user->email)->send(new OtpMail(
        $otp,
        $user->name . ' ' . $user->lastname,
        $expiresAt
    ));
    
    return redirect()->route('otp.show');
}
```

### 5. Routes

**Location:** `routes/web.php`

```php
// OTP verification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/verify-otp', [OTPController::class, 'showForm'])->name('otp.show');
    Route::post('/verify-otp', [OTPController::class, 'verify'])->name('otp.verify');
    Route::post('/resend-otp', [OTPController::class, 'resend'])->name('otp.resend');
});
```

### 6. Middleware: Ensure2FAVerified

**Location:** `app/Http/Middleware/Ensure2FAVerified.php`

**Purpose:** Protects authenticated routes by ensuring OTP is verified

**Usage:**
```php
Route::middleware(['auth', '2fa'])->group(function () {
    // Protected routes
});
```

### 7. Audit Logging

**Service:** `app/Services/AuditLogService.php`

**Logged Events:**
- OTP generation (on login)
- OTP verification success
- OTP verification failure
- OTP expiration
- OTP resend requests

---

## Testing

### Test Brevo SMTP Configuration

Use the provided Artisan command:

```bash
php artisan test:brevo-email your-email@example.com
```

**Expected Output (Success):**
```
Testing Brevo SMTP configuration...
Sending test OTP email to: your-email@example.com

✓ Email sent successfully!
✓ Brevo SMTP is working correctly

Check your email inbox for the test OTP: 123456
```

**Expected Output (Failure):**
```
Testing Brevo SMTP configuration...
Sending test OTP email: your-email@example.com

✗ Failed to send email
Error: [error message]

Please check your Brevo SMTP credentials in .env file
```

### Manual Testing

1. **Test Login Flow:**
   ```
   1. Go to /login
   2. Enter valid credentials
   3. Check email for OTP
   4. Enter OTP on verification page
   5. Should redirect to dashboard
   ```

2. **Test OTP Expiration:**
   ```
   1. Login and receive OTP
   2. Wait 5+ minutes
   3. Try to verify expired OTP
   4. Should see "OTP has expired" error
   ```

3. **Test Resend Functionality:**
   ```
   1. On OTP verification page
   2. Click "Resend OTP"
   3. Check for new email
   4. Verify with new code
   ```

---

## Usage Flow

### Complete User Journey

```
1. User enters email/password
   ↓
2. System validates credentials
   ↓
3. If valid and require_2fa = true:
   ├─ Generate 6-digit OTP
   ├─ Set expiration (5 minutes)
   ├─ Save to database
   ├─ Send email via Brevo
   └─ Redirect to /verify-otp
   ↓
4. User receives email
   ↓
5. User enters OTP code
   ↓
6. System validates:
   ├─ Code matches? ✓
   ├─ Not expired? ✓
   └─ Mark as verified
   ↓
7. Redirect to dashboard
```

### Resend Flow

```
1. User clicks "Resend OTP"
   ↓
2. AJAX request to /resend-otp
   ↓
3. Check cooldown (60 seconds)
   ↓
4. If allowed:
   ├─ Generate new OTP
   ├─ Update expiration
   ├─ Send new email
   └─ Return success JSON
   ↓
5. Frontend updates UI
```

---

## Troubleshooting

### Issue: Emails Not Sending

**Solution:**
1. Verify Brevo credentials in `.env`
2. Check Brevo account status/limits
3. Clear config cache: `php artisan config:clear`
4. Check Laravel logs: `storage/logs/laravel.log`
5. Test SMTP: `php artisan test:brevo-email test@example.com`

### Issue: OTP Expired Immediately

**Solution:**
1. Check server timezone matches application timezone
2. Verify `config/app.php` timezone setting
3. Ensure Carbon is using correct timezone

### Issue: OTP Not Matching

**Possible Causes:**
- Special characters in OTP input (frontend strips non-digits)
- Database encoding issues
- OTP regenerated before user entered code

**Solution:**
- Check database `otp_code` field
- Verify no spaces/formatting in input
- Check audit logs for OTP generation events

### Issue: Redirect Loop

**Cause:** User trying to access dashboard without OTP verification

**Solution:**
- Ensure `Ensure2FAVerified` middleware is properly configured
- Check `otp_verified` field is set to true after successful verification
- Verify route middleware order

### Common Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| "OTP has expired" | More than 5 minutes passed | Request new OTP |
| "Invalid OTP" | Wrong code entered | Check email and re-enter |
| "Session expired" | User not authenticated | Login again |
| "Failed to send email" | SMTP configuration issue | Check Brevo credentials |

---

## Security Considerations

1. **OTP Storage:** Stored as plain text in database (acceptable for short-lived codes)
2. **Expiration:** 5-minute window reduces attack surface
3. **Single Use:** OTP is cleared after successful verification
4. **Audit Trail:** All attempts are logged
5. **Rate Limiting:** Consider adding rate limiting on OTP requests
6. **HTTPS:** Always use HTTPS in production for credential submission

---

## Production Checklist

Before deploying to production:

- [ ] Update `.env` with production Brevo credentials
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Enable HTTPS
- [ ] Configure proper `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`
- [ ] Test email delivery end-to-end
- [ ] Review audit log configuration
- [ ] Set up monitoring for failed email sends
- [ ] Configure proper session lifetime
- [ ] Enable rate limiting on OTP endpoints
- [ ] Review security headers
- [ ] Test with real user accounts

---

## API Reference

### POST /verify-otp

**Request:**
```json
{
  "otp": "123456"
}
```

**Success Response:**
- Redirects to dashboard
- Session updated with verification status

**Error Responses:**
- "Invalid OTP. Please try again."
- "OTP has expired. Please request a new code."

### POST /resend-otp

**Response (Success):**
```json
{
  "success": true,
  "message": "OTP has been resent to your email"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Failed to send OTP. Please try again."
}
```

---

## File Structure

```
HumanResources3/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── TestBrevoEmail.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   └── OTPController.php
│   │   └── Middleware/
│   │       └── Ensure2FAVerified.php
│   ├── Mail/
│   │   └── OtpMail.php
│   └── Services/
│       └── AuditLogService.php
├── database/
│   └── migrations/
│       └── 2026_01_11_014655_add_two_factor_fields_to_users_table.php
├── resources/
│   └── views/
│       ├── auth/
│       │   └── verify-otp.blade.php
│       └── emails/
│           └── otp.blade.php
└── .env
```

---

## Support & Maintenance

### Logs to Monitor
- `storage/logs/laravel.log` - Application errors
- Brevo Dashboard - Email delivery status
- `audit_logs` table - OTP verification attempts

### Metrics to Track
- OTP delivery success rate
- OTP verification success rate
- Average time to verify
- Failed verification attempts per user

---

## Credits

- **SMTP Provider:** Brevo (formerly Sendinblue)
- **Framework:** Laravel 11.x
- **Email Template:** Custom HTML/CSS design
- **Audit System:** Custom implementation

---

## Version History

- **v1.0** - Initial implementation with Brevo SMTP
- Database migration for 2FA fields
- OTP email template
- Verification flow
- Audit logging integration

---

**Last Updated:** January 20, 2026
**Maintained By:** Development Team
