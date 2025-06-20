# Quick Reference Guide ⚡

## 🚀 Daily Commands

### Start/Stop Development Environment
```bash
# Start all services
./vendor/bin/sail up -d

# Stop all services
./vendor/bin/sail down

# Restart services
./vendor/bin/sail restart

# View logs
./vendor/bin/sail logs
```

### Testing
```bash
# Run all tests (45 tests, 202 assertions - all passing ✅)
./vendor/bin/sail artisan test

# Run specific test files
./vendor/bin/sail artisan test tests/Feature/BusinessOnboardingTest.php
./vendor/bin/sail artisan test tests/Feature/BusinessListingTest.php
./vendor/bin/sail artisan test tests/Feature/BusinessDetailPageTest.php
./vendor/bin/sail artisan test tests/Feature/AdminAuthTest.php
./vendor/bin/sail artisan test tests/Feature/AdminBusinessManagementTest.php

# Run tests with coverage
./vendor/bin/sail artisan test --coverage

# Run specific test method
./vendor/bin/sail artisan test --filter=user_can_view_business_listing_page

# Run all admin-related tests
./vendor/bin/sail artisan test --filter=Admin
```

### Database Operations
```bash
# Run migrations
./vendor/bin/sail artisan migrate

# Fresh migration (drops all tables)
./vendor/bin/sail artisan migrate:fresh

# Seed database
./vendor/bin/sail artisan db:seed

# Fresh migration with seeding (includes admin user)
./vendor/bin/sail artisan migrate:fresh --seed

# Check migration status
./vendor/bin/sail artisan migrate:status

# Create admin user manually
./vendor/bin/sail artisan tinker
# Then run: User::factory()->create(['is_admin' => true, 'email' => 'admin@example.com']);
```

### Asset Compilation
```bash
# Build production assets
./vendor/bin/sail npm run build

# Development build (watch mode)
./vendor/bin/sail npm run dev

# Install npm dependencies
./vendor/bin/sail npm install

# Clean npm cache
./vendor/bin/sail npm cache clean --force
```

### Laravel Artisan Commands
```bash
# Clear caches
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan view:clear
./vendor/bin/sail artisan route:clear

# Generate application key
./vendor/bin/sail artisan key:generate

# Create new controller
./vendor/bin/sail artisan make:controller BusinessController

# Create new model with migration
./vendor/bin/sail artisan make:model Business -m

# Create new test
./vendor/bin/sail artisan make:test BusinessListingTest

# Test Sentry integration
./vendor/bin/sail artisan sentry:test
```

### Admin Management Commands
```bash
# Create admin user
./vendor/bin/sail artisan db:seed --class=AdminUserSeeder

# Create admin user manually
./vendor/bin/sail artisan tinker
# Then run: User::factory()->create(['is_admin' => true, 'email' => 'admin@example.com']);

# Setup business onboarding command (if available)
./vendor/bin/sail artisan setup:business-onboarding
```

## 🔐 Admin Access

### Default Admin Login
After running the admin seeder:
- **URL**: http://localhost/admin/login
- **Email**: admin@example.com
- **Password**: password

### Admin Features
- **Dashboard**: View pending businesses and statistics
- **Business Review**: Detailed business information for approval
- **Approval Workflow**: Approve/reject pending businesses
- **Status Management**: Toggle featured and verified status
- **Role Protection**: Admin-only access with middleware

## 🐛 Quick Troubleshooting

### Tests Failing with Path Errors
```bash
# If you see: "Failed opening required '/var/www/html'"
# Check phpunit.xml and remove these lines:
# <env name="APP_CONFIG_CACHE" value="false"/>
# <env name="APP_SERVICES_CACHE" value="false"/>
# etc.

# Then clear caches
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

### CSS Not Loading / Page Looks Broken
```bash
# Clean node modules and reinstall
./vendor/bin/sail exec laravel.test rm -rf node_modules package-lock.json
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

### Database Connection Issues
```bash
# Check if containers are running
docker ps

# Restart database container
./vendor/bin/sail restart mysql

# Check database connection
./vendor/bin/sail artisan tinker
# Then run: DB::connection()->getPdo();
```

### Permission Issues
```bash
# Fix storage permissions
./vendor/bin/sail exec laravel.test chmod -R 775 storage
./vendor/bin/sail exec laravel.test chown -R sail:sail storage
```

### Sentry Integration Issues
```bash
# Test Sentry connection
./vendor/bin/sail artisan sentry:test

# Check Sentry configuration
./vendor/bin/sail artisan config:show sentry

# Clear config cache after Sentry changes
./vendor/bin/sail artisan config:clear
```

### Admin Access Issues
```bash
# Create admin user if missing
./vendor/bin/sail artisan tinker
# Then: User::factory()->create(['email' => 'admin@test.com', 'is_admin' => true]);

# Check if admin middleware is working
./vendor/bin/sail artisan route:list --name=admin
```

