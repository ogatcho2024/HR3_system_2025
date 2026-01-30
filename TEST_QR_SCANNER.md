# QR Scanner - Quick Test Guide

## 🚀 Quick Start Testing (5 minutes)

### Step 1: Open the Page
1. Navigate to the Attendance Time Tracking page
2. Make sure you're on `http://localhost` or `https://...` (not plain http://)
3. Open browser Developer Tools (F12) → Console tab

### Step 2: Open Scanner Modal
1. Click the **"Scan QR Code"** button (purple/indigo gradient)
2. **WATCH CONSOLE** - you should see:
   ```
   [QR Scanner] ========================================
   [QR Scanner] Modal component initialized
   [QR Scanner] Protocol: http:
   [QR Scanner] Hostname: localhost
   [QR Scanner] Html5Qrcode available: true
   [QR Scanner] ========================================
   ```

### Step 3: Initialize Cameras
1. Modal should automatically detect cameras
2. **WATCH CONSOLE** - you should see:
   ```
   [QR Scanner] ======== initializeCameras() called ========
   [QR Scanner] ✓ Html5Qrcode library loaded
   [QR Scanner] Requesting camera devices...
   [QR Scanner] ✓ Found 1 camera device(s): [...]
   [QR Scanner] ✓ Successfully loaded 1 camera(s)
   [QR Scanner] ✓ Auto-selected camera: abc123...
   ```
3. **Camera dropdown** should show your camera name(s)
4. **Status message** should show: "✓ Found 1 camera(s). Click "Start Scanner" to begin"
5. If browser asks for camera permission → **Allow**

### Step 4: Start Scanner
1. Click **"Start Scanner"** (green button)
2. **WATCH CONSOLE** - you should see:
   ```
   [QR Scanner] ======== START SCANNING clicked ========
   [QR Scanner] Selected camera ID: abc123...
   [QR Scanner] Creating Html5Qrcode instance for element #qr-reader...
   [QR Scanner] ✓ Html5Qrcode instance created
   [QR Scanner] Starting camera stream with config: {...}
   [QR Scanner] ✓✓✓ Camera started successfully! Scanning active.
   ```
3. **Camera preview** should appear in the black container
4. **Camera preview should STAY ON** (not flash and disappear)
5. "Start Scanner" button should hide
6. "Stop Scanner" (red) button should appear
7. Camera dropdown should be disabled
8. **Status message**: "✓ Scanner active - Ready to scan QR codes"

### Step 5: Scan a QR Code
1. Get an employee QR code (from /employee/qr-today page)
2. Hold it up to the camera
3. **WATCH CONSOLE** - when detected you should see:
   ```
   [QR Scanner] 📷 QR Code detected!
   [QR Scanner] 📷 QR Code scanned successfully!
   [QR Scanner] Decoded text: {"token":"...","emp_id":123,"date":"2026-01-30"}
   [QR Scanner] ✓ QR data parsed: {...}
   [QR Scanner] 📤 Sending attendance data to server: {...}
   [QR Scanner] Posting to: http://localhost/attendance/qr-scan
   [QR Scanner] Server response status: 200
   [QR Scanner] Server response data: {success: true, ...}
   [QR Scanner] ✓✓✓ Attendance logged successfully!
   ```
4. **Status message**: "✅ SUCCESS: Employee Name - IN at HH:MM:SS"
5. **Sound** should play (brief beep)
6. **Scanner keeps running** (ready for next scan)

### Step 6: Stop Scanner
1. Click **"Stop Scanner"** (red button)
2. **WATCH CONSOLE**:
   ```
   [QR Scanner] ======== STOP SCANNING called ========
   [QR Scanner] Stopping camera stream...
   [QR Scanner] ✓ Camera stream stopped
   [QR Scanner] Clearing scanner instance...
   [QR Scanner] ✓ Scanner instance cleared
   ```
3. Camera preview should clear (black box returns)
4. "Start Scanner" button reappears
5. Camera dropdown re-enabled

