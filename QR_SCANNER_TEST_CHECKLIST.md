# QR Code Attendance Scanner - Test Checklist

## Implementation Summary

### ✅ What Was Added

1. **"Scan QR Code" Button**
   - Location: Clock In/Out tab → Search bar section
   - Prominent purple gradient button with QR icon
   - Opens modal scanner when clicked

2. **QR Scanner Modal**
   - Full-screen modal with camera preview
   - Camera selection dropdown
   - Start/Stop scanner controls
   - Real-time status messages
   - HTTPS/localhost detection with warnings
   - User-friendly error handling

3. **Camera Integration**
   - Uses html5-qrcode library (v2.3.8)
   - Automatic camera detection via getUserMedia API
   - Permission request handling
   - Multiple camera support

4. **Security & Reliability**
   - CSRF token protection on all AJAX requests
   - 3-second debouncing to prevent duplicate scans
   - HTTPS requirement validation
   - Token validation on backend
   - Cooldown enforcement (5 minutes backend)

5. **Backend Integration**
   - Endpoint: `POST /attendance/qr-scan`
   - Controller: `QrAttendanceController@processScan`
   - Auto-detects TIME-IN / TIME-OUT
   - Validates daily QR tokens
   - Updates attendance records

---

## Test Checklist

### Prerequisites
- [ ] HTTPS enabled OR testing on `localhost` / `127.0.0.1`
- [ ] Modern browser (Chrome 87+, Edge 88+, Firefox 85+)
- [ ] Webcam connected and functional
- [ ] Employee with generated QR code (from "My QR Code" page)

---

### Test 1: Button Visibility
**Goal**: Verify "Scan QR Code" button is visible and functional

1. [ ] Navigate to **Attendance & Time Tracking**
2. [ ] Click on **Clock In/Out** tab
3. [ ] Verify purple "Scan QR Code" button appears next to "Manual Entry"
4. [ ] Button should have QR icon and gradient purple/indigo background
5. [ ] Click button → Modal should open

**Expected**: Modal opens with camera selector and controls

---

### Test 2: Camera Permission Flow
**Goal**: Test camera permission handling in different scenarios

#### 2A: First-Time Permission Request
1. [ ] Open QR scanner modal
2. [ ] Select a camera from dropdown
3. [ ] Click "Start Scanner"
4. [ ] Browser should prompt for camera permission
5. [ ] Click "Allow"

**Expected**: 
- Camera preview starts in modal
- Status: "Scanner active - Ready to scan QR codes" (blue)
- Start button hides, Stop button appears

#### 2B: Permission Denied
1. [ ] Open QR scanner modal
2. [ ] Click "Start Scanner"
3. [ ] Click "Block" or "Deny" on browser permission prompt

**Expected**:
- Error message: "Camera permission denied..."
- Status displayed in red box
- Instructions on how to grant permission

#### 2C: No Camera Found
1. [ ] Disconnect/disable all cameras
2. [ ] Open QR scanner modal
3. [ ] Dropdown should show "No cameras found"

**Expected**:
- Warning status message
- Helpful message about checking camera connection

---

### Test 3: HTTPS Requirement
**Goal**: Verify HTTPS enforcement

#### 3A: On HTTP (Not Localhost)
1. [ ] Access site via `http://` (e.g., `http://hr3.cranecali-ms.com`)
2. [ ] Open QR scanner modal

**Expected**:
- Yellow warning banner: "HTTPS Required: Camera access requires..."
- Starting scanner shows error if attempted

#### 3B: On HTTPS
1. [ ] Access site via `https://`
2. [ ] Open QR scanner modal

**Expected**:
- No HTTPS warning
- Scanner works normally

#### 3C: On Localhost
1. [ ] Access site via `http://localhost` or `http://127.0.0.1`
2. [ ] Open QR scanner modal

**Expected**:
- No HTTPS warning (localhost exception)
- Scanner works normally

---

### Test 4: QR Code Scanning
**Goal**: Test successful attendance logging

