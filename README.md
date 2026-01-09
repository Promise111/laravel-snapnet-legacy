# Legacy HR API

A Laravel-based HR/Payroll API for managing employees. This is a legacy application that is part of a gradual transition to Node.js, with new services being built in Node.js while maintaining and improving this Laravel codebase.

## Project Overview

This API provides endpoints for managing employee data in an HR/Payroll system. The application follows Laravel best practices with clean MVC architecture, proper validation, and production-ready error handling.

### Features

- Employee management via RESTful API
- Comprehensive input validation
- Support for both JSON and form data requests
- MySQL database
- Clean, maintainable code structure

## Requirements

- PHP >= 8.2
- Composer
- MySQL
- Node.js and npm (for frontend assets)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd legacy-hr-api
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Set up environment file**
   ```bash
   cp .env.example .env
   ```
   
   Or create a `.env` file with the sample configuration below.

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Build frontend assets (optional)**
   ```bash
   npm run build
   ```

## Environment Configuration

Create a `.env` file in the root directory with the following sample configuration:

```env
APP_NAME="Legacy HR API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=legacy_hr_api
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Note:** For SQLite (development/testing only), update the database configuration:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

## Starting the Server

### Development Server

Start the Laravel development server:

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

### Custom Host/Port

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## Running the Queue Worker

The application uses background jobs to send welcome emails when employees are created. You need to run a queue worker to process these jobs.

### Development

For development, you can run the queue worker in a separate terminal:

```bash
php artisan queue:work
```

This will process jobs continuously until you stop it (Ctrl+C).

**Process a single job:**
```bash
php artisan queue:work --once
```

**Process jobs with specific options:**
```bash
php artisan queue:work --tries=3 --timeout=60
```

### Production

For production environments, you should use a process manager like Supervisor to keep the queue worker running. Alternatively, you can use Laravel Horizon if you're using Redis.

**Example Supervisor configuration** (`/etc/supervisor/conf.d/laravel-worker.conf`):
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/legacy-hr-api/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/legacy-hr-api/storage/logs/worker.log
stopwaitsecs=3600
```

After creating the configuration, reload Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Queue Configuration

The application is configured to use the `database` queue driver by default. Jobs are stored in the `jobs` table, which is created automatically when you run migrations.

To check if jobs are queued:
```bash
php artisan queue:monitor
```

To view failed jobs:
```bash
php artisan queue:failed
```

To retry failed jobs:
```bash
php artisan queue:retry all
```

### Background Job Behavior

When an employee is created via the API, a `SendWelcomeEmail` job is automatically dispatched to the queue. The job logs a message: `"Welcome email sent to {email}"` which can be found in `storage/logs/laravel.log`.

**Important:** The queue worker must be running for background jobs to be processed. If the worker is not running, jobs will remain in the queue until a worker processes them.

## API Endpoints

### Create Employee

Create a new employee in the system.

**Endpoint:** `POST /api/employees`

**Content-Type:** `application/json` or `application/x-www-form-urlencoded`

**Request Body:**
```json
{
    "first_name": "Promise",
    "last_name": "Ihunna",
    "email": "promiseihunna@gmail.com",
    "salary": "500000.00",
    "department": "Software Engineering"
}
```

**Field Requirements:**
- `first_name` (required, string, max:255)
- `last_name` (required, string, max:255)
- `email` (required, valid email, unique, max:255)
- `salary` (required, numeric, min:0)
- `department` (required, string, max:255)

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "Employee created successfully",
    "data": {
        "id": 1,
        "first_name": "Promise",
        "last_name": "Ihunna",
        "email": "promiseihunna@gmail.com",
        "salary": "500000.00",
        "department": "Software Engineering",
        "created_at": "2026-01-09T12:00:00.000000Z",
        "updated_at": "2026-01-09T12:00:00.000000Z"
    }
}
```

**Validation Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "salary": ["The salary field is required."]
    }
}
```

**Example cURL Request:**
```bash
curl -X POST http://localhost:8000/api/employees \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "first_name": "Promise",
    "last_name": "Ihunna",
    "email": "promiseihunna@gmail.com",
    "salary": "500000.00",
    "department": "Software Engineering"
  }'
```

**Example Form Data Request:**
```bash
curl -X POST http://localhost:8000/api/employees \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Accept: application/json" \
  -d "first_name=Promise&last_name=Ihunna&email=promiseihunna@gmail.com&salary=500000.00&department=Software Engineering"
```

