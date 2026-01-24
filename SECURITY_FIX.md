# 🚨 CRITICAL SECURITY FIX - .env Publicly Accessible

## Problem
Your `.env` file is accessible via browser, exposing:
- Database credentials
- API keys (Brevo SMTP password)
- APP_KEY
- All sensitive configuration

---

## Immediate Actions Taken

### 1. Updated Root `.htaccess`
Added blocking rules for sensitive files:
- `.env` and `.env.*`
- `.git/` directory
- `/storage/`, `/vendor/`, `/bootstrap/cache/`, `/database/`
- `composer.json`, `composer.lock`
- `artisan`, `phpunit.xml`

### 2. Test If Fixed
Try accessing: `https://your-domain.com/.env`

**Should see:** 403 Forbidden error
**If you still see the file:** Continue to Step 3

---

## Step 3: Configure Apache Document Root (REQUIRED!)

Your Apache virtual host should point to the `public/` folder, NOT the root folder.

### For cPanel/Shared Hosting:
1. Log into your hosting control panel
2. Find "Document Root" or "Web Root" settings
3. Change from: `/home/user/HumanResources3/`
4. Change to: `/home/user/HumanResources3/public/`
5. Save and restart Apache

### For VPS/Dedicated Server:
Edit your Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    
    # CORRECT - Point to public folder
    DocumentRoot /path/to/HumanResources3/public
    
    <Directory /path/to/HumanResources3/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Optional: Block direct access to parent directories
    <DirectoryMatch "^/path/to/HumanResources3/(storage|vendor|bootstrap|database|\.git)">
        Require all denied
    </DirectoryMatch>
</VirtualHost>
```

Then restart Apache:
```bash
sudo systemctl restart apache2
# or
sudo service httpd restart
```

### For XAMPP (Local Development):
Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName hr.local
    DocumentRoot "C:/xampp/htdocs/dashboard/HumanResources3/public"
    
    <Directory "C:/xampp/htdocs/dashboard/HumanResources3/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add to `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 hr.local
```

Restart XAMPP Apache.

---

## Step 4: Verify Protection

Test these URLs - all should return 403 Forbidden:

1. `https://your-domain.com/.env`
2. `https://your-domain.com/.env.example`
3. `https://your-domain.com/.git/config`
4. `https://your-domain.com/composer.json`
5. `https://your-domain.com/storage/logs/laravel.log`
6. `https://your-domain.com/database/database.sqlite`

Only this should work:
- `https://your-domain.com/` (your app)

---

## Step 5: Rotate Compromised Credentials (CRITICAL!)

Since your `.env` was publicly accessible, assume ALL credentials are compromised:

### A. Change APP_KEY
```bash
php artisan key:generate --force
```

### B. Change Database Password
1. Log into your database admin panel
2. Change the database password
3. Update `DB_PASSWORD` in `.env`

### C. Regenerate Brevo API Key
1. Log into Brevo dashboard
2. Go to SMTP & API → API Keys
3. Delete the compromised key
4. Generate a new key
5. Update `MAIL_PASSWORD` in `.env`

### D. Change Any Other API Keys
Review your `.env` and rotate:
- Payment gateway keys
- Third-party API keys
- OAuth secrets

---

## Step 6: Additional Security Measures

### A. Add to `.gitignore` (verify it's there)
```
/.env
/.env.backup
/.env.production
```

### B. Check Git History
```bash
git log --all --full-history -- .env
```

If `.env` was committed, remove it:
```bash
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch .env" \
  --prune-empty --tag-name-filter cat -- --all

git push origin --force --all
```

### C. Set Proper File Permissions (Linux/VPS)
```bash
chmod 644 .env
chmod -R 755 storage bootstrap/cache
```

### D. Add Security Headers (public/.htaccess)
Add to your `public/.htaccess` after `<IfModule mod_rewrite.c>`:

```apache
<IfModule mod_headers.c>
    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "no-referrer-when-downgrade"
</IfModule>
```

---

## Step 7: Monitor for Unauthorized Access

### Check Apache access logs:
```bash
grep ".env" /var/log/apache2/access.log
# or for cPanel
grep ".env" /home/user/access-logs/your-domain.com
```

Look for 200 responses (successful access) - those IPs accessed your `.env`.

### Check for database breaches:
```sql
-- Look for suspicious admin accounts created recently
SELECT * FROM users WHERE role = 'Admin' ORDER BY created_at DESC LIMIT 10;

-- Check for unusual login activity
SELECT * FROM audit_logs WHERE action_type = 'login' ORDER BY created_at DESC LIMIT 50;
```

---

## Verification Checklist

- [ ] `.env` returns 403 Forbidden when accessed via browser
- [ ] Apache DocumentRoot points to `/public/` folder
- [ ] APP_KEY has been regenerated
- [ ] Database password has been changed
- [ ] Brevo SMTP key has been regenerated
- [ ] `.env` is in `.gitignore`
- [ ] Git history doesn't contain `.env`
- [ ] File permissions are correct (644 for .env)
- [ ] Security headers are added
- [ ] Access logs checked for suspicious activity
- [ ] Database checked for unauthorized changes

---

## Why This Happened

Laravel expects Apache DocumentRoot to point to the `public/` folder. When it points to the root folder instead:
- The root `.htaccess` tries to redirect to `/public/`
- BUT direct file requests (like `.env`) bypass the rewrite rules
- Result: Sensitive files are exposed

**The ONLY proper fix:** Point Apache DocumentRoot to `public/` folder.

---

## Emergency Contact

If you believe your database was breached:
1. Take the site offline immediately (maintenance mode)
2. Change ALL passwords
3. Review audit logs for suspicious activity
4. Consider restoring from a backup before the breach
5. Notify affected users if data was compromised

```bash
# Put site in maintenance mode
php artisan down --secret="your-secret-bypass-token"

# Access site as admin with:
# https://your-domain.com/your-secret-bypass-token
```

---

**PRIORITY: Fix Apache DocumentRoot first, then rotate all credentials!**
