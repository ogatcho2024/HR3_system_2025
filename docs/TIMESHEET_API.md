# Timesheet API Documentation

## Overview

This document describes the secure REST API endpoints for retrieving employee timesheets, designed to support integration between subdomain systems. The API is available in two implementations:

1. **Pure PHP (PDO)**: `public/api/timesheets.php`
2. **Laravel**: API routes with controller

Both implementations provide the same functionality with token-based authentication, input validation, proper HTTP status codes, and CORS support.

---

## Authentication

All endpoints require token-based authentication using Bearer tokens.

### Authorization Header

```
Authorization: Bearer YOUR_API_TOKEN_HERE
```

### Obtaining an API Token

Use the existing authentication endpoint to obtain a token:

**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "your_password"
}
```

**Response:**
```json
{
  "success": true,
  "token": "your-api-token-here",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

---

## Endpoints

### 1. Get All Timesheets (Pure PHP)

**URL:** `GET /api/timesheets.php`

**Authentication:** Required (Bearer Token)

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `employee_id` | string | No | Filter by employee ID (alphanumeric, max 10 chars) | `EMP001` |
| `user_id` | integer | No | Filter by user ID | `42` |
| `start_date` | string | No | Filter by start date (YYYY-MM-DD) | `2026-01-01` |
| `end_date` | string | No | Filter by end date (YYYY-MM-DD) | `2026-01-31` |
| `status` | string | No | Filter by status (`draft`, `submitted`, `approved`, `rejected`) | `approved` |
| `limit` | integer | No | Number of records per page (default: 100, max: 1000) | `50` |
| `offset` | integer | No | Offset for pagination (default: 0) | `0` |

#### Example Request (cURL)

```bash
curl -X GET \
  'http://localhost/dashboard/HumanResources3/public/api/timesheets.php?start_date=2026-01-01&end_date=2026-01-31&status=approved&limit=10' \
  -H 'Authorization: Bearer your-token-here'
```

#### Example Request (JavaScript/Fetch)

```javascript
fetch('http://localhost/dashboard/HumanResources3/public/api/timesheets.php?status=approved&limit=10', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer your-token-here',
    'Content-Type': 'application/json'
  }
})
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));
```

#### Success Response (200 OK)

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

---

### 2. Get All Timesheets (Laravel)

**URL:** `GET /api/timesheets`

**Authentication:** Required (Bearer Token)

#### Query Parameters

Same as the Pure PHP endpoint above.

#### Example Request (cURL)

```bash
curl -X GET \
  'http://localhost/api/timesheets?start_date=2026-01-01&end_date=2026-01-31&status=approved&limit=10' \
  -H 'Authorization: Bearer your-token-here'
```

#### Example Request (JavaScript/Axios)

```javascript
const axios = require('axios');

axios.get('http://localhost/api/timesheets', {
  params: {
    start_date: '2026-01-01',
    end_date: '2026-01-31',
    status: 'approved',
    limit: 10
  },
  headers: {
    'Authorization': 'Bearer your-token-here'
  }
})
  .then(response => console.log(response.data))
  .catch(error => console.error('Error:', error));
```

#### Success Response (200 OK)

Same format as the Pure PHP endpoint.

---

### 3. Get Single Timesheet (Laravel Only)

**URL:** `GET /api/timesheets/{id}`

**Authentication:** Required (Bearer Token)

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Timesheet ID |

#### Example Request (cURL)

```bash
curl -X GET \
  'http://localhost/api/timesheets/42' \
  -H 'Authorization: Bearer your-token-here'
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 42,
    "employee_id": "EMP001",
    "user_id": 15,
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
}
```

---

## Error Responses

### 401 Unauthorized

**Description:** Invalid or missing authentication token.

```json
{
  "success": false,
  "message": "Unauthorized. Invalid or expired token.",
  "error": "UNAUTHORIZED"
}
```

### 404 Not Found

**Description:** Timesheet with the specified ID does not exist.

```json
{
  "success": false,
  "message": "Timesheet not found",
  "error": "NOT_FOUND"
}
```

### 405 Method Not Allowed

**Description:** HTTP method not allowed (Pure PHP endpoint only accepts GET).

```json
{
  "success": false,
  "message": "Method not allowed. Only GET requests are supported.",
  "error": "METHOD_NOT_ALLOWED"
}
```

### 422 Validation Error

**Description:** Request parameters failed validation.

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": [
    "Invalid start_date format. Use YYYY-MM-DD",
    "Invalid status. Allowed values: draft, submitted, approved, rejected"
  ],
  "error": "VALIDATION_ERROR"
}
```

### 500 Internal Server Error

**Description:** Server error occurred while processing the request.

```json
{
  "success": false,
  "message": "Failed to retrieve timesheets",
  "error": "SERVER_ERROR"
}
```

---

## CORS Configuration

Both implementations support Cross-Origin Resource Sharing (CORS) for subdomain integration.

### Pure PHP Implementation

The allowed origins are configured in `public/api/timesheets.php`:

```php
$allowedOrigins = [
    'http://localhost',
    'http://localhost:3000',
    'http://localhost:8080',
    // Add your production/staging domains here
    // 'https://your-subdomain.example.com'
];
```

**To add your production domain:**

1. Edit `public/api/timesheets.php`
2. Add your domain to the `$allowedOrigins` array
3. Save the file

### Laravel Implementation

Laravel CORS is configured through middleware. To configure allowed origins:

1. Edit `config/cors.php`
2. Update the `allowed_origins` array
3. Run `php artisan config:cache` to apply changes

---

## Integration Examples

### PHP Integration

```php
<?php

