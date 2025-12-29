# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is a **Laravel 12** Human Resources Management System running on PHP 8.2+ with XAMPP (Windows environment). The application manages employee data, attendance tracking, leave management, timesheets, shift scheduling, and includes external employee synchronization capabilities.

## Development Commands

### Environment Setup
```powershell
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file (first time setup)
copy .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### Development Server
```powershell
# Start all services (Laravel server, queue worker, Vite)
composer run dev

# Or start individually:
php artisan serve                    # Laravel server on localhost:8000
php artisan queue:listen --tries=1   # Queue worker
npm run dev                           # Vite dev server
```

### Frontend Build
```powershell
# Development build with hot reload
npm run dev

# Production build
npm run build
```

### Testing
```powershell
# Run all tests
composer run test
# Or: php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Test using PHPUnit directly
vendor/bin/phpunit

# Test specific features
php artisan test:login-throttling
php artisan test:password-policy
```

### Code Quality
```powershell
# Laravel Pint (code formatting)
vendor/bin/pint

# Clear all caches
php artisan optimize:clear
# Or individually:
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Maintenance
```powershell
# Clean up old login attempts
php artisan login-attempts:cleanup

# Clean up old notifications
php artisan notifications:cleanup

# Sync employees from external microservice
php artisan employees:sync --url=https://api.example.com --token=YOUR_TOKEN

# Dry run (preview changes without applying)
php artisan employees:sync --url=https://api.example.com --token=YOUR_TOKEN --dry-run
```

## Application Architecture

### Core Domain Structure

The application follows Laravel's MVC pattern with additional service layer for complex business logic:

#### Models (Domain Entities)
- **Employee Management**: `Employee`, `EmployeeSync`, `User`, `Department`
- **Attendance System**: `Attendance`, `Timesheet`, `ShiftAssignment`, `ShiftTemplate`, `ShiftRequest`
- **Leave Management**: `LeaveRequest`, `LeaveBalance`, `LeaveBalanceAdjustment`, `LeavePolicy`
- **Security**: `LoginAttempt`, `ApiToken`
- **Communication**: `Notification`, `Alert`

#### Controllers (Request Handlers)
Controllers are organized by functional area:
- `AuthController` - Authentication with rate limiting
- `EmployeeManagementController` - HR admin employee operations
- `EmployeeSelfServiceController` - Employee portal features
- `AttendanceController` - Clock in/out, manual entry
- `TimesheetController` - Timesheet approval workflow
- `LeaveManagementController` - Leave request approval, balance tracking
- `ShiftManagementController` - Shift template and assignment management
- `NotificationController` - In-app notification system
- `ReportsController` - Analytics and PDF generation

API Controllers in `app/Http/Controllers/Api/`:
- `SimpleAuthController` - Mobile app authentication
- `EmployeeController` - Mobile clock in/out API
- `EmployeeSyncController` - External microservice webhook integration

#### Services (Business Logic)
- `NotificationService` - Notification creation and delivery
- `PayrollService` - Payroll integration data

#### Routes Organization
- `routes/web.php` - Main web routes
- `routes/api.php` - API routes for mobile app and external systems
- `routes/attendance.php` - Attendance-specific API endpoints
- `routes/migration.php` - Production deployment migration helpers
- `routes/console.php` - Artisan command routes

### External Employee Sync Architecture

The system supports syncing employee data from an external microservice as the source of truth:

**Models**: `Employee` (has `external_id`), `EmployeeSync` (tracks sync status)

**Webhook Flow**:
1. External microservice sends webhook to `/api/employee-sync/webhook`
2. System creates/updates `EmployeeSync` record with status `pending`
3. Employee data is processed (create/update/delete local employee)
4. Sync status updated to `synced` or `failed`
5. Failed syncs can be retried via `/api/employee-sync/retry-failed`

**Key Methods**:
- `Employee::isExternallyManaged()` - Check if employee from external source
- `EmployeeSync::shouldRetrySync()` - Determine if failed sync should retry
- `EmployeeSync::markAsSynced()` / `markAsFailed()` - Update sync status

**Configuration** (`.env`):
```
EMPLOYEE_MICROSERVICE_URL=https://external-api.com/api
EMPLOYEE_MICROSERVICE_TOKEN=your-api-token
EMPLOYEE_SYNC_RETRY_ATTEMPTS=3
```

See `EMPLOYEE_SYNC_README.md` for complete documentation.

### Security Features

