# Employee Sync System

This system allows your HR application to sync employee data from an external microservice, where the external system is the source of truth.

## Features

- **Real-time sync**: Webhook endpoints to receive employee updates
- **Batch sync**: Process multiple employee updates in a single request
- **Manual sync**: Artisan command to pull all employees from external API
- **Sync tracking**: Complete audit trail of sync operations
- **Error handling**: Retry mechanism for failed syncs
- **Status monitoring**: API endpoints to monitor sync health

## Database Tables

### `employee_sync`
Tracks synchronization status and stores raw external data:
- `external_id`: Unique ID from external microservice
- `employee_id`: Local employee ID (nullable)
- `external_data`: Raw JSON data from external API
- `sync_status`: pending, synced, failed, deleted
- `last_sync_at`: Timestamp of last successful sync
- `sync_error`: Error message if sync failed
- `sync_attempts`: Number of retry attempts
- `source_service`: Name of external microservice

### `employees` table updates
- Added `external_id` field to link with external microservice

## API Endpoints

### Webhook Endpoint (for external microservice to push updates)
```
POST /api/employee-sync/webhook
```

**Request Format:**
```json
{
  "action": "create|update|delete",
  "employee": {
    "external_id": "123",
    "email": "john@company.com",
    "name": "John Doe",
    "position": "Software Engineer",
    "department": "Engineering",
    "salary": 75000,
    "hire_date": "2023-01-15",
    "status": "active"
  },
  "api_version": "v1",
  "source_service": "employee-microservice"
}
```

### Batch Sync Endpoint
```
POST /api/employee-sync/batch
```

**Request Format:**
```json
{
  "employees": [
    {
      "action": "create",
      "employee": { /* employee data */ }
    },
    {
      "action": "update", 
      "employee": { /* employee data */ }
    }
  ],
  "api_version": "v1",
  "source_service": "employee-microservice"
}
```

### Sync Status Monitoring
```
GET /api/employee-sync/status
```

**Query Parameters:**
- `status`: Filter by sync status (pending, synced, failed, deleted)
- `source_service`: Filter by source service
- `hours`: Show records from last N hours (default: 24)

### Retry Failed Syncs
```
POST /api/employee-sync/retry-failed
```

## Artisan Commands

### Manual Sync from External API
```bash
# Basic sync
php artisan employees:sync --url=https://external-api.com/api --token=your-token

# Dry run (preview changes)
php artisan employees:sync --url=https://external-api.com/api --token=your-token --dry-run

# With custom timeout and service name
php artisan employees:sync --url=https://external-api.com/api --token=your-token --timeout=60 --service=hr-system
```

## Configuration

Add these environment variables to your `.env` file:

```env
# External Employee Microservice API URL
EMPLOYEE_MICROSERVICE_URL=https://your-employee-microservice.com/api

# API Authentication Token
EMPLOYEE_MICROSERVICE_TOKEN=your-api-token-here

# Optional configurations
EMPLOYEE_MICROSERVICE_TIMEOUT=30
EMPLOYEE_SYNC_INTERVAL=3600
EMPLOYEE_SYNC_RETRY_ATTEMPTS=3
EMPLOYEE_WEBHOOK_SECRET=your-webhook-secret-here
```

## External API Expected Format

The external microservice should provide employee data in this format:

```json
{
  "data": [
    {
      "id": "ext_123",
      "email": "john@company.com",
      "name": "John Doe",
      "first_name": "John",
      "last_name": "Doe",
      "position": "Software Engineer",
      "job_title": "Senior Developer",
      "department": "Engineering",
      "salary": 75000,
      "hire_date": "2023-01-15",
      "employee_number": "EMP001",
      "status": "active"
    }
  ]
}
```

## Sync Process

1. **External microservice sends webhook** to your app when employee data changes
2. **Your app receives webhook** and validates the data
3. **Sync record is created/updated** in `employee_sync` table
4. **Employee data is processed** (create/update/delete local employee)
5. **Sync status is updated** (synced/failed)
6. **Errors are logged** for failed syncs with retry capability

## Security

- **API Authentication**: Use tokens to secure webhook endpoints
- **Webhook Validation**: Verify webhook signatures if provided
- **Data Validation**: Strict validation of incoming employee data
- **Error Logging**: All sync operations are logged for audit

## Monitoring

- Check sync status via API: `GET /api/employee-sync/status`
- Monitor failed syncs and retry them
- Review sync logs in Laravel logs
- Track sync performance and timing

## Troubleshooting

### Failed Syncs
1. Check the sync status API for error details
2. Retry failed syncs using the retry endpoint
3. Check Laravel logs for detailed error messages
4. Verify external API connectivity and authentication

### Manual Sync Issues
1. Verify external API URL and token configuration
2. Check network connectivity to external microservice
3. Validate external API response format
4. Use dry-run mode to preview changes without applying them

### Data Conflicts
- External microservice is always the source of truth
- Local changes to externally managed employees will be overwritten
- Use `isExternallyManaged()` method to check if employee is from external source