function getTimesheets($token, $filters = []) {
    $baseUrl = 'http://localhost/dashboard/HumanResources3/public/api/timesheets.php';
    $queryString = http_build_query($filters);
    $url = $baseUrl . ($queryString ? '?' . $queryString : '');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return null;
}

// Usage
$token = 'your-api-token-here';
$filters = [
    'start_date' => '2026-01-01',
    'end_date' => '2026-01-31',
    'status' => 'approved',
    'limit' => 50
];

$result = getTimesheets($token, $filters);
if ($result && $result['success']) {
    foreach ($result['data'] as $timesheet) {
        echo "Employee: {$timesheet['employee']['full_name']}\n";
        echo "Date: {$timesheet['date']}\n";
        echo "Hours: {$timesheet['hours_worked']}\n\n";
    }
}
?>
```

### Python Integration

```python
import requests

def get_timesheets(token, filters=None):
    url = "http://localhost/api/timesheets"
    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json"
    }
    
    response = requests.get(url, headers=headers, params=filters or {})
    
    if response.status_code == 200:
        return response.json()
    else:
        print(f"Error: {response.status_code}")
        return None

# Usage
token = "your-api-token-here"
filters = {
    "start_date": "2026-01-01",
    "end_date": "2026-01-31",
    "status": "approved",
    "limit": 50
}

result = get_timesheets(token, filters)
if result and result.get("success"):
    for timesheet in result["data"]:
        print(f"Employee: {timesheet['employee']['full_name']}")
        print(f"Date: {timesheet['date']}")
        print(f"Hours: {timesheet['hours_worked']}\n")
```

### Node.js Integration

```javascript
const axios = require('axios');

async function getTimesheets(token, filters = {}) {
  try {
    const response = await axios.get('http://localhost/api/timesheets', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      params: filters
    });
    
    return response.data;
  } catch (error) {
    console.error('Error fetching timesheets:', error.message);
    return null;
  }
}

