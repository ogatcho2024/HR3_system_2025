# SMTP Configuration Guide for OTP Email

## Overview
The OTP (One-Time Password) system requires a working email configuration to send verification codes to users.

## Configuration Steps

### 1. Update `.env` File

Replace the mail configuration in your `.env` file with one of the following setups:

---

### Option A: Gmail (Recommended for Testing)

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="CaliCrane HR System"
```

#### Gmail Setup Instructions:
1. **Enable 2-Step Verification** on your Google account
2. **Generate an App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" as the app
   - Select "Other" as the device
   - Name it "CaliCrane HR System"
   - Copy the 16-character password
3. **Use the App Password** (not your regular password) in `MAIL_PASSWORD`

---

### Option B: Outlook/Office365

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@outlook.com"
MAIL_FROM_NAME="CaliCrane HR System"
```

---

### Option C: Yahoo Mail

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yahoo.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@yahoo.com"
MAIL_FROM_NAME="CaliCrane HR System"
```

---

### Option D: Mailtrap (For Testing - No Real Emails Sent)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@calicrane.com"
MAIL_FROM_NAME="CaliCrane HR System"
```

**Mailtrap Setup:**
1. Sign up at https://mailtrap.io (free account available)
2. Get your SMTP credentials from the inbox settings
3. All emails will be captured in Mailtrap (won't reach real inboxes)

---

### Option E: SendGrid (Production Recommended)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="CaliCrane HR System"
```

---

### 2. Clear Configuration Cache

After updating `.env`, run:

```bash
php artisan config:clear
php artisan cache:clear
```

---

### 3. Test Email Configuration

Create a test route or use Tinker to test:

```bash
php artisan tinker
```

Then run:

```php
Mail::raw('Test email from CaliCrane', function ($message) {
    $message->to('test@example.com')
            ->subject('Test Email');
});
```

---

## Verification Checklist

- [ ] `.env` file updated with correct SMTP credentials
- [ ] Configuration cache cleared
- [ ] Test email sent successfully
- [ ] Login with valid credentials
- [ ] OTP received in email inbox (check spam folder)
- [ ] OTP verification works correctly

---

## Troubleshooting

### Issue: "Connection refused"
**Solution:** Check firewall settings and ensure port 587/465 is not blocked.

### Issue: "Authentication failed"
**Solution:** 
- Verify username and password are correct
- For Gmail, ensure you're using an App Password, not your regular password
- Check if 2FA is enabled on your email account

### Issue: "Email not received"
**Solution:**
- Check spam/junk folder
- Verify `MAIL_FROM_ADDRESS` is correct
- Check Laravel logs: `storage/logs/laravel.log`
- Ensure user email in database is valid

### Issue: "SSL certificate problem"
**Solution:** Add to `.env`:
```env
MAIL_VERIFY_PEER=false
```

---

## Log Files

Check these files for debugging:
- `storage/logs/laravel.log` - Application logs
- Look for "OTP sent successfully" or error messages

---

## Security Notes

1. **Never commit `.env` to version control**
2. Use App Passwords instead of regular passwords
3. In production, use professional email services (SendGrid, Mailgun, AWS SES)
4. Consider using queue workers for email sending in production
5. Monitor email sending rates to avoid being flagged as spam

---

## Production Recommendations

For production environments:

1. **Use a dedicated email service** (SendGrid, Mailgun, AWS SES, Postmark)
2. **Enable email queues** to improve performance
3. **Set up SPF, DKIM, and DMARC** records for your domain
4. **Monitor email deliverability** and bounce rates
5. **Implement rate limiting** on OTP requests

---

## Current Implementation Features

✅ OTP stored in database before sending
✅ 5-minute expiration time included in email
✅ Beautiful HTML email template
✅ User name personalization
✅ Security notices included
✅ Comprehensive error logging
✅ Resend functionality with 60-second cooldown

---

For additional help, check Laravel Mail documentation:
https://laravel.com/docs/11.x/mail
