# Timesheet API - Quick Start Guide

## Overview

Secure REST API endpoints for retrieving employee timesheets, supporting subdomain system integration.

## Features

- ✅ **Token-based authentication** (Bearer tokens)
- ✅ **Input validation** (comprehensive parameter checking)
- ✅ **CORS support** (configurable allowed origins)
- ✅ **Pagination** (efficient data retrieval)
- ✅ **Filtering** (by employee, date range, status)
- ✅ **Proper HTTP status codes** (401, 404, 422, 500, etc.)
- ✅ **Two implementations** (Pure PHP/PDO + Laravel)
- ✅ **SQL injection protection** (prepared statements)
- ✅ **Error logging** (server-side only)

## Files Created

1. **Pure PHP Implementation**
   - `public/api/timesheets.php` - Standalone PDO-based API endpoint

2. **Laravel Implementation**
   - `app/Http/Controllers/Api/TimesheetController.php` - API controller
   - `routes/api.php` - Updated with timesheet routes

3. **Documentation**
   - `docs/TIMESHEET_API.md` - Complete API documentation
   - `TIMESHEET_API_README.md` - This quick start guide
   - `test_timesheet_api.php` - Test script

## Quick Setup

### 1. Database Setup

Ensure your database has the required tables:
- `timesheets`
- `employees`
- `users`
- `api_tokens`

These should already exist if you've run the Laravel migrations.

### 2. Configure CORS (Optional)

#### For Pure PHP:
Edit `public/api/timesheets.php` (lines 15-21):

```php
$allowedOrigins = [
    'http://localhost',
    'http://localhost:3000',
    'http://localhost:8080',
    'https://your-production-domain.com', // Add your domains here
];
```

#### For Laravel:
Edit `config/cors.php` and add your domains to `allowed_origins`.

### 3. Test the API

#### Option A: Use the test script

```bash
php test_timesheet_api.php
```

#### Option B: Use cURL

```bash
# Get a token first (if you don't have one)
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"your@email.com","password":"your_password"}'

# Use the token to get timesheets
curl -X GET "http://localhost/dashboard/HumanResources3/public/api/timesheets.php?limit=10" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## API Endpoints

### Pure PHP Endpoint

```
GET /dashboard/HumanResources3/public/api/timesheets.php
```

### Laravel Endpoints

```
GET /api/timesheets           # Get all timesheets
GET /api/timesheets/{id}      # Get specific timesheet
```

## Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `employee_id` | string | Filter by employee ID | `EMP001` |
| `user_id` | integer | Filter by user ID | `42` |
| `start_date` | string | Start date (YYYY-MM-DD) | `2026-01-01` |
| `end_date` | string | End date (YYYY-MM-DD) | `2026-01-31` |
| `status` | string | Filter by status | `approved` |
| `limit` | integer | Records per page (max 1000) | `100` |
| `offset` | integer | Pagination offset | `0` |

## Response Format

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_id": "EMP001",
      "user_id": 42,
      "employee": {
        "first_name": "John",
        "last_name": "Doe",
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "department": "Engineering"
      },
      "date": "2026-01-15",
      "project_name": "Project Alpha",
      "task_description": "Backend API development",
      "hours_worked": 8.5,
      "is_overtime": false,
      "status": "approved",
      "approved_by": 5,
      "approved_by_name": "Jane Manager",
      "approved_at": "2026-01-16T10:30:00Z",
      "notes": "Completed API endpoints",
      "created_at": "2026-01-15T17:00:00Z",
      "updated_at": "2026-01-16T10:30:00Z"
    }
  ],
  "meta": {
    "total": 145,
    "count": 10,
    "limit": 10,
    "offset": 0,
    "has_more": true
  }
}
```

## Common Use Cases

### Get approved timesheets for a date range

```bash
curl -X GET \
  "http://localhost/api/timesheets?start_date=2026-01-01&end_date=2026-01-31&status=approved" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get timesheets for a specific employee

```bash
curl -X GET \
  "http://localhost/api/timesheets?employee_id=EMP001" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Paginate through all timesheets

```bash
# Page 1
curl -X GET \
  "http://localhost/api/timesheets?limit=50&offset=0" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Page 2
curl -X GET \
  "http://localhost/api/timesheets?limit=50&offset=50" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Integration Examples

### JavaScript/Fetch

```javascript
async function getTimesheets() {
  const response = await fetch('http://localhost/api/timesheets?limit=10', {
    headers: {
      'Authorization': 'Bearer YOUR_TOKEN',
      'Content-Type': 'application/json'
    }
  });
  
  const data = await response.json();
  
  if (data.success) {
    data.data.forEach(timesheet => {
      console.log(timesheet.employee.full_name, timesheet.hours_worked);
    });
  }
}
```

### PHP

```php
function getTimesheets($token, $filters = []) {
    $url = 'http://localhost/api/timesheets?' . http_build_query($filters);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

$result = getTimesheets('YOUR_TOKEN', ['status' => 'approved', 'limit' => 10]);
```

### Python

```python
import requests

def get_timesheets(token, filters=None):
    response = requests.get(
        'http://localhost/api/timesheets',
        headers={'Authorization': f'Bearer {token}'},
        params=filters or {}
    )
    return response.json()

result = get_timesheets('YOUR_TOKEN', {'status': 'approved', 'limit': 10})
```

## Security Notes

### For Development
- Use HTTP with localhost
- Tokens stored in database
- CORS configured for local origins

### For Production
- ⚠️ **ALWAYS use HTTPS**
- Update CORS allowed origins to production domains
- Implement rate limiting
- Monitor API usage and logs
- Rotate tokens regularly
- Use environment variables for sensitive config

## Troubleshooting

### "401 Unauthorized"
- Token missing or invalid
- Token expired
- Check Authorization header format: `Bearer YOUR_TOKEN`

### "422 Validation Error"
- Invalid parameter format
- Check date format (YYYY-MM-DD)
- Check status value (draft/submitted/approved/rejected)

### "500 Internal Server Error"
- Check PHP error logs
- Check Laravel logs: `storage/logs/laravel.log`
- Verify database connection
- Ensure tables exist

### CORS Errors
- Add your origin to allowed origins list
- Check preflight OPTIONS requests
- Verify CORS headers are sent

### Empty Response
- No data matches your filters
- Check database has timesheet records
- Verify employee and timesheet tables have data

## Performance Tips

1. **Use pagination** for large datasets (limit ≤ 100 recommended)
2. **Filter by date range** to reduce result set
3. **Add database indexes** on frequently queried columns:
   - `timesheets.date`
   - `timesheets.status`
   - `timesheets.employee_id`

## Support

For detailed documentation, see `docs/TIMESHEET_API.md`

For issues:
1. Check error logs (PHP/Apache logs, `storage/logs/laravel.log`)
2. Run the test script: `php test_timesheet_api.php`
3. Verify database tables and data exist

## Next Steps

1. ✅ Test both implementations (Pure PHP and Laravel)
2. ✅ Add your production domains to CORS whitelist
3. ✅ Configure HTTPS for production
4. ✅ Implement rate limiting (optional but recommended)
5. ✅ Set up monitoring and logging
6. ✅ Create API tokens for your subdomain systems
7. ✅ Document any custom integrations

## Version

**Version:** 1.0.0  
**Created:** 2026-01-26  
**Last Updated:** 2026-01-26
