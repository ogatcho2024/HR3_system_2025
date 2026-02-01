# User Sync API Specification
## For admin.cranecali-ms.com Integration

---

## Base URL
```
https://your-domain.com/api/user-sync
```

---

## Authentication
All requests require HMAC SHA-256 signature in header:
```
X-Webhook-Signature: {hmac_sha256_hash}
```

### Signature Generation
```php
$signature = hash_hmac('sha256', json_encode($payload), $sharedSecret);
```

---

## Endpoints

### 1. Single User Sync
```
POST /api/user-sync/webhook
```

**Headers:**
```
Content-Type: application/json
X-Webhook-Signature: {signature}
```

**Request Body:**
```json
{
  "user": {
    "external_user_id": "CRANE-USR-12345",
    "email": "john.doe@example.com",
    "name": "John",
    "lastname": "Doe",
    "phone": "+1234567890",
    "position": "Operations Manager",
    "account_type": "Admin",
    "role": "admin",
    "is_active": true,
    "date_of_birth": "1985-06-15",
    "gender": "male",
    "photo": "https://example.com/photo.jpg"
  },
  "api_version": "1.0",
  "source_service": "admin.cranecali-ms.com"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "User synced successfully",
  "data": {
    "external_user_id": "CRANE-USR-12345",
    "user_id": 42,
    "action": "created"
  }
}
```

---

### 2. Batch User Sync
```
POST /api/user-sync/batch
```

**Headers:**
```
Content-Type: application/json
X-Webhook-Signature: {signature}
```

**Request Body:**
```json
{
  "users": [
    {
      "external_user_id": "CRANE-USR-001",
      "email": "user1@example.com",
      "name": "User",
      "lastname": "One",
      "account_type": "Employee"
    },
    {
      "external_user_id": "CRANE-USR-002",
      "email": "user2@example.com",
      "name": "User",
      "lastname": "Two",
      "account_type": "Staff"
    }
  ],
  "api_version": "1.0",
  "source_service": "admin.cranecali-ms.com"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Batch sync completed: 2 successful, 0 failed",
  "summary": {
    "total": 2,
    "successful": 2,
    "failed": 0
  },
  "results": [
    {
      "external_user_id": "CRANE-USR-001",
      "success": true,
      "action": "created"
    },
    {
      "external_user_id": "CRANE-USR-002",
      "success": true,
      "action": "updated"
    }
  ]
}
```

---

## Field Reference

### Required Fields
| Field | Type | Description |
|-------|------|-------------|
| `external_user_id` | string | Unique ID from your system (max 255) |
| `email` | string | Valid email address (max 255) |
| `name` | string | First name (max 255) |

### Optional Fields
| Field | Type | Allowed Values |
|-------|------|----------------|
| `lastname` | string | Max 255 chars |
| `phone` | string | Max 20 chars |
| `position` | string | Max 255 chars |
| `date_of_birth` | date | Format: YYYY-MM-DD |
| `gender` | enum | `male`, `female`, `other` |
| `account_type` | enum | `Super admin`, `Admin`, `Staff`, `Employee` |
| `role` | string | Max 100 chars |
| `is_active` | boolean | `true` or `false` |
| `photo` | string | URL (max 500 chars) |

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Sync operation failed |
| 401 | Invalid or missing signature |
| 422 | Validation error |
| 500 | Server error |
| 503 | Service disabled |

---

## Error Response Format

**401 Unauthorized:**
```json
{
  "success": false,
  "message": "Invalid webhook signature"
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "user.email": ["The user.email field must be a valid email address."]
  }
}
```

**400 Sync Failed:**
```json
{
  "success": false,
  "message": "User sync failed",
  "error": "Error details here"
}
```

---

## Implementation Examples

### PHP
```php
$payload = json_encode([
    'user' => [
        'external_user_id' => 'CRANE-001',
        'email' => 'user@example.com',
        'name' => 'John',
        'lastname' => 'Doe'
    ],
    'api_version' => '1.0'
]);

$signature = hash_hmac('sha256', $payload, $webhookSecret);

$ch = curl_init('https://your-domain.com/api/user-sync/webhook');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Webhook-Signature: ' . $signature
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
```

### Python
```python
import hmac
import hashlib
import json
import requests

payload = {
    'user': {
        'external_user_id': 'CRANE-001',
        'email': 'user@example.com',
        'name': 'John',
        'lastname': 'Doe'
    },
    'api_version': '1.0'
}

payload_json = json.dumps(payload)
signature = hmac.new(
    webhook_secret.encode('utf-8'),
    payload_json.encode('utf-8'),
    hashlib.sha256
).hexdigest()

response = requests.post(
    'https://your-domain.com/api/user-sync/webhook',
    data=payload_json,
    headers={
        'Content-Type': 'application/json',
        'X-Webhook-Signature': signature
    }
)
```

### Node.js
```javascript
const crypto = require('crypto');
const axios = require('axios');

const payload = {
  user: {
    external_user_id: 'CRANE-001',
    email: 'user@example.com',
    name: 'John',
    lastname: 'Doe'
  },
  api_version: '1.0'
};

const payloadJson = JSON.stringify(payload);
const signature = crypto
  .createHmac('sha256', webhookSecret)
  .update(payloadJson)
  .digest('hex');

axios.post('https://your-domain.com/api/user-sync/webhook', payload, {
  headers: {
    'Content-Type': 'application/json',
    'X-Webhook-Signature': signature
  }
});
```

---

## Testing

### Test Payload
```json
{
  "user": {
    "external_user_id": "TEST-001",
    "email": "test@example.com",
    "name": "Test User"
  },
  "api_version": "1.0"
}
```

### Expected Result
- **First request:** Creates new user, returns `"action": "created"`
- **Second request (same email):** Updates user, returns `"action": "updated"`

---

**API Version:** 1.0  
**Contact:** Your development team  
**Webhook Secret:** To be provided separately