## Database Setup

### MySQL

1. Create a MySQL database:
   ```sql
   CREATE DATABASE legacy_hr_api;
   ```

2. Update `.env` with MySQL credentials (see Environment Configuration above)

3. Run migrations:
   ```bash
   php artisan migrate
   ```

### SQLite (Development/Testing Only)

For local development or testing, you can use SQLite. The database file will be created automatically at `database/database.sqlite` when you run migrations.

Update your `.env` file:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Then run migrations:
```bash
php artisan migrate
```

## Project Structure

```
legacy-hr-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── EmployeeController.php
│   │   └── Requests/
│   │       └── PersistEmployeeRequest.php
│   └── Models/
│       └── Employee.php
├── database/
│   ├── migrations/
│   │   └── 2026_01_09_101723_create_employees_table.php
│   └── database.sqlite
├── routes/
│   └── api.php
├── config/
└── .env
```

## Key Assumptions

1. **Database:** MySQL is the intended database for production use. SQLite can be used for local development/testing.
2. **Validation:** All employee fields are required and validated according to business rules.
3. **Email Uniqueness:** Email addresses must be unique across all employees.
4. **Salary Format:** Salary is stored as a decimal with 2 decimal places.
5. **API Format:** All responses follow a consistent JSON structure with `success`, `message`, and `data`/`errors` fields.

## Task A3: Legacy Code Improvement Strategies

### 1. Identifying Technical Debt in a Legacy Laravel App

**Simple Approaches:**
- **Code Review:** Manually review code for common issues like business logic in controllers, duplicated code, and hardcoded values.
- **Check Dependencies:** Run `composer outdated` to identify outdated packages that may have security vulnerabilities.
- **Review Logs:** Check application logs and error tracking for recurring issues that indicate problematic code areas.

**Common Patterns to Look For:**
- **Fat Controllers:** Business logic embedded in controllers instead of being in service classes or models.
- **N+1 Query Problems:** Missing eager loading causing excessive database queries (check Laravel Debugbar or query logs).
- **Code Duplication:** Same logic repeated in multiple places that should be extracted to shared methods or services.
- **Tight Coupling:** Classes that depend too heavily on each other, making changes difficult.
- **Missing Validation:** Inconsistent or missing input validation across endpoints.
- **Deprecated Code:** Usage of old Laravel methods that are no longer recommended or supported.

### 2. Safely Refactoring Without Breaking Production

**Practical Strategies:**
- **Test First:** Write tests for existing functionality before refactoring to ensure behavior doesn't change.
- **Small Incremental Changes:** Make small changes and deploy frequently rather than large refactors all at once.
- **Feature Flags:** Use simple configuration flags to enable/disable new code paths, allowing quick rollback if needed.
- **Gradual Migration:** Build new code alongside old code, test thoroughly, then switch over once stable.

**Zero Downtime Techniques:**
- **Backward Compatible Changes:** Keep old and new code working together during transitions (e.g., support both old and new API formats temporarily).
- **Database Migrations:** Add new columns as nullable first, migrate data gradually, then remove old columns later.
- **API Versioning:** Use versioned endpoints (`/api/v1/`, `/api/v2/`) to allow consumers to migrate gradually.
- **Staged Rollouts:** Deploy to a subset of servers first, monitor for issues, then roll out to all servers.

### 3. Deciding What to Migrate vs. What to Leave

**Key Criteria:**
- **Business Impact:** Prioritize features that are frequently used or critical to revenue. Leave stable, rarely-touched code alone.
- **Maintenance Burden:** Migrate code that's causing frequent bugs or is hard to maintain. Keep code that works well and rarely needs changes.
- **Team Skills:** Migrate when you have the right expertise available. Don't migrate complex systems if the team isn't ready.
- **Isolation:** Migrate standalone features first. Leave tightly integrated systems until you can untangle dependencies.

**Decision Factors:**
- **Change Frequency:** Code that changes often benefits more from migration to a modern stack.
- **Performance Needs:** Migrate if the new stack offers clear performance benefits for that specific use case.
- **Complexity:** Simple, well-defined features are easier to migrate. Complex systems with many dependencies should wait.
- **Cost vs. Benefit:** If legacy code is stable and maintenance is low, migration may not be worth the effort and risk.

## Testing

Run the test suite:

```bash
php artisan test
```

## License

This project is part of a legacy system and is maintained for production use.
