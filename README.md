# Laravel Multi-Auth Web Application

A secure and optimized Laravel web application featuring multi-authentication, real-time WebSocket updates, and efficient bulk product import capabilities.

## Features

### 1. Multiple Authentication Guards
- **Admin Authentication**: Separate login, registration, and dashboard for administrators
- **Customer Authentication**: Separate login, registration, and dashboard for customers
- Uses Laravel's built-in multi-authentication system with `Auth::guard()`
- Route protection using middleware (`auth:admin`, `auth:customer`)

### 2. Product Management
- **CRUD Operations**: Full Create, Read, Update, Delete functionality for products
- **Product Fields**: name, description, price, image, category, stock
- **Bulk Import**: Import up to 100,000 products via CSV/Excel
- **Chunked Processing**: Processes imports in chunks of 1000 rows to prevent timeouts
- **Queue Processing**: Uses Laravel queues for background processing
- **Default Images**: Automatically assigns default image when not provided in CSV

### 3. Real-Time Updates (WebSockets)
- **Presence Channels**: Real-time online/offline presence tracking
- **Pusher Integration**: Uses Pusher for WebSocket broadcasting
- **Database Updates**: Presence status is stored and updated in the database
- **Admin Dashboard**: Shows real-time user presence (online/offline) for both Admins and Customers

### 4. Optimized Product Import
- **Large File Support**: Handles files up to 100k products
- **Chunked Reading**: Processes CSV/Excel in chunks of 1000 rows
- **Background Jobs**: Uses Laravel queues to prevent timeouts
- **Validation**: Validates each row before processing
- **Error Handling**: Skips invalid rows and continues processing

## Technology Stack

- **Framework**: Laravel 12
- **Database**: MySQL/SQLite (configurable)
- **Queue**: Database queue driver (configurable)
- **WebSockets**: Pusher
- **Excel/CSV Processing**: Maatwebsite/Excel
- **Frontend**: Blade templates with minimal styling

## Installation & Setup

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js and NPM (for assets)
- MySQL or SQLite database
- Pusher account (for WebSocket functionality)

### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd assesment
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Configuration
Copy the `.env.example` file to `.env`:
```bash
cp .env.example .env
```

Generate application key:
```bash
php artisan key:generate
```

### Step 4: Configure Environment Variables
Edit `.env` file and set the following:

```env
APP_NAME="Laravel Multi-Auth App"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database

PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=your_pusher_cluster
```

### Step 5: Run Migrations
```bash
php artisan migrate
```

### Step 6: Create Storage Link
```bash
php artisan storage:link
```

### Step 7: Create Default Product Image Directory
```bash
mkdir -p storage/app/public/products
# Place a default.png image in storage/app/public/products/
```

### Step 8: Start Queue Worker
In a separate terminal, start the queue worker:
```bash
php artisan queue:work
```

### Step 9: Start Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Multi-Authentication Strategy

### Guards Configuration
The application uses three authentication guards defined in `config/auth.php`:
- `web`: Default guard
- `admin`: For admin users
- `customer`: For customer users

### User Roles
Users have a `role` field that can be either:
- `admin`: Full access to product management and user presence
- `customer`: Limited access to customer dashboard

### Route Protection
Routes are protected using middleware:
- `auth:admin`: Ensures user is authenticated as admin
- `auth:customer`: Ensures user is authenticated as customer
- Custom middleware `admin` and `customer`: Additional role verification

### Example Routes
```
/admin/login          - Admin login page
/admin/register       - Admin registration
/admin/dashboard      - Admin dashboard (protected)
/admin/products       - Product management (protected)

/customer/login       - Customer login page
/customer/register    - Customer registration
/customer/dashboard   - Customer dashboard (protected)
```

## WebSocket Stack

### Pusher Configuration
The application uses **Pusher** for WebSocket broadcasting:

1. **Presence Channel**: `presence`
   - Tracks online/offline status of all users
   - Broadcasts `user.presence.changed` events

2. **Authentication**: 
   - Presence channel authentication handled via `/broadcasting/auth` endpoint
   - Uses Pusher's authentication mechanism

3. **Frontend Integration**:
   - Uses Pusher JavaScript SDK
   - Subscribes to presence channel on admin dashboard
   - Updates UI in real-time when users come online/offline

### Database Updates
- `user_presence` table stores current status
- Updated via `UpdateUserPresence` listener when events are broadcast
- Status can be `online` or `offline`
- `last_seen_at` timestamp tracks when status was last updated

