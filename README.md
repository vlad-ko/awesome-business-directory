# Awesome Business Directory 🏢

A Laravel application for business onboarding into an awesome business directory, built using **Test-Driven Development (TDD)**.

## 🎯 Project Overview

This application allows businesses to onboard themselves into a comprehensive directory with features like:
- Business registration and onboarding
- Comprehensive business profiles
- Verification system
- Search and filtering capabilities
- Modern, responsive UI

## 🧪 Development Approach: Test-Driven Development (TDD)

### TDD Rules We Follow:

**TDD Cycle:**
1. 🔴 **Red:** Write a failing test
2. 🟢 **Green:** Write minimal code to make test pass
3. 🔵 **Refactor:** Clean up code while keeping tests green

**Benefits:**
- ✅ Ensures functionality works as expected
- ✅ Provides documentation through tests
- ✅ Catches regressions early
- ✅ Forces good design decisions

### Technical Rules:
- **Always write tests first** before implementing functionality
- **Run tests frequently** to ensure they pass/fail as expected
- **Refactor only when tests are green**
- **Use terminal commands** instead of file editing tools in Docker environments

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- PHP 8.2+
- Composer

### Installation

1. **Clone and setup:**
   \`\`\`bash
   git clone <repository-url>
   cd awesome-business-directory
   \`\`\`

2. **Install dependencies:**
   \`\`\`bash
   composer install
   \`\`\`

3. **Start Laravel Sail:**
   \`\`\`bash
   ./vendor/bin/sail up -d
   \`\`\`

4. **Run migrations:**
   \`\`\`bash
   ./vendor/bin/sail artisan migrate
   \`\`\`

5. **Run tests:**
   \`\`\`bash
   ./vendor/bin/sail artisan test
   \`\`\`

### Access Points
- **Application:** http://localhost
- **MySQL:** localhost:3306
- **Vite Dev Server:** localhost:5173

## 🏗️ Application Architecture

### Routes
| Method | URI | Action | Description |
|--------|-----|--------| ----------- |
| GET | \`/\` | Welcome page | Landing page |
| GET | \`/onboard\` | BusinessOnboardingController@create | Show onboarding form |
| POST | \`/onboard\` | BusinessOnboardingController@store | Process business registration |
| GET | \`/businesses\` | Business listing | List all businesses |
| GET | \`/business/{business}\` | Individual business | Show single business |

### Controllers

#### BusinessOnboardingController
- **create()** - Displays the business onboarding form
- **store()** - Handles business registration with validation

## 🗄️ Database Schema

### businesses Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| business_name | varchar(255) | **Required** - Business name |
| business_slug | varchar(255) | Unique slug for URLs |
| description | text | **Required** - Business description |
| tagline | varchar(255) | Optional tagline |
| industry | varchar(255) | **Required** - Business industry |
| business_type | varchar(255) | **Required** - LLC, Corp, etc |
| founded_date | date | When business was founded |
| registration_number | varchar(255) | Official registration number |
| primary_email | varchar(255) | **Required** - Main contact email |
| phone_number | varchar(255) | **Required** - Contact phone |
| website_url | varchar(255) | Business website |
| street_address | varchar(255) | **Required** - Street address |
| city | varchar(255) | **Required** - City |
| state_province | varchar(255) | **Required** - State/Province |
| postal_code | varchar(255) | **Required** - ZIP/Postal code |
| country | varchar(255) | **Required** - Country |
| latitude | decimal(10,8) | GPS latitude |
| longitude | decimal(11,8) | GPS longitude |
| logo_path | varchar(255) | Path to business logo |
| business_hours | json | Operating hours |
| services_offered | json | List of services |
| employee_count | integer | Number of employees |
| facebook_url | varchar(255) | Facebook profile |
| twitter_url | varchar(255) | Twitter profile |
| instagram_url | varchar(255) | Instagram profile |
| linkedin_url | varchar(255) | LinkedIn profile |
| status | enum | pending/approved/rejected/suspended |
| is_verified | boolean | Verification status |
| is_featured | boolean | Featured listing status |
| verified_at | timestamp | When verification occurred |
| owner_name | varchar(255) | **Required** - Owner name |
| owner_email | varchar(255) | **Required** - Owner email |
| owner_phone | varchar(255) | Owner phone |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

### Indexes
- \`status, is_verified\` - For filtering businesses
- \`industry\` - For industry-based searches
- \`city, state_province\` - For location-based searches

## 🧪 Testing

### Running Tests

\`\`\`bash
# Run all tests
./vendor/bin/sail artisan test

# Run specific test class
./vendor/bin/sail artisan test --filter=BusinessOnboardingTest

# Run with coverage (if configured)
./vendor/bin/sail artisan test --coverage
\`\`\`

### Test Coverage

#### Business Onboarding Tests (BusinessOnboardingTest)
- ✅ **user_can_view_business_onboarding_form** - Ensures form displays correctly
- ✅ **user_can_submit_business_for_onboarding** - Tests successful business submission
- ✅ **business_requires_required_fields** - Validates form validation

### Test Results
- **Tests:** 3 passed
- **Assertions:** 10 passed
- **Duration:** ~0.4s

## 🔧 Development Guidelines

### TDD Development Process

1. **Write a failing test:**
   \`\`\`bash
   ./vendor/bin/sail artisan make:test FeatureNameTest
   \`\`\`

2. **Run tests to see failure:**
   \`\`\`bash
   ./vendor/bin/sail artisan test --filter=FeatureNameTest
   \`\`\`

3. **Write minimal code to pass:**
   - Implement controller methods
   - Create necessary models
   - Add routes
   - Create views

4. **Refactor and improve:**
   - Clean up code
   - Add documentation
   - Optimize performance

### File Editing in Docker

**⚠️ Important:** Due to Docker path resolution issues, use terminal commands instead of file editing tools:

\`\`\`bash
# Good: Use terminal commands
./vendor/bin/sail artisan make:controller MyController
./vendor/bin/sail artisan make:model MyModel -m

# Avoid: Direct file editing tools in Docker environments
\`\`\`

## 📝 API Documentation

### Business Onboarding

#### POST /onboard

Submit a new business for onboarding.

**Required Fields:**
- \`business_name\` (string, max:255)
- \`industry\` (string)
- \`description\` (string)
- \`primary_email\` (email)
- \`phone_number\` (string)
- \`street_address\` (string)
- \`city\` (string)
- \`state_province\` (string)
- \`postal_code\` (string)
- \`country\` (string)
- \`owner_name\` (string)
- \`owner_email\` (email)
- \`business_type\` (string)

**Response:**
- **Success:** 302 redirect with success message
- **Validation Error:** 422 with error details

## 🚀 Deployment

### Environment Setup

1. **Production Environment Variables:**
   \`\`\`env
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=your-db-host
   DB_DATABASE=your-db-name
   DB_USERNAME=your-db-user
   DB_PASSWORD=your-db-password
   \`\`\`

2. **Optimize for Production:**
   \`\`\`bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   \`\`\`

## 🤝 Contributing

1. **Follow TDD:** Always write tests first
2. **Run tests:** Ensure all tests pass before submitting
3. **Document changes:** Update relevant documentation
4. **Code style:** Follow Laravel conventions

## 📈 Project Status

### ✅ Completed Features
- [x] Business onboarding form
- [x] Form validation
- [x] Database schema
- [x] Test coverage
- [x] TDD implementation

### 🚧 In Progress
- [ ] Business listing view
- [ ] Individual business pages
- [ ] Admin verification system

### 📋 Planned Features
- [ ] Search and filtering
- [ ] Business image uploads
- [ ] Review system
- [ ] API endpoints
- [ ] Admin dashboard

## 🐛 Troubleshooting

### Common Issues

**1. Sail command not found:**
\`\`\`bash
# Make sure you're in the project directory
cd /path/to/awesome-business-directory
./vendor/bin/sail up
\`\`\`

**2. Database connection issues:**
\`\`\`bash
# Check if MySQL container is running
docker ps
# Restart services if needed
./vendor/bin/sail restart
\`\`\`

**3. Tests failing:**
\`\`\`bash
# Refresh database
./vendor/bin/sail artisan migrate:fresh
# Clear caches
./vendor/bin/sail artisan cache:clear
\`\`\`

## 📞 Support

For issues and questions:
1. Check this documentation
2. Review test files for examples
3. Check Laravel documentation
4. Run tests to ensure functionality

---

**Built with ❤️ using Laravel + TDD**
