# Employee Management Routing Bug Fix

## Issue Description
When clicking the **Edit** button in User Profiles Management, the browser navigated to:
```
http://localhost/employee-management/users/22
```

This resulted in a **404 Not Found** error because the URL bypassed the Laravel public path.

## Root Cause
The JavaScript code used **hardcoded relative URLs** that didn't account for the Laravel application being hosted in a subdirectory:
```
/dashboard/HumanResources3/public
```

### Problem Code (Before Fix)
```javascript
// Line 467 - Edit form action
document.getElementById('editUserForm').action = `/employee-management/users/${userId}`;

// Line 503 - Delete form action
form.action = `/employee-management/users/${userToDelete}`;

// Line 461 - Profile photo
profilePreview.src = `/storage/${photo}`;
```

## Solution

### 1. Fixed JavaScript URLs
Updated all hardcoded URLs to use Laravel's `url()` helper:

```javascript
// Edit form action (Line 467)
document.getElementById('editUserForm').action = `{{ url('employee-management/users') }}/${userId}`;

// Delete form action (Line 503)
form.action = `{{ url('employee-management/users') }}/${userToDelete}`;

// Profile photo (Line 461)
profilePreview.src = `{{ url('storage') }}/${photo}`;
```

### 2. Fixed Controller Validation
The account_type validation was expecting numeric values (1,2,3) but the database stores strings:

**Before:**
```php
'account_type' => 'required|in:1,2,3'
```

**After:**
```php
'account_type' => 'required|in:Super admin,Admin,Staff,Employee'
```

## Files Modified
1. `resources/views/employee-management/employees/index.blade.php` (Lines 461, 467, 503)
2. `app/Http/Controllers/EmployeeManagementController.php` (Lines 195, 250)

## Routes Verified
✓ `PUT employee-management/users/{user}` → `employee-management.users.update`
✓ `DELETE employee-management/users/{user}` → `employee-management.users.delete`

## Testing

### Expected Behavior
1. Click Edit button on any user
2. Browser stays on the same page (modal opens)
3. Form submits to: `http://localhost/dashboard/HumanResources3/public/employee-management/users/22`
4. User data is updated successfully
5. Redirects back to employee list with success message

### Test URLs Generated
```php
// From tinker:
url('employee-management/users/22')
// Output: http://localhost/dashboard/HumanResources3/public/employee-management/users/22

route('employee-management.users.update', ['user' => 22])
// Output: http://localhost/dashboard/HumanResources3/public/employee-management/users/22
```

## Configuration
The fix relies on the correct `APP_URL` configuration in `.env`:
```
APP_URL=http://localhost/dashboard/HumanResources3/public
```

## Key Takeaways
1. **Never use hardcoded URLs** in Laravel applications
2. Always use `url()`, `route()`, or `asset()` helpers
3. These helpers automatically include the configured base path
4. This ensures the application works correctly regardless of deployment location

## Alternative Approach (Optional Enhancement)
For even better maintainability, consider using named routes in JavaScript:

```javascript
// Create route helpers in Blade
const routes = {
    updateUser: (userId) => `{{ route('employee-management.users.update', ['user' => ':id']) }}`.replace(':id', userId),
    deleteUser: (userId) => `{{ route('employee-management.users.delete', ['user' => ':id']) }}`.replace(':id', userId)
};

// Use in JavaScript
document.getElementById('editUserForm').action = routes.updateUser(userId);
form.action = routes.deleteUser(userToDelete);
```

This approach provides better IDE support and ensures route changes are automatically reflected.
