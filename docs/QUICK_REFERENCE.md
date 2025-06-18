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
# Run all tests
./vendor/bin/sail artisan test

# Run specific test file
./vendor/bin/sail artisan test tests/Feature/BusinessListingTest.php

# Run tests with coverage
./vendor/bin/sail artisan test --coverage

# Run specific test method
./vendor/bin/sail artisan test --filter=user_can_view_business_listing_page
```

### Database Operations
```bash
# Run migrations
./vendor/bin/sail artisan migrate

# Fresh migration (drops all tables)
./vendor/bin/sail artisan migrate:fresh

# Seed database
./vendor/bin/sail artisan db:seed

# Fresh migration with seeding
./vendor/bin/sail artisan migrate:fresh --seed

# Check migration status
./vendor/bin/sail artisan migrate:status
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
```

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

## 📁 Important File Locations

### Configuration
- `config/app.php` - Application configuration
- `config/database.php` - Database configuration
- `.env` - Environment variables
- `phpunit.xml` - Test configuration

### Application Structure
- `app/Http/Controllers/` - Controllers
- `app/Models/` - Eloquent models
- `app/Http/Requests/` - Form request validation
- `resources/views/` - Blade templates
- `routes/web.php` - Web routes
- `database/migrations/` - Database migrations
- `tests/Feature/` - Feature tests

### Frontend Assets
- `resources/css/app.css` - Main CSS file
- `resources/js/app.js` - Main JavaScript file
- `public/build/` - Compiled assets (auto-generated)
- `tailwind.config.js` - Tailwind configuration
- `vite.config.js` - Vite build configuration

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
// Feature test structure
/** @test */
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
```

## 🌐 URLs and Access Points

### Application URLs
- **Home**: http://localhost
- **Business Onboarding**: http://localhost/onboard
- **Business Listing**: http://localhost/businesses

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
```

### Current Implementation Status
- ✅ Business Onboarding (7 tests passing)
- ✅ Business Listing (5 tests passing)
- ✅ Empty State Handling
- ✅ Responsive UI Design
- ⚠️ 1 failing test (slug uniqueness edge case)

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
```

## 📝 Code Style Guidelines

### Controller Methods
```php
// Keep controllers thin
public function index()
{
    $businesses = Business::approved()->orderedForListing()->get();
    return view('businesses.index', compact('businesses'));
}
```

### Model Scopes
```php
// Use descriptive scope names
public function scopeApproved($query)
{
    return $query->where('status', 'approved');
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
```

---

💡 **Tip**: Bookmark this page for quick access to common commands and troubleshooting steps! 