# Legacy HR API

A Laravel-based HR/Payroll API for managing employees. This is a legacy application that is part of a gradual transition to Node.js, with new services being built in Node.js while maintaining and improving this Laravel codebase.

## Project Overview

This API provides endpoints for managing employee data in an HR/Payroll system. The application follows Laravel best practices with clean MVC architecture, proper validation, and production-ready error handling.

### Features

- Employee management via RESTful API
- Comprehensive input validation
- Support for both JSON and form data requests
- SQLite database (can be configured for MySQL)
- Clean, maintainable code structure

## Requirements

- PHP >= 8.2
- Composer
- SQLite (default) or MySQL
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

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

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

**Note:** For MySQL, update the database configuration:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=legacy_hr_api
DB_USERNAME=root
DB_PASSWORD=
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

### SQLite (Default)

The application uses SQLite by default. The database file will be created automatically at `database/database.sqlite` when you run migrations.

```bash
php artisan migrate
```

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

1. **Database:** SQLite is used by default for simplicity. Can be easily switched to MySQL.
2. **Validation:** All employee fields are required and validated according to business rules.
3. **Email Uniqueness:** Email addresses must be unique across all employees.
4. **Salary Format:** Salary is stored as a decimal with 2 decimal places.
5. **API Format:** All responses follow a consistent JSON structure with `success`, `message`, and `data`/`errors` fields.

## Testing

Run the test suite:

```bash
php artisan test
```

## License

This project is part of a legacy system and is maintained for production use.
