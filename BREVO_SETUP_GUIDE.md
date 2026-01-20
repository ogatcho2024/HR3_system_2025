# Brevo SMTP Setup Guide

## Complete Setup Instructions for Laravel OTP System

---

## Step 1: Get Brevo SMTP Credentials

### Option A: Using Existing Brevo Account

1. **Login to Brevo**
   - Go to https://app.brevo.com/
   - Login with your credentials

2. **Navigate to SMTP Settings**
   - Click on your name (top right)
   - Select "SMTP & API"
   - Click on "SMTP" tab

3. **Get Your SMTP Credentials**
   ```
   SMTP Server: smtp-relay.brevo.com
   Port: 587 (TLS) or 465 (SSL)
   Login: Your email address OR your Brevo login email
   Password: Your SMTP Key (NOT your account password)
   ```

4. **Generate SMTP Key (if not already done)**
   - Click "Generate a new SMTP key"
   - Give it a name (e.g., "Laravel OTP System")
   - Copy the key immediately (you won't see it again!)
   - Format: `xsmtpsib-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-xxxxxxxxxxxxxxxxxx`

### Option B: Create New Brevo Account

1. Go to https://www.brevo.com/
2. Click "Sign up free"
3. Complete registration
4. Verify your email address
5. Follow "Option A" steps above to get credentials

---

## Step 2: Verify Sender Email Address

**Important:** Brevo requires you to verify the sender email address before sending emails.

1. **In Brevo Dashboard:**
   - Go to "Senders" section
   - Click "Add a sender"
   - Enter the email address (e.g., gdelmonte261@gmail.com)
   - Brevo will send a verification email
   - Click the verification link in that email

2. **Wait for Verification:**
   - Status will change from "Pending" to "Verified"
   - This usually takes a few minutes
   - You CANNOT send emails until verified

---

## Step 3: Configure Laravel .env File

Update your `.env` file with the correct Brevo credentials:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=YOUR_BREVO_LOGIN_EMAIL
MAIL_PASSWORD=YOUR_SMTP_KEY_HERE
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=YOUR_VERIFIED_SENDER_EMAIL
MAIL_FROM_NAME="MyApp OTP"
```

### Important Notes:

- **MAIL_USERNAME:** This is your Brevo login email (the one you use to login to Brevo dashboard)
- **MAIL_PASSWORD:** This is the SMTP key you generated (NOT your Brevo account password)
- **MAIL_FROM_ADDRESS:** This MUST be a verified sender email in your Brevo account
- **MAIL_PORT:** Use 587 for TLS or 465 for SSL (587 is recommended)

### Example Configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=youremail@example.com
MAIL_PASSWORD=your_brevo_smtp_key_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=youremail@example.com
MAIL_FROM_NAME="MyApp OTP"
```

---

## Step 4: Clear Laravel Cache

After updating `.env`, clear the configuration cache:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Step 5: Test Email Sending

Use the provided test command:

```bash
php artisan test:brevo-email your-verified-email@example.com
```

### Expected Success Output:
```
Testing Brevo SMTP configuration...
Sending test OTP email to: your-verified-email@example.com

✓ Email sent successfully!
✓ Brevo SMTP is working correctly

Check your email inbox for the test OTP: 123456
```

### If You Get Errors:

#### Error: "Authentication failed"
**Possible Causes:**
- Wrong SMTP key (password)
- Wrong login email (username)
- Copy/paste errors with extra spaces

**Solution:**
1. Go back to Brevo dashboard
2. Generate a NEW SMTP key
3. Copy it carefully (no extra spaces)
4. Update `.env` file
5. Run `php artisan config:clear`
6. Test again

#### Error: "Sender address not verified"
**Solution:**
1. Go to Brevo dashboard → Senders
2. Verify your sender email address
3. Wait for verification to complete
4. Make sure `MAIL_FROM_ADDRESS` matches the verified email

#### Error: "Connection timed out"
**Possible Causes:**
- Firewall blocking port 587
- ISP blocking SMTP
- Incorrect MAIL_HOST

**Solution:**
1. Check firewall settings
2. Try port 465 with SSL instead:
   ```env
   MAIL_PORT=465
   MAIL_ENCRYPTION=ssl
   ```
3. Contact your ISP if using port 587 fails

---

## Step 6: Brevo Account Limits

### Free Plan Limits:
- **Daily Limit:** 300 emails per day
- **Monthly Limit:** 9,000 emails per month
- **Max Recipients:** Unlimited (but spread across your daily limit)

### If You Need More:
- Upgrade to a paid plan in Brevo dashboard
- Plans start at $25/month for 20,000 emails

### Monitor Usage:
- Check Brevo dashboard for real-time statistics
- Set up alerts for approaching limits

---

## Step 7: Production Considerations

### 1. Use Environment-Specific Credentials

Create separate SMTP keys for development and production:

**Development:**
- Name: "Dev - Laravel OTP"
- Use for testing

**Production:**
- Name: "Prod - Laravel OTP"
- Use for live system

### 2. Set Up Email Monitoring

In Brevo Dashboard:
- Go to Statistics → Email
- Monitor:
  - Delivery rate
  - Bounce rate
  - Open rate (if tracking enabled)
  - Click rate (if tracking enabled)

### 3. Configure SPF and DKIM

To improve deliverability:

1. **SPF Record:**
   Add to your domain's DNS:
   ```
   v=spf1 include:spf.brevo.com ~all
   ```

2. **DKIM:**
   - Brevo provides DKIM automatically
   - Verify in Brevo dashboard → Senders → Domain settings

3. **Custom Domain (Optional):**
   - Use your own domain for sender address
   - Requires DNS configuration
   - Better for professional appearance

---

## Step 8: Troubleshooting Common Issues

### Issue 1: "Could not connect to SMTP host"

**Symptoms:**
```
Failed to send email
Error: Connection could not be established with host smtp-relay.brevo.com
```

**Solutions:**
1. Check internet connection
2. Verify MAIL_HOST is correct: `smtp-relay.brevo.com`
3. Try alternative ports (587 or 465)
4. Check if antivirus/firewall is blocking
5. Contact hosting provider if on shared hosting

### Issue 2: OTP Emails in Spam Folder

**Solutions:**
1. Verify SPF and DKIM records
2. Use a verified domain (not free email providers)
3. Warm up your sending reputation (start with small volumes)
4. Ask recipients to whitelist your email
5. Include unsubscribe link (best practice)

### Issue 3: Emails Not Arriving

**Checklist:**
- [ ] Sender email verified in Brevo?
- [ ] Within daily sending limit?
- [ ] Check Brevo dashboard for delivery status
- [ ] Check spam/junk folder
- [ ] Correct recipient email address?
- [ ] Laravel logs for errors: `storage/logs/laravel.log`

### Issue 4: Slow Email Delivery

**Normal Delays:**
- 0-30 seconds: Normal
- 30-60 seconds: Acceptable during high load
- 60+ seconds: Investigate

**Solutions:**
1. Use Laravel queues for async sending:
   ```env
   QUEUE_CONNECTION=database
   ```
2. Run queue worker:
   ```bash
   php artisan queue:work
   ```
3. Update AuthController to queue emails:
   ```php
   Mail::to($user->email)->queue(new OtpMail(...));
   ```

---

## Step 9: Advanced Configuration

### Queueing OTP Emails (Recommended for Production)

1. **Update .env:**
   ```env
   QUEUE_CONNECTION=database
   ```

2. **Run migrations:**
   ```bash
   php artisan queue:table
   php artisan migrate
   ```

3. **Update mail sending to use queues:**
   ```php
   Mail::to($user->email)->queue(new OtpMail(...));
   ```

4. **Run queue worker:**
   ```bash
   php artisan queue:work
   ```

5. **For production, use supervisor to keep queue worker running**

### Custom Email Templates

Customize the OTP email template:

**File:** `resources/views/emails/otp.blade.php`

**Available Variables:**
- `$otp` - The 6-digit OTP code
- `$userName` - User's full name
- `$expiresAt` - Expiration timestamp
- `$expiryMinutes` - Minutes until expiration

---

## Step 10: Testing Checklist

Before going live, test the following:

### Functional Tests:
- [ ] Login with valid credentials
- [ ] Receive OTP email within 30 seconds
- [ ] Email appears in inbox (not spam)
- [ ] OTP code is clearly visible
- [ ] Enter correct OTP → successfully logged in
- [ ] Enter wrong OTP → shows error message
- [ ] Wait 5+ minutes → OTP expires
- [ ] Click "Resend OTP" → receive new code
- [ ] New OTP works correctly
- [ ] Old OTP no longer works

### Edge Cases:
- [ ] Multiple resend requests in quick succession
- [ ] Login from different devices simultaneously
- [ ] Special characters in user names display correctly
- [ ] Email works with long user names
- [ ] System handles Brevo API downtime gracefully

### Performance Tests:
- [ ] Multiple users can receive OTPs simultaneously
- [ ] Email delivery under 30 seconds
- [ ] No memory leaks with queue worker
- [ ] Audit logs created correctly

---

## Step 11: Monitoring & Maintenance

### Daily Checks:
1. Check Brevo dashboard for delivery rates
2. Review Laravel logs for errors
3. Monitor audit logs for failed OTP attempts

### Weekly Checks:
1. Review Brevo usage vs limits
2. Check bounce rates
3. Analyze OTP verification success rates

### Monthly Checks:
1. Review and clean old audit logs
2. Update Brevo SMTP keys (security best practice)
3. Review email template effectiveness

---

## Alternative SMTP Providers

If Brevo doesn't work for you, consider:

### 1. SendGrid
```env
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
```

### 2. Mailgun
```env
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mailgun_username
MAIL_PASSWORD=your_mailgun_password
```

### 3. Amazon SES
```env
MAIL_HOST=email-smtp.us-east-1.amazonaws.com
MAIL_PORT=587
MAIL_USERNAME=your_ses_smtp_username
MAIL_PASSWORD=your_ses_smtp_password
```

---

## Support Resources

### Brevo Support:
- Help Center: https://help.brevo.com/
- Email: support@brevo.com
- API Docs: https://developers.brevo.com/

### Laravel Mail:
- Documentation: https://laravel.com/docs/mail
- Community: https://laracasts.com/

### This Project:
- Check `OTP_SYSTEM_DOCUMENTATION.md` for complete system documentation
- Check Laravel logs: `storage/logs/laravel.log`
- Check audit logs in database: `audit_logs` table

---

## Quick Reference

### Test Email Command:
```bash
php artisan test:brevo-email your-email@example.com
```

### Clear Cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### Check Logs:
```bash
tail -f storage/logs/laravel.log
```

### Queue Worker:
```bash
php artisan queue:work
```

---

**Last Updated:** January 20, 2026
