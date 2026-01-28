# Quick Setup - QR Attendance System

## Run These Commands (In Order)

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Generate QR Secrets for Existing Employees
```bash
php artisan qr:generate-secrets
```

### 3. Verify Routes (Optional)
```bash
php artisan route:list | findstr qr
```

## Access URLs

### For Employees
- **My QR Code**: http://localhost/dashboard/HumanResources3/public/employee/qr-today

### For Admin/Staff/Super Admin
- **QR Scanner**: http://localhost/dashboard/HumanResources3/public/attendance/scanner

## Test Flow

1. **Login as Employee** → Go to "My QR Today" → Display QR code
2. **Login as Admin/Staff** → Go to Scanner → Click "Start Scanner"
3. **Scan Employee QR** → Should log TIME-IN
4. **Wait 5 minutes** → Scan again → Should log TIME-OUT
5. **Try 3rd scan** → Should be rejected (max 2 logs/day)

## Key Features

✅ Daily QR codes (expire at midnight)  
✅ Auto IN/OUT detection  
✅ 5-minute cooldown between scans  
✅ Max 2 logs per day  
✅ Role-based access (Admin/Staff can scan, Employees cannot)  
✅ Real-time webcam scanning  
✅ Audit trail with IP and user agent  

## Troubleshooting

**QR Code Not Showing?**
```bash
php artisan qr:generate-secrets --force
```

**Camera Not Working?**
- Allow camera permissions in browser
- Use HTTPS (or localhost exception)
- Try Chrome/Edge browser

**403 Error on Scanner?**
- Verify user is Admin, Staff, or Super Admin
- Check `account_type` column in users table

## Support

See `QR_ATTENDANCE_IMPLEMENTATION.md` for full documentation.