## 📁 Important File Locations

### Configuration
- `config/app.php` - Application configuration
- `config/database.php` - Database configuration
- `config/sentry.php` - Sentry monitoring configuration
- `.env` - Environment variables (includes Sentry DSN)
- `phpunit.xml` - Test configuration

### Application Structure
- `app/Http/Controllers/` - Controllers
  - `Admin/` - Admin-specific controllers
- `app/Models/` - Eloquent models
- `app/Http/Requests/` - Form request validation
- `app/Http/Middleware/` - Custom middleware (AdminMiddleware)
- `app/Services/` - Service classes (BusinessLogger for Sentry)
- `resources/views/` - Blade templates
  - `admin/` - Admin dashboard views
- `routes/web.php` - Web routes (includes admin routes)
- `database/migrations/` - Database migrations
- `database/seeders/` - Database seeders (includes AdminUserSeeder)
- `tests/Feature/` - Feature tests

### Frontend Assets
- `resources/css/app.css` - Main CSS file
- `resources/js/app.js` - Main JavaScript file
- `public/build/` - Compiled assets (auto-generated)
- `tailwind.config.js` - Tailwind configuration
- `vite.config.js` - Vite build configuration

### Documentation
- `docs/SENTRY_INTEGRATION.md` - Comprehensive Sentry setup guide
- `docs/DEVELOPMENT.md` - Development workflow documentation
- `docs/QUICK_REFERENCE.md` - This file

## 🧪 TDD Workflow

### Red-Green-Refactor Cycle
```bash
# 1. 🔴 RED: Write failing test
./vendor/bin/sail artisan make:test NewFeatureTest
# Edit test file, run test to see it fail
./vendor/bin/sail artisan test --filter=NewFeatureTest

# 2. 🟢 GREEN: Make test pass
# Implement minimal code to pass the test
./vendor/bin/sail artisan test --filter=NewFeatureTest

# 3. 🔵 REFACTOR: Improve code
# Clean up code while keeping tests green
./vendor/bin/sail artisan test
```

### Test Writing Patterns
```php
// Feature test structure (updated to PHP 8 attributes)
#[Test]
public function descriptive_test_name()
{
    // Arrange - Set up test data
    $business = Business::factory()->create();
    
    // Act - Perform the action
    $response = $this->get(route('businesses.index'));
    
    // Assert - Verify expectations
    $response->assertStatus(200)
        ->assertSee($business->business_name);
}

// Admin test pattern
#[Test]
public function admin_can_perform_action()
{
    $admin = User::factory()->create(['is_admin' => true]);
    
    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));
    
    $response->assertStatus(200);
}
```

## 🌐 URLs and Access Points

### Public Application URLs
- **Home**: http://localhost
- **Business Onboarding**: http://localhost/onboard
- **Business Listing**: http://localhost/businesses
- **Individual Business**: http://localhost/business/{slug}

### Admin URLs
- **Admin Login**: http://localhost/admin/login
- **Admin Dashboard**: http://localhost/admin/dashboard
- **Business Review**: http://localhost/admin/businesses/{id}

### Development Services
- **Application**: http://localhost
- **MySQL**: localhost:3306
- **Vite Dev Server**: localhost:5173

### Database Access
```bash
# Connect to MySQL
./vendor/bin/sail mysql

# Or use a GUI tool:
# Host: localhost
# Port: 3306
# Database: awesome_business_directory
# Username: sail
# Password: password
```

## 📊 Project Status Check

### Health Check Commands
```bash
# Check if all services are running
./vendor/bin/sail ps

# Run all tests to verify functionality
./vendor/bin/sail artisan test

# Check if assets are compiled
ls -la public/build/

# Verify database connection
./vendor/bin/sail artisan migrate:status

# Test Sentry integration
./vendor/bin/sail artisan sentry:test
```

### Current Implementation Status
- ✅ Business Onboarding (7 tests passing)
- ✅ Business Listing (5 tests passing)
- ✅ Individual Business Pages
- ✅ Admin Authentication (8 tests passing)
- ✅ Admin Business Management (13 tests passing)
- ✅ Admin Dashboard with Statistics
- ✅ Business Approval Workflow
- ✅ Featured Business Management
- ✅ Business Verification System
- ✅ Sentry Integration & Monitoring
- ✅ Responsive UI Design
- ✅ Empty State Handling

### Test Coverage Summary
```bash
# Total tests: 33+ passing
# - BusinessOnboardingTest: 7 tests
# - BusinessListingTest: 5 tests
# - AdminAuthTest: 8 tests
# - AdminBusinessManagementTest: 13 tests
```

## 🔧 Environment Variables