#### 4A: First Scan (TIME-IN)
1. [ ] Start scanner
2. [ ] Have employee open their "My QR Code" page
3. [ ] Show QR code to camera

**Expected**:
- Green success message: "✓ [Employee Name] - IN at [time]"
- Attendance record created in database
- Clock In time recorded
- Status updated to "present"

#### 4B: Second Scan (TIME-OUT)
1. [ ] Wait 5+ minutes (cooldown)
2. [ ] Scan same employee's QR code again

**Expected**:
- Green success message: "✓ [Employee Name] - OUT at [time]"
- Clock Out time recorded
- Status remains "present"

---

### Test 5: Error Handling
**Goal**: Test various error scenarios

#### 5A: Invalid QR Code
1. [ ] Show any non-attendance QR code (e.g., website URL)

**Expected**:
- Red error: "Invalid QR code format..."

#### 5B: Expired QR Code
1. [ ] Use QR code from previous day

**Expected**:
- Error: "QR code is not valid for today..."

#### 5C: Cooldown Violation
1. [ ] Scan QR code
2. [ ] Immediately scan again (within 3 seconds)

**Expected**:
- No second submission (debounced)
- First scan processes normally

#### 5D: Backend Cooldown
1. [ ] Scan QR code successfully
2. [ ] Wait 1 minute
3. [ ] Scan same QR again

**Expected**:
- Error: "Please wait 5 minutes between scans" (backend enforced)

#### 5E: Network Error
1. [ ] Disconnect internet
2. [ ] Scan QR code

**Expected**:
- Red error: "Network error - please try again"

#### 5F: Camera Already in Use
1. [ ] Open camera in another app/tab
2. [ ] Try to start scanner

**Expected**:
- Error: "Camera is already in use by another application"

---

### Test 6: Multiple Cameras
**Goal**: Test camera switching

1. [ ] Connect multiple cameras (if available)
2. [ ] Open scanner modal
3. [ ] Dropdown should list all cameras
4. [ ] Select different camera
5. [ ] Click "Start Scanner"

**Expected**:
- Selected camera activates
- Can switch cameras between scans

---

### Test 7: Stop Scanner
**Goal**: Test scanner cleanup

1. [ ] Start scanner
2. [ ] Click "Stop Scanner" button

**Expected**:
- Camera preview stops
- Camera light turns off
- Status: "Scanner stopped"
- Stop button hides, Start button appears

---

### Test 8: Modal Close
**Goal**: Test modal cleanup

#### 8A: Close via X Button
1. [ ] Start scanner
2. [ ] Click X button in header

**Expected**:
- Camera stops
- Modal closes
- Background overlay disappears

#### 8B: Close via Background Click
1. [ ] Start scanner
2. [ ] Click dark overlay outside modal

**Expected**:
- Same as 8A

---

### Test 9: Browser Compatibility
**Goal**: Test across different browsers

#### Chrome/Edge (Chromium-based)
- [ ] Camera permission works
- [ ] Scanning works
- [ ] UI renders correctly

#### Firefox
- [ ] Camera permission works
- [ ] Scanning works
- [ ] UI renders correctly

---

### Test 10: Concurrent Users
**Goal**: Test multiple admin/staff scanning simultaneously

1. [ ] Open scanner on two different devices/browsers
2. [ ] Both scan different employee QR codes

**Expected**:
- Both scans process independently
- No conflicts
- Both attendance records created

---

### Test 11: Data Reload
**Goal**: Verify attendance data updates after scan

1. [ ] Note current "Clocked In" count
2. [ ] Scan employee QR code (TIME-IN)
3. [ ] Wait 2-3 seconds

**Expected**:
- "Clocked In" count increases by 1
- Employee appears in employee list with updated status

---

## Common Issues & Solutions

### Issue: "Camera permission denied"
**Solution**: 
- Chrome: Settings → Privacy and Security → Site Settings → Camera → Allow
- Edge: Same as Chrome
- Firefox: Click camera icon in address bar → Allow

### Issue: "HTTPS Required" warning
**Solution**:
- Use `https://` URL
- OR test on `http://localhost` / `http://127.0.0.1`
- OR set up local SSL certificate