// Usage
(async () => {
  const token = 'your-api-token-here';
  const filters = {
    start_date: '2026-01-01',
    end_date: '2026-01-31',
    status: 'approved',
    limit: 50
  };
  
  const result = await getTimesheets(token, filters);
  if (result && result.success) {
    result.data.forEach(timesheet => {
      console.log(`Employee: ${timesheet.employee.full_name}`);
      console.log(`Date: ${timesheet.date}`);
      console.log(`Hours: ${timesheet.hours_worked}\n`);
    });
  }
})();
```

---

## Security Considerations

### 1. Token Management
- Store API tokens securely (environment variables, secret managers)
- Never commit tokens to version control
- Rotate tokens regularly
- Implement token expiration policies

### 2. HTTPS in Production
- Always use HTTPS in production environments
- Update allowed origins to use `https://` protocol
- Enable HTTP Strict Transport Security (HSTS)

### 3. Rate Limiting
- Implement rate limiting to prevent API abuse
- Consider adding throttling middleware in Laravel
- Monitor API usage patterns

### 4. Input Validation
- All inputs are validated and sanitized
- SQL injection protection via prepared statements
- XSS protection through proper encoding

### 5. Error Handling
- Sensitive error details are logged server-side only
- Generic error messages returned to clients
- Monitor error logs for security incidents

---

## Pagination Best Practices

### Efficient Pagination

```javascript
async function getAllTimesheets(token, filters = {}) {
  const allData = [];
  let offset = 0;
  const limit = 100;
  
  while (true) {
    const response = await fetch(`http://localhost/api/timesheets?${new URLSearchParams({
      ...filters,
      limit,
      offset
    })}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    
    const result = await response.json();
    
    if (!result.success || result.data.length === 0) {
      break;
    }
    
    allData.push(...result.data);
    
    if (!result.meta.has_more) {
      break;
    }
    
    offset += limit;
  }
  
  return allData;
}
```

---

## Testing the API

### Using cURL

```bash
# Set your token
TOKEN="your-token-here"

# Get all approved timesheets for January 2026
curl -X GET \
  "http://localhost/api/timesheets?start_date=2026-01-01&end_date=2026-01-31&status=approved" \
  -H "Authorization: Bearer $TOKEN"

# Get timesheets for a specific employee
curl -X GET \
  "http://localhost/api/timesheets?employee_id=EMP001" \
  -H "Authorization: Bearer $TOKEN"

# Get a specific timesheet by ID
curl -X GET \
  "http://localhost/api/timesheets/42" \
  -H "Authorization: Bearer $TOKEN"
```

### Using Postman

1. Create a new GET request
2. Enter the endpoint URL
3. Go to the "Authorization" tab
4. Select "Bearer Token" as the type
5. Enter your token
6. Add query parameters in the "Params" tab
7. Click "Send"

---

## Troubleshooting

### Common Issues

#### 1. CORS Errors
**Problem:** Browser blocks request due to CORS policy

**Solution:** 
- Ensure your origin is in the allowed origins list
- Check that CORS headers are being sent
- Verify preflight OPTIONS requests are handled

#### 2. 401 Unauthorized
**Problem:** Token authentication fails

**Solution:**
- Verify token is valid and not expired
- Check Authorization header format: `Bearer TOKEN`
- Ensure token exists in the `api_tokens` table

#### 3. Empty Response
**Problem:** API returns empty data array

**Solution:**
- Check filter parameters are correct
- Verify data exists for the given filters
- Check database table has records

#### 4. 500 Internal Server Error
**Problem:** Server error during request

**Solution:**
- Check PHP error logs
- Check Laravel logs in `storage/logs/laravel.log`
- Verify database connection
- Check table structure matches migration

---

## Support and Maintenance

### Logs

- **Pure PHP errors:** Check Apache/PHP error logs
- **Laravel errors:** Check `storage/logs/laravel.log`
- **Database errors:** Check MySQL error logs

### Performance Monitoring

- Monitor query execution time
- Track API response times
- Monitor memory usage for large result sets
- Consider adding database indexes for frequently queried fields

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-01-26 | Initial release with both PHP and Laravel implementations |