#### Login Rate Limiting
- Default: 3 failed attempts = 5-minute block
- Tracks both IP address and email
- Custom middleware: `LoginThrottleMiddleware`
- Database table: `login_attempts`
- Configuration in `.env`: `LOGIN_MAX_ATTEMPTS`, `LOGIN_BLOCK_DURATION`
- See `LOGIN_RATE_LIMITING.md` for details

#### Password Policy
- Enforced via custom rule: `App\Rules\StrongPassword`
- Default requirements: 8 chars, uppercase, lowercase, number, special character
- Real-time validation in UI with strength meter
- Configuration in `.env`: `PASSWORD_MIN_LENGTH`, `PASSWORD_REQUIRE_*`
- See `PASSWORD_POLICY.md` for details

#### API Authentication
- Mobile app uses simple token-based auth (ApiToken model)
- Middleware: `simple.api.auth`
- Endpoint: `POST /api/auth/login` returns token
- All employee API routes require authentication

### Frontend Stack
- **Vite** for asset bundling
- **TailwindCSS 4.0** for styling
- **Alpine.js** for reactive components (used extensively in attendance tracking)
- **SASS** for custom styling
- Proxy configuration in `vite.config.js` routes API requests to Laravel backend

### Database Conventions
- Database connection: MySQL via XAMPP
- Default database: `humanresources3`
- Migrations in `database/migrations/`
- Seeders: `DepartmentSeeder`, `LeaveManagementSeeder`
- Queue connection: `database` (uses `jobs` table)
- Session driver: `database`

### Notification System
- Custom notification system (not Laravel's default)
- Model: `Notification` with user_id, title, message, type, read_at
- Service: `NotificationService` for centralized creation
- AJAX endpoints for real-time notification counts
- Types: info, success, warning, error

### PDF Generation
- Package: `barryvdh/laravel-dompdf`
- Used for leave reports, employee reports
- Example: `LeaveManagementController@exportLeaveReportsPDF`

## Testing Environment

Tests use SQLite in-memory database (configured in `phpunit.xml`):
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=:memory:`
- Queue and cache use `array` driver for testing
- Test files in `tests/Feature/` and `tests/Unit/`

## Production Deployment Notes

### XAMPP/Windows Specific
- Application runs on XAMPP in production at `hr3.cranecali-ms.com`
- Use `.htaccess` for URL rewriting
- Public directory: `public/`
- Storage permissions: Ensure `storage/` and `bootstrap/cache/` are writable

### Migration Strategy
- Admin interface available at `/admin/migration-status` for creating missing tables
- Web-based migration at `/run-migration?secret=create-login-table-2025`
- Fallback: Manual SQL execution (see `PRODUCTION_DEPLOYMENT.md`)

### Post-Deployment Checklist
```powershell
# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Link storage
php artisan storage:link
```

## Important Conventions

### Middleware
- `auth` - Requires authenticated user
- `login.throttle` - Rate limiting for login route
- `simple.api.auth` - API token authentication

### Timestamps
- All models use Laravel timestamps (`created_at`, `updated_at`)
- Use Carbon for date manipulation
- Timezones configured in `config/app.php`

### Validation
- Custom rules in `app/Rules/`
- Form requests should be used for complex validation
- API responses use consistent JSON structure: `{ success: bool, data: {}, message: string }`

### Artisan Commands
Custom commands in `app/Console/Commands/`:
- `CleanupLoginAttempts` - Remove old login attempt records
- `CleanupNotifications` - Remove old notifications
- `SyncEmployeesFromExternal` - Pull employees from external API
- Test commands: `TestPasswordPolicy`, `TestLoginThrottling`, etc.

### Code Style
- Laravel Pint enforces PSR-12 coding standards
- Run `vendor/bin/pint` before committing

## Environment Variables

Critical variables for development (see `.env.example`):
```
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=humanresources3
DB_USERNAME=root
DB_PASSWORD=

# Security
LOGIN_MAX_ATTEMPTS=3
LOGIN_BLOCK_DURATION=5
PASSWORD_MIN_LENGTH=8
PASSWORD_REQUIRE_UPPERCASE=true
PASSWORD_REQUIRE_LOWERCASE=true
PASSWORD_REQUIRE_NUMBER=true
PASSWORD_REQUIRE_SPECIAL_CHAR=true

# Queue
QUEUE_CONNECTION=database

# External Sync (optional)
EMPLOYEE_MICROSERVICE_URL=
EMPLOYEE_MICROSERVICE_TOKEN=
```
