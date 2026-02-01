# User Sync Receiver API Documentation

## Overview

The User Sync Receiver API enables secure, near real-time synchronization of user account data from **admin.cranecali-ms.com** (the source of truth) to this Laravel application (the receiver). The API implements HMAC SHA-256 webhook signature verification, field-level protection, and comprehensive audit logging.

## Table of Contents

1. [Architecture](#architecture)
2. [Security](#security)
3. [Setup & Configuration](#setup--configuration)
4. [API Endpoints](#api-endpoints)
5. [Field Mappings](#field-mappings)
6. [Webhook Payload Examples](#webhook-payload-examples)
7. [Error Handling](#error-handling)
8. [Monitoring & Troubleshooting](#monitoring--troubleshooting)

---

## Architecture

### Components

- **UserSyncController**: Handles incoming webhook requests and orchestrates UPSERT operations
- **UserSync Model**: Tracks all sync operations with status, attempts, and error messages
- **WebhookSignatureMiddleware**: Verifies HMAC SHA-256 signatures on all webhook requests
- **user_syncs Table**: Audit trail of all sync operations

### Data Flow

```
admin.cranecali-ms.com → [HTTPS + HMAC Signature] → WebhookSignatureMiddleware 
→ UserSyncController → Validation → UPSERT → Database → Response
```

---

## Security

### 1. HMAC SHA-256 Signature Verification

All webhook requests must include an `X-Webhook-Signature` header containing a HMAC SHA-256 hash of the request payload.

**Signature Generation (at admin.cranecali-ms.com):**
```php
$payload = json_encode($requestData);
$signature = hash_hmac('sha256', $payload, $sharedSecret);
// Include in header: X-Webhook-Signature: {$signature}
```

**Signature Verification (automatic):**
The `WebhookSignatureMiddleware` automatically verifies all requests to webhook endpoints using a timing-safe comparison to prevent timing attacks.

### 2. Protected Fields

The following fields are **NEVER** overwritten by sync operations:
- `password`
- `otp_code`
- `otp_expires_at`
- `otp_verified`
- `otp_status`
- `require_2fa`
- `remember_token`
- `email_verified_at`

### 3. Rate Limiting

Default rate limits (configurable via .env):
- 60 requests per minute per IP address

### 4. SSL/TLS

All webhook endpoints should be accessed over HTTPS in production.

---

## Setup & Configuration

### 1. Environment Variables

Add these to your `.env` file:

```env
# User Sync Configuration
USER_SYNC_ENABLED=true
USER_SYNC_WEBHOOK_SECRET=your-secure-secret-key-here
USER_SYNC_SOURCE_SERVICE=admin.cranecali-ms.com
USER_SYNC_MAX_RETRY_ATTEMPTS=3
USER_SYNC_LOGGING_ENABLED=true
USER_SYNC_LOG_PAYLOAD=false
```

**Important:** The `USER_SYNC_WEBHOOK_SECRET` must match the secret configured in admin.cranecali-ms.com.

### 2. Generate a Secure Webhook Secret

```bash
php artisan tinker
# Then run:
echo bin2hex(random_bytes(32));
```

Share this secret securely with the admin.cranecali-ms.com team.

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Configuration Customization

Edit `config/user_sync.php` to customize:
- Syncable fields list
- Protected fields list
- Validation rules
- Default values for new users
- Retry attempts
- Logging behavior

---

## API Endpoints

### Base URL

```
https://your-domain.com/api/user-sync
```

### 1. Single User Webhook

**Endpoint:** `POST /api/user-sync/webhook`

**Authentication:** HMAC signature via `X-Webhook-Signature` header

**Description:** Receives a single user record and performs UPSERT operation.

**Request Headers:**
```
Content-Type: application/json
X-Webhook-Signature: {hmac_sha256_signature}
```

**Request Body:**
```json
{
  "user": {
    "external_user_id": "USR-12345",
    "email": "john.doe@example.com",
    "name": "John",
    "lastname": "Doe",
    "phone": "+1234567890",
    "position": "Senior Manager",
    "account_type": "Admin",
    "role": "admin",
    "is_active": true,
    "date_of_birth": "1985-06-15",
    "gender": "male",
    "photo": "https://example.com/photos/john.jpg"
  },
  "api_version": "1.0",
  "source_service": "admin.cranecali-ms.com"
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "User synced successfully",
  "data": {
    "external_user_id": "USR-12345",
    "user_id": 42,
    "action": "updated"
  }
}
```

**Error Response (422 Validation Error):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "user.email": ["The user.email field must be a valid email address."]
  }
}
```

---

### 2. Batch User Sync

**Endpoint:** `POST /api/user-sync/batch`

**Authentication:** HMAC signature via `X-Webhook-Signature` header

**Description:** Syncs multiple users in a single request (max 100 users).

**Request Body:**
```json
{
  "users": [
    {
      "external_user_id": "USR-001",
      "email": "user1@example.com",
      "name": "User",
      "lastname": "One",
      "account_type": "Employee"
    },
    {
      "external_user_id": "USR-002",
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

**Success Response (200 OK):**
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
      "external_user_id": "USR-001",
      "success": true,
      "action": "created"
    },
    {
      "external_user_id": "USR-002",
      "success": true,
      "action": "updated"
    }
  ]
}
```

---

### 3. Get Sync Status (Monitoring)

**Endpoint:** `GET /api/user-sync/status`

**Authentication:** Bearer token (SimpleApiAuth)

**Description:** Retrieves sync operation statistics and recent sync records.

**Query Parameters:**
- `status` (optional): Filter by status (`pending`, `synced`, `failed`)
- `source_service` (optional): Filter by source service
- `hours` (optional, default: 24): Time window in hours

**Example Request:**
```
GET /api/user-sync/status?status=failed&hours=48
Authorization: Bearer {your-api-token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "external_user_id": "USR-12345",
        "sync_status": "synced",
        "last_sync_at": "2026-01-31T10:30:00Z",
        "user": {
          "id": 42,
          "name": "John",
          "lastname": "Doe",
          "email": "john.doe@example.com"
        }
      }
    ],
    "per_page": 50,
    "total": 150
  },
  "stats": {
    "total": 1500,
    "pending": 5,
    "synced": 1480,
    "failed": 15,
    "last_sync": "2026-01-31T12:00:00Z"
  }
}
```

---

### 4. Retry Failed Syncs

**Endpoint:** `POST /api/user-sync/retry-failed`

**Authentication:** Bearer token (SimpleApiAuth)

**Description:** Retries all failed sync operations that haven't exceeded max retry attempts.

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Retry completed: 8 successful out of 10",
  "results": [
    {
      "external_user_id": "USR-999",
      "success": true
    },
    {
      "external_user_id": "USR-888",
      "success": false,
      "error": "User validation failed"
    }
  ]
}
```

---

## Field Mappings

### Syncable Fields (from admin.cranecali-ms.com)

These fields **CAN** be synced and will be updated:

| Field | Type | Description | Validation |
|-------|------|-------------|------------|
| `external_user_id` | string | Unique ID from source system | Required, max:255 |
| `email` | string | User email address | Required, valid email |
| `name` | string | First name | Required, max:255 |
| `lastname` | string | Last name | Nullable, max:255 |
| `phone` | string | Phone number | Nullable, max:20 |
| `position` | string | Job position/title | Nullable, max:255 |
| `date_of_birth` | date | Birth date | Nullable, valid date |
| `gender` | enum | Gender | Nullable, one of: male, female, other |
| `account_type` | enum | Account type | Nullable, one of: Super admin, Admin, Staff, Employee |
| `role` | string | User role | Nullable, max:100 |
| `is_active` | boolean | Active status | Nullable, boolean |
| `photo` | string | Photo URL | Nullable, max:500 |

### Protected Fields (NEVER overwritten)

- `password` - User authentication credential
- `otp_code` - Two-factor auth code
- `otp_expires_at` - OTP expiration
- `otp_verified` - OTP verification status
- `otp_status` - OTP enabled status
- `require_2fa` - 2FA requirement flag
- `remember_token` - Session token
- `email_verified_at` - Email verification timestamp

### New User Defaults

When creating new users via sync, these defaults are applied:
```php
[
    'account_type' => 'Employee',
    'role' => 'employee',
    'is_active' => true,
    'email_verified_at' => null,  // Forces email verification
    'password' => bcrypt('sync_' . random_string),  // Secure random password
    'otp_status' => false,
    'otp_verified' => false,
    'require_2fa' => false,
]
```

---

## Webhook Payload Examples

### Example 1: Create New User

```json
{
  "user": {
    "external_user_id": "CRANE-USER-001",
    "email": "maria.garcia@example.com",
    "name": "Maria",
    "lastname": "Garcia",
    "phone": "+1-555-0123",
    "position": "Operations Manager",
    "account_type": "Staff",
    "role": "staff",
    "is_active": true,
    "date_of_birth": "1990-03-20",
    "gender": "female"
  },
  "api_version": "1.0"
}
```

### Example 2: Update Existing User

```json
{
  "user": {
    "external_user_id": "CRANE-USER-001",
    "email": "maria.garcia@example.com",
    "name": "Maria",
    "lastname": "Garcia-Smith",
    "position": "Senior Operations Manager",
    "account_type": "Admin",
    "is_active": true
  },
  "api_version": "1.0"
}
```

### Example 3: Deactivate User

```json
{
  "user": {
    "external_user_id": "CRANE-USER-002",
    "email": "john.smith@example.com",
    "name": "John",
    "lastname": "Smith",
    "is_active": false
  },
  "api_version": "1.0"
}
```

---

## Error Handling

### HTTP Status Codes

| Status | Meaning | Description |
|--------|---------|-------------|
| 200 | Success | Request processed successfully |
| 400 | Bad Request | Sync operation failed (business logic error) |
| 401 | Unauthorized | Invalid or missing webhook signature |
| 422 | Validation Error | Request data validation failed |
| 500 | Server Error | Internal server error |
| 503 | Service Unavailable | User sync is disabled |

### Common Error Scenarios

#### 1. Invalid Signature
```json
{
  "success": false,
  "message": "Invalid webhook signature"
}
```

**Solution:** Verify the shared secret matches on both systems.

#### 2. Missing Required Fields
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "user.email": ["The user.email field is required."],
    "user.external_user_id": ["The user.external_user_id field is required."]
  }
}
```

**Solution:** Ensure all required fields are included in the payload.

#### 3. Duplicate Email Conflict
When syncing a new user with an email that already exists, the system will **UPDATE** the existing user record instead of creating a duplicate.

---

## Monitoring & Troubleshooting

### Logging

All sync operations are logged with context. View logs at:
```
storage/logs/laravel.log
```

Log entries are prefixed with `[UserSync]` for easy filtering:
```
[2026-01-31 12:00:00] local.INFO: [UserSync] User synced successfully {"external_user_id":"USR-001","user_id":42}
[2026-01-31 12:00:05] local.ERROR: [UserSync] User sync failed {"external_user_id":"USR-002","error":"Validation failed"}
```

### Database Monitoring

Query the `user_syncs` table for sync status:

```sql
-- Get failed syncs
SELECT * FROM user_syncs WHERE sync_status = 'failed' ORDER BY updated_at DESC;

-- Get sync statistics
SELECT 
    sync_status, 
    COUNT(*) as count,
    MAX(last_sync_at) as last_sync
FROM user_syncs 
GROUP BY sync_status;

-- Find users not yet synced
SELECT * FROM user_syncs WHERE user_id IS NULL;
```

### Health Check Endpoint

Use the sync status endpoint to monitor system health:

```bash
curl -H "Authorization: Bearer {token}" \
  "https://your-domain.com/api/user-sync/status?status=failed&hours=1"
```

### Retry Failed Syncs

Manually trigger retry of failed syncs:

```bash
curl -X POST \
  -H "Authorization: Bearer {token}" \
  "https://your-domain.com/api/user-sync/retry-failed"
```

### Testing Webhook Signatures Locally

```php
// Generate test signature
$payload = json_encode([
    'user' => [
        'external_user_id' => 'TEST-001',
        'email' => 'test@example.com',
        'name' => 'Test User'
    ]
]);

$secret = config('user_sync.webhook_secret');
$signature = hash_hmac('sha256', $payload, $secret);

// Use in curl:
// curl -X POST https://your-domain.com/api/user-sync/webhook \
//   -H "Content-Type: application/json" \
//   -H "X-Webhook-Signature: {$signature}" \
//   -d '{payload}'
```

---

## Best Practices

1. **Secret Management**: Store the webhook secret securely (use environment variables, never commit to version control)

2. **Retry Logic**: The system automatically tracks retry attempts. Failed syncs with < 3 attempts can be retried via the retry endpoint.

3. **Batch Operations**: Use the batch endpoint for bulk updates to reduce HTTP overhead (max 100 users per request)

4. **Monitoring**: Set up alerts for:
   - High failure rates (>10% failed syncs)
   - Signature verification failures (potential security issue)
   - Sync lag (last_sync_at > 1 hour old)

5. **Email Uniqueness**: The system uses `email` as the unique identifier for UPSERT operations. Ensure emails are unique in admin.cranecali-ms.com.

6. **Testing**: Always test in a staging environment before deploying to production.

---

## Integration Checklist for admin.cranecali-ms.com Team

- [ ] Generate and securely share webhook secret
- [ ] Configure webhook endpoint URL: `https://your-domain.com/api/user-sync/webhook`
- [ ] Implement HMAC SHA-256 signature generation
- [ ] Include `X-Webhook-Signature` header in all webhook requests
- [ ] Map your user fields to our syncable fields
- [ ] Implement retry logic with exponential backoff for failed webhooks
- [ ] Set up monitoring for webhook delivery failures
- [ ] Test with staging environment first
- [ ] Document the `external_user_id` format used in your system

---

## Support & Contact

For technical issues or questions about the User Sync API, contact your development team or refer to this documentation.

**API Version:** 1.0  
**Last Updated:** 2026-01-31