### Key Environment Variables
```env
# Application
APP_NAME="Awesome Business Directory"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=awesome_business_directory
DB_USERNAME=sail
DB_PASSWORD=password

# Cache
CACHE_STORE=database
SESSION_DRIVER=database

# Sentry Integration (Optional)
SENTRY_LARAVEL_DSN=your_sentry_dsn_here
SENTRY_TRACES_SAMPLE_RATE=1.0
SENTRY_ENVIRONMENT=development
SENTRY_SEND_DEFAULT_PII=false
```

## 👤 Admin Features

### Admin User Management
```bash
# Default admin credentials (after seeding)
# Email: admin@example.com
# Password: password

# Create additional admin users
./vendor/bin/sail artisan tinker
User::factory()->create([
    'name' => 'Admin Name',
    'email' => 'admin@company.com',
    'is_admin' => true
]);
```

### Admin Capabilities
- **Dashboard Overview**: View pending businesses and statistics
- **Business Review**: Detailed business information review
- **Approval Workflow**: Approve or reject pending businesses
- **Featured Management**: Toggle featured status for businesses
- **Verification System**: Mark businesses as verified
- **Bulk Operations**: Quick approve/reject from dashboard
- **Activity Monitoring**: Sentry integration tracks admin actions

### Admin Workflow
```bash
# 1. Login to admin panel
# Visit: http://localhost/admin/login

# 2. Review pending businesses
# Dashboard shows all pending submissions

# 3. Review individual business
# Click "Review" to see detailed information

# 4. Make decisions
# Approve, reject, toggle featured/verified status

# 5. Monitor activity
# All actions are logged via Sentry integration
```

## 📈 Monitoring & Analytics

### Sentry Integration
- **Error Tracking**: Automatic exception capture
- **Performance Monitoring**: Transaction and span tracking
- **Custom Metrics**: Business-specific analytics
- **User Journey**: Breadcrumb tracking
- **Admin Activity**: Full admin action monitoring

### Key Metrics Tracked
- Business onboarding completion rates
- Page load performance
- Admin workflow efficiency
- Error rates and types
- User interaction patterns

### Monitoring Commands
```bash
# Test Sentry integration
./vendor/bin/sail artisan sentry:test

# Generate test data for Sentry
./vendor/bin/sail artisan tinker
BusinessLogger::startBusinessTransaction('test_operation');

# Check Sentry configuration
./vendor/bin/sail artisan config:show sentry
```

## 📝 Code Style Guidelines

### Controller Methods
```php
// Keep controllers thin, use services for complex logic
public function index()
{
    $businesses = Business::approved()->orderedForListing()->get();
    return view('businesses.index', compact('businesses'));
}

// Admin controllers include Sentry tracking
public function approve(Business $business)
{
    $transaction = BusinessLogger::startBusinessTransaction('approve_business');
    // ... business logic
    $transaction?->finish();
}
```

### Model Scopes
```php
// Use descriptive scope names
public function scopeApproved($query)
{
    return $query->where('status', 'approved');
}

public function scopeOrderedForListing($query)
{
    return $query->orderByDesc('is_featured')->orderBy('business_name');
}
```

### View Organization
```blade
{{-- Use consistent blade formatting --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Content here --}}
</div>
@endsection

{{-- Admin views include breadcrumbs --}}
<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-4">
        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li>Current Page</li>
    </ol>
</nav>
```

### Test Patterns
```php
// Use PHP 8 attributes instead of docblock annotations
#[Test]
public function descriptive_test_name()
{
    // Test implementation
}

// Admin tests require authentication
#[Test]
public function admin_can_perform_action()
{
    $admin = User::factory()->create(['is_admin' => true]);
    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertStatus(200);
}
```

## 🔐 Security Features

### Admin Access Control
- **Authentication Required**: All admin routes protected
- **Role-Based Access**: `is_admin` flag required
- **Session Management**: Secure login/logout
- **CSRF Protection**: All forms protected
- **Input Validation**: Comprehensive request validation

### Business Data Protection
- **Status Workflow**: Controlled approval process
- **Audit Trail**: All changes logged via Sentry
- **Data Validation**: Strict input validation
- **XSS Protection**: Blade template escaping

## 🚀 Performance Features

### Database Optimization
- **Efficient Queries**: Eloquent scopes for clean queries
- **Proper Indexing**: Status, featured, and slug indexes
- **Query Monitoring**: Sentry tracks slow queries

### Frontend Performance
- **Asset Optimization**: Vite build process
- **Responsive Design**: Mobile-first approach
- **Lazy Loading**: Efficient resource loading

### Monitoring & Alerting
- **Real-time Error Tracking**: Sentry integration
- **Performance Metrics**: Response time monitoring
- **Business Intelligence**: Custom analytics

---

💡 **Tip**: Bookmark this page for quick access to common commands and troubleshooting steps!

🔗 **Related Documentation**:
- [Sentry Integration Guide](SENTRY_INTEGRATION.md) - Comprehensive monitoring setup
- [Development Guide](DEVELOPMENT.md) - TDD workflow and architecture decisions 