## Bulk Import Implementation

### Architecture
1. **File Upload**: Admin uploads CSV/Excel file via web interface
2. **Job Dispatch**: File path is passed to `ProcessProductImport` job
3. **Chunked Processing**: 
   - Uses `WithChunkReading` to read 1000 rows at a time
   - Uses `WithBatchInserts` to insert 1000 rows at a time
4. **Validation**: Each row is validated before insertion
5. **Error Handling**: Invalid rows are skipped using `SkipsOnFailure`

### CSV Format
The CSV file should have the following columns:
- `name` (required): Product name
- `description` (optional): Product description
- `price` (required): Product price (numeric)
- `image` (optional): Image path/URL (defaults to `products/default.png`)
- `category` (optional): Product category
- `stock` (optional): Stock quantity (defaults to 0)

### Sample File
See `products_sample_import.csv` in the root directory for an example format.

### Performance Optimizations
- **Chunked Reading**: Prevents memory exhaustion
- **Batch Inserts**: Reduces database queries
- **Queue Processing**: Prevents HTTP timeouts
- **Background Jobs**: Allows processing of large files without blocking

## Testing

### Running Tests
```bash
php artisan test
```

### Test Coverage

#### Feature Tests (`tests/Feature/ProductTest.php`)
- Admin can create products
- Admin can view products
- Admin can update products
- Admin can delete products

#### Unit Tests (`tests/Unit/ProductImportTest.php`)
- Product import validation
- Default image handling
- Price parsing from various formats
- Job queueability
- File processing

### Running Specific Tests
```bash
# Run feature tests only
php artisan test --testsuite=Feature

# Run unit tests only
php artisan test --testsuite=Unit

# Run specific test class
php artisan test tests/Feature/ProductTest.php
```

## Architectural Decisions

### 1. Single Users Table with Role
- **Decision**: Use a single `users` table with a `role` enum field
- **Rationale**: Simpler schema, easier to maintain, sufficient for this use case
- **Alternative Considered**: Separate `admins` and `customers` tables (rejected for simplicity)

### 2. Queue Driver: Database
- **Decision**: Use database queue driver
- **Rationale**: No additional infrastructure required, works out of the box
- **Alternative Considered**: Redis (better performance but requires Redis server)

### 3. WebSocket: Pusher
- **Decision**: Use Pusher for WebSocket broadcasting
- **Rationale**: Easy setup, reliable, good documentation
- **Alternative Considered**: Laravel Echo Server (requires self-hosting)

### 4. Excel Library: Maatwebsite/Excel
- **Decision**: Use Maatwebsite/Excel for CSV/Excel processing
- **Rationale**: Well-maintained, supports chunked reading, good Laravel integration
- **Note**: The installed version uses phpexcel (legacy), but works for this use case

### 5. Presence Tracking: Database + Broadcasting
- **Decision**: Store presence in database AND broadcast via WebSocket
- **Rationale**: 
  - Database provides persistence and queryability
  - Broadcasting provides real-time updates
  - Best of both worlds

## Performance Considerations

1. **Product Import**:
   - Chunked processing prevents memory issues
   - Queue processing prevents timeouts
   - Batch inserts reduce database load

2. **Presence Updates**:
   - Updates are queued to prevent blocking
   - Database updates are efficient with indexed user_id

3. **Pagination**:
   - Product listing uses pagination (20 per page)
   - Reduces query load for large datasets

## Security Considerations

1. **Authentication**:
   - Password hashing using bcrypt
   - CSRF protection on all forms
   - Session-based authentication

2. **Authorization**:
   - Middleware ensures only authorized users access routes
   - Role-based access control

3. **File Uploads**:
   - File type validation (CSV, Excel only)
   - File size limits (10MB max)
   - Files stored securely in storage directory

## Troubleshooting

### Queue Not Processing
- Ensure queue worker is running: `php artisan queue:work`
- Check queue connection in `.env`: `QUEUE_CONNECTION=database`
- Check failed jobs: `php artisan queue:failed`

### WebSocket Not Working
- Verify Pusher credentials in `.env`
- Check browser console for errors
- Ensure `/broadcasting/auth` route is accessible
- Verify presence channel subscription

### Import Failing
- Check file format matches expected CSV structure
- Verify queue worker is running
- Check logs: `storage/logs/laravel.log`
- Ensure storage directory is writable

## License

This project is created for assessment purposes.

## Author

Laravel Developer Assessment
