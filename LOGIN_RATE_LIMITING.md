# Login Rate Limiting System

## Overview
The login rate limiting system protects your application from brute force attacks by temporarily blocking users after multiple failed login attempts.

## Features
- **Configurable attempt limits** (default: 3 attempts)
- **Configurable block duration** (default: 5 minutes)
- **IP and email-based tracking**
- **Automatic cleanup** of old records
- **User-friendly error messages**
- **Laravel's built-in rate limiter** as backup protection

## Configuration

### Environment Variables
Add these to your `.env` file:

```env
# Login Rate Limiting
LOGIN_MAX_ATTEMPTS=3        # Maximum failed attempts before blocking
LOGIN_BLOCK_DURATION=5      # Block duration in minutes
LOGIN_RESET_TIMEOUT=15      # Time to reset attempt counter in minutes
```

### Default Behavior
- After **3 failed attempts**, the account is blocked for **5 minutes**
- Attempts are reset after **15 minutes** of inactivity
- Both IP address and email are tracked independently

## How It Works

### 1. Failed Login Attempt
When a user enters wrong credentials:
1. System records the attempt with IP and email
2. Increments the attempt counter
3. If attempts ≥ max_attempts, user is blocked
4. User sees error message with remaining time

### 2. Successful Login
When a user logs in successfully:
1. All failed attempts for that IP/email are cleared
2. User gains normal access

### 3. Automatic Cleanup
Old login attempt records are automatically cleaned up to prevent database bloat.

## Database Structure

The `login_attempts` table tracks:
- `email` - User's email address
- `ip_address` - User's IP address
- `attempts` - Number of failed attempts
- `last_attempt` - Timestamp of last attempt
- `blocked_until` - When the block expires

## Testing

### Automated Testing
Run the test command:
```bash
php artisan test:login-throttling
```

### Manual Testing
1. Go to `/login`
2. Enter wrong credentials 3 times
3. Observe the rate limiting message
4. Wait 5 minutes or clear attempts manually

### Clear Attempts (for testing)
```bash
php artisan tinker
App\Models\LoginAttempt::clearAttempts('user@example.com', '127.0.0.1');
```

## Maintenance

### Clean Up Old Records
Run periodically (e.g., via cron):
```bash
php artisan login-attempts:cleanup
```

### Monitor Login Attempts
```sql
SELECT * FROM login_attempts ORDER BY last_attempt DESC;
```

## Security Features

### Multiple Layer Protection
1. **Database tracking** - Custom LoginAttempt model
2. **Laravel RateLimiter** - Built-in backup protection
3. **Middleware protection** - LoginThrottleMiddleware
4. **IP + Email tracking** - Dual-factor protection

### Attack Prevention
- **Brute Force Protection** - Limits password guessing
- **Distributed Attacks** - Tracks both IP and email
- **Account Enumeration** - Same error for invalid emails
- **Session Fixation** - Clears attempts on success

## Customization

### Adjust Blocking Rules
Modify `app/Models/LoginAttempt.php`:
```php
// Example: Block for 10 minutes after 5 attempts
$maxAttempts = config('auth.login_attempts.max_attempts', 5);
$blockDuration = config('auth.login_attempts.block_duration', 10);
```

### Custom Error Messages
Edit `resources/views/auth/login.blade.php`:
```html
@if ($errors->has('throttle'))
    <div class="alert alert-danger">
        Custom throttle message: {{ $errors->first('throttle') }}
    </div>
@endif
```

### Different Block Strategies
You can implement:
- **Progressive delays** (1min, 5min, 15min, etc.)
- **CAPTCHA after X attempts**
- **Email notifications** for admins
- **Geolocation-based rules**

## Troubleshooting

### Common Issues

**User stuck in blocked state:**
```bash
php artisan tinker
App\Models\LoginAttempt::where('email', 'user@example.com')->delete();
```

**Rate limiting not working:**
1. Check if migration ran: `php artisan migrate:status`
2. Verify middleware is registered in `bootstrap/app.php`
3. Check route has middleware: `php artisan route:list`

**Database connection errors:**
Ensure your `.env` has correct database credentials.

## Production Recommendations

### Performance
- Add indexes to `login_attempts` table (already included)
- Run cleanup command via cron job
- Consider Redis for rate limiting in high-traffic sites

### Security
- Enable HTTPS (`SESSION_SECURE_COOKIE=true`)
- Set `APP_DEBUG=false`
- Monitor failed attempts via logs
- Consider IP whitelisting for admins

### Monitoring
- Set up alerts for unusual login patterns
- Log blocked attempts for analysis
- Monitor cleanup command execution

## Files Modified/Created

### New Files
- `database/migrations/2025_01_25_141700_create_login_attempts_table.php`
- `app/Models/LoginAttempt.php`
- `app/Http/Middleware/LoginThrottleMiddleware.php`
- `app/Console/Commands/CleanupLoginAttempts.php`
- `app/Console/Commands/TestLoginThrottling.php`

### Modified Files
- `app/Http/Controllers/AuthController.php` - Added rate limiting logic
- `config/auth.php` - Added configuration options
- `bootstrap/app.php` - Registered middleware
- `routes/web.php` - Applied middleware to login route
- `resources/views/auth/login.blade.php` - Added error display
- `.env.example` - Added configuration examples

This comprehensive rate limiting system provides robust protection against brute force attacks while maintaining a good user experience.