### Issue: Camera not detected
**Solution**:
- Ensure camera is connected and drivers installed
- Close other apps using camera
- Restart browser
- Check Windows Privacy Settings → Camera → Allow apps

### Issue: Scanner starts but freezes
**Solution**:
- Stop scanner and restart
- Refresh page
- Check browser console for errors
- Ensure adequate lighting

### Issue: QR code not scanning
**Solution**:
- Ensure good lighting
- Hold QR code steady
- Move closer/farther from camera
- Ensure QR code is for current date
- Check QR code is not expired (midnight reset)

---

## Performance Considerations

- **Scan Cooldown**: 3 seconds (client-side debouncing)
- **Backend Cooldown**: 5 minutes (server-side enforcement)
- **Max Scans Per Day**: 2 (TIME-IN, TIME-OUT)
- **Camera FPS**: 10 (configurable)
- **QR Box Size**: 250x250px
- **Auto-hide Messages**: 5 seconds for success/info

---

## Security Features Implemented

✅ **CSRF Protection**: All AJAX requests include CSRF token  
✅ **Token Validation**: Backend verifies HMAC-SHA256 signature  
✅ **Date Validation**: QR code must match current date  
✅ **Cooldown Enforcement**: Prevents spam scanning  
✅ **Max Logs**: 2 per employee per day  
✅ **HTTPS Requirement**: Camera access requires secure connection  
✅ **Role-Based Access**: Only Admin/Staff can access scanner  
✅ **IP Logging**: All scans logged with IP address  

---

## Files Modified

1. **resources/views/attendanceTimeTracking.blade.php**
   - Added "Scan QR Code" button
   - Added QR scanner modal HTML
   - Added `qrScannerModal()` Alpine component
   - Added `openQrScanner()` and `closeQrScanner()` methods
   - Included html5-qrcode library

---

## Backend Endpoints Used

- **GET** `/employee/qr-today` - Employee QR code display
- **GET** `/attendance/scanner` - Scanner page (Admin/Staff only)
- **POST** `/attendance/qr-scan` - Process QR scan (Admin/Staff only)

---

## NPM/Composer Dependencies

**No additional installations required!**

All libraries loaded via CDN:
- html5-qrcode@2.3.8 (from unpkg.com)
- Alpine.js (already in project)
- Tailwind CSS (already in project)

---

## Production Deployment Notes

1. **HTTPS Requirement**: Camera API requires HTTPS in production
2. **Browser Support**: Requires WebRTC support (all modern browsers)
3. **Camera Access**: Users must grant permission once per domain
4. **Performance**: Uses 10 FPS to balance performance and battery
5. **Mobile Support**: Works on mobile browsers with rear/front camera

---

## Troubleshooting Commands

```bash
# Check if QR secrets are generated for employees
php artisan tinker
>>> App\Models\Employee::whereNull('qr_secret')->count()
# Should return 0

# Regenerate QR secrets if needed
php artisan app:generate-qr-secrets

# View recent QR attendance logs
php artisan tinker
>>> App\Models\QrAttendanceLog::latest()->take(5)->get()

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Success Criteria

✅ Button visible in Clock In/Out tab  
✅ Modal opens on button click  
✅ Camera permission requests properly  
✅ Scanner starts and displays camera preview  
✅ QR codes scan successfully  
✅ Attendance records created (TIME-IN/TIME-OUT)  
✅ Error messages display for all failure scenarios  
✅ HTTPS warnings show on HTTP (except localhost)  
✅ 3-second debouncing prevents duplicate submissions  
✅ Backend validates tokens and enforces cooldown  
✅ CSRF token included in all requests  
✅ Works on Chrome and Edge browsers  

---

## Test Sign-Off

**Tester Name**: ________________  
**Date**: ________________  
**Browser**: ________________  
**Environment**: [ ] Localhost [ ] HTTPS Production  
**Camera Type**: ________________  

**Overall Result**: [ ] PASS [ ] FAIL  

**Notes**:  
_____________________________________________  
_____________________________________________  
_____________________________________________  