### Step 7: Close Modal
1. Try **ESC key** → Modal closes
2. Reopen modal, try **X button** → Modal closes
3. Reopen modal, try **clicking outside** → Modal closes
4. Each time, console should show cleanup logs

---

## ✅ Success Indicators

### Camera is Working When You See:
- ✅ Live camera preview in the container
- ✅ Console: "✓✓✓ Camera started successfully! Scanning active."
- ✅ Status: "✓ Scanner active - Ready to scan QR codes"
- ✅ Camera preview **stays on** (doesn't flicker/stop)
- ✅ Green "Start Scanner" button hidden
- ✅ Red "Stop Scanner" button visible

### Scan is Working When You See:
- ✅ Console: "📷 QR Code detected!"
- ✅ Console: "✓✓✓ Attendance logged successfully!"
- ✅ Status: "✅ SUCCESS: Name - IN/OUT at time"
- ✅ Sound plays
- ✅ Scanner keeps running

---

## ❌ Troubleshooting

### Camera Shows Briefly Then Stops
**What You'll See:**
- Camera flashes on then immediately goes black
- Console shows start logs but then stops

**Solution:** This was the original bug and should be FIXED now. If you still see this:
1. Hard refresh: Ctrl+Shift+R (or Cmd+Shift+R)
2. Clear browser cache
3. Check console for any JavaScript errors

### "HTTPS Required" Error
**What You'll See:**
```
⚠️ HTTPS is required for camera access. Please use https:// or localhost
```

**Solution:**
- Use `http://localhost` or `http://127.0.0.1` (both work)
- OR use HTTPS: `https://your-domain.com`
- Plain `http://192.168.x.x` or `http://your-ip` won't work

### "Camera Permission Denied"
**What You'll See:**
```
❌ Camera permission denied. Click the camera icon in your browser address bar to allow access.
```

**Solution:**
1. Look for camera icon in browser address bar
2. Click it and select "Allow"
3. Or go to browser settings → Site settings → Camera → Allow
4. Refresh page and try again

### "Camera is in use"
**What You'll See:**
```
❌ Camera is in use by another application. Please close other apps using the camera.
```

**Solution:**
1. Close Zoom, Teams, Skype, or other video apps
2. Close other browser tabs using camera
3. On Windows: Close Camera app
4. Try again

### "No cameras found"
**What You'll See:**
```
⚠️ No cameras detected. Please check device permissions.
```

**Solution:**
1. Connect a webcam (if using desktop)
2. Check Device Manager (Windows) or System Preferences (Mac) that camera is recognized
3. Grant camera permission in browser settings
4. Refresh page

### "Invalid QR code format"
**What You'll See:**
```
❌ Invalid QR code format. Please use a valid attendance QR code.
```

**Solution:**
1. Make sure you're scanning the QR code from `/employee/qr-today` page
2. QR must contain JSON: `{"token":"...","emp_id":123,"date":"2026-01-30"}`
3. QR must be for TODAY (date in QR must match current date)
4. Don't scan random QR codes - only employee attendance QR codes

### "Network error"
**What You'll See:**
```
❌ Network error - please check connection and try again
```

**Solution:**
1. Make sure Laravel backend is running
2. Check route exists: `php artisan route:list | grep qr-scan`
3. Check network tab in DevTools for failed request
4. Verify CSRF token in page: `<meta name="csrf-token" content="...">`

---

## 🔍 Console Log Cheat Sheet

### Good Logs (Success):
```
[QR Scanner] ✓ Html5Qrcode library loaded
[QR Scanner] ✓ Found X camera device(s)
[QR Scanner] ✓ Successfully loaded X camera(s)
[QR Scanner] ✓ Html5Qrcode instance created
[QR Scanner] ✓✓✓ Camera started successfully!
[QR Scanner] 📷 QR Code detected!
[QR Scanner] ✓ QR data parsed
[QR Scanner] ✓✓✓ Attendance logged successfully!
[QR Scanner] ✓ Camera stream stopped
[QR Scanner] ✓ Scanner instance cleared
```

### Bad Logs (Errors):
```
[QR Scanner] ✗ HTTPS required
[QR Scanner] ✗ Html5Qrcode library not loaded
[QR Scanner] ✗ Camera select element not found
[QR Scanner] ✗ Error during camera initialization
[QR Scanner] ✗✗✗ Error starting scanner
[QR Scanner] ✗ Invalid QR code format
[QR Scanner] ✗ CSRF token not found
[QR Scanner] ✗ Error stopping scanner
[QR Scanner] ✗ Could not find parent component
```

### Warning Logs:
```
[QR Scanner] ⚠️ No cameras detected
[QR Scanner] ⚠️ No camera selected
[QR Scanner] ⚠️ Already scanning
[QR Scanner] ⏱️ Scan cooldown active
[QR Scanner] ⏱️ Already processing a scan
```

---

## 📋 Quick Checklist

Before testing:
- [ ] Laravel backend running (`php artisan serve`)
- [ ] Using localhost or HTTPS
- [ ] Camera connected and working
- [ ] Browser DevTools console open
- [ ] Test employee QR code ready

During test:
- [ ] Modal opens
- [ ] Cameras detected
- [ ] Start Scanner works
- [ ] Camera preview stays on
- [ ] QR code scans successfully
- [ ] Attendance logs to backend
- [ ] Success message shown
- [ ] Stop Scanner works
- [ ] Modal closes properly

---

## 🎯 Expected Full Flow

```
User Action                  →  What You See                           →  Console Log
────────────────────────────────────────────────────────────────────────────────────────
Click "Scan QR Code"        →  Modal opens                            →  [QR Scanner] Modal component initialized
                               Camera dropdown shows "Loading..."        [QR Scanner] Protocol: http:
                                                                         [QR Scanner] Html5Qrcode available: true

(Automatic)                 →  Cameras detected                       →  [QR Scanner] ✓ Html5Qrcode library loaded
                               Dropdown shows camera names               [QR Scanner] ✓ Found 1 camera device(s)
                               Status: "✓ Found 1 camera(s)..."          [QR Scanner] ✓ Successfully loaded 1 camera(s)

Click "Start Scanner"       →  Camera preview appears                 →  [QR Scanner] ======== START SCANNING clicked
                               Button changes to "Stop Scanner"          [QR Scanner] ✓ Html5Qrcode instance created
                               Status: "✓ Scanner active"                [QR Scanner] ✓✓✓ Camera started successfully!

Show QR code to camera      →  Status: "📷 QR Code detected!"         →  [QR Scanner] 📷 QR Code detected!
                                                                         [QR Scanner] ✓ QR data parsed
                                                                         [QR Scanner] 📤 Sending attendance data

(Wait for server)           →  Status: "✅ SUCCESS: Name - IN at..."  →  [QR Scanner] Server response status: 200
                               Sound plays                               [QR Scanner] ✓✓✓ Attendance logged successfully!
                               Scanner keeps running

Click "Stop Scanner"        →  Camera preview clears                  →  [QR Scanner] ======== STOP SCANNING called
                               Button changes to "Start Scanner"         [QR Scanner] ✓ Camera stream stopped
                                                                         [QR Scanner] ✓ Scanner instance cleared

Press ESC (or click X)      →  Modal closes                           →  [QR Scanner] ======== closeScanner() called
                                                                         [QR Scanner] Closing modal via parent...
```

---

## 🐛 Report Issues

If scanner still doesn't work after this fix, report with:

1. **Browser & Version**: Chrome 131, Firefox 124, etc.
2. **Operating System**: Windows 11, macOS 14, etc.
3. **Full Console Log**: Copy entire console output
4. **What You See**: Camera shows? Flickers? Black screen?
5. **Error Message**: Exact text from status message
6. **Steps**: What did you click before it failed?

---

**GOOD LUCK! The scanner should now WORK and PERSIST! 🎉**
