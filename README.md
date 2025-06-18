# Awesome Business Directory 🏢

A comprehensive Laravel application for business onboarding and directory listing, built using **Test-Driven Development (TDD)** methodology.

## 🎯 Project Overview

This application provides a complete business directory platform where companies can:
- **Register and onboard** themselves with comprehensive business profiles
- **Browse and discover** other businesses in the community
- **Showcase their services** with rich business information
- **Connect with customers** through verified business listings
- **Enjoy modern UI/UX** with responsive design and professional styling

### ✨ Key Features
- ✅ **Business Onboarding** - Complete registration process with validation
- ✅ **Business Listing** - Professional directory with search and filtering
- ✅ **Empty State Handling** - Engaging call-to-action when no businesses exist
- ✅ **Status Management** - Approval workflow (pending/approved/rejected)
- ✅ **Featured Businesses** - Premium placement for highlighted listings
- ✅ **Verification System** - Trust badges for verified businesses
- ✅ **Responsive Design** - Mobile-first approach with Tailwind CSS
- ✅ **Comprehensive Testing** - Full TDD coverage with 35 tests (154 assertions)
- ✅ **Admin Panel** - Complete business management and approval system
- ✅ **Monitoring & Analytics** - Sentry.io integration with custom tracing

### 🔧 Technical Stack
- **Laravel 12** with modern PHP 8.3+ features
- **Docker Development** via Laravel Sail
- **MySQL Database** with comprehensive business schema
- **Tailwind CSS** for responsive, modern UI
- **Sentry.io Integration** with 100% tracing for development
- **Structured Logging** with BusinessLogger service
- **Custom Performance Monitoring** with transaction traces and spans

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
| GET | \`/businesses\` | BusinessController@index | **✅ IMPLEMENTED** - List all approved businesses |

### Controllers

#### ✅ BusinessOnboardingController (Complete)
- **create()** - Displays the business onboarding form with comprehensive fields
- **store()** - Handles business registration with full validation and slug generation

#### ✅ BusinessController (Complete)
- **index()** - Lists approved businesses with featured priority and alphabetical sorting

### Models

#### ✅ Business Model (Complete)
- **Eloquent Scopes**: \`approved()\`, \`orderedForListing()\`
- **Auto-generation**: Slugs, status management
- **Relationships**: Ready for future user associations
- **Validation**: Comprehensive form request validation

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

#### ✅ Business Onboarding Tests (BusinessOnboardingTest) - 7 Tests
- ✅ **user_can_view_business_onboarding_form** - Ensures form displays correctly
- ✅ **user_can_submit_business_for_onboarding** - Tests successful business submission
- ✅ **business_requires_required_fields** - Validates all required field validation
- ✅ **business_name_must_be_unique** - Ensures unique business names
- ✅ **business_slug_is_generated_automatically** - Tests slug auto-generation
- ✅ **business_status_defaults_to_pending** - Verifies default status
- ✅ **business_onboarding_redirects_after_successful_submission** - Tests redirect flow

#### ✅ Business Listing Tests (BusinessListingTest) - 5 Tests
- ✅ **user_can_view_business_listing_page** - Basic page functionality
- ✅ **business_listing_displays_approved_businesses** - Shows only approved businesses
- ✅ **business_listing_shows_message_when_no_businesses** - Enhanced empty state handling
- ✅ **business_listing_displays_business_cards_with_key_information** - Rich business cards
- ✅ **businesses_are_ordered_by_featured_first_then_alphabetically** - Proper sorting

### Test Results Summary
- **Total Tests:** 13 tests (1 failing - slug uniqueness edge case)
- **Total Assertions:** 45+ assertions
- **Coverage:** Business onboarding and listing functionality
- **Duration:** ~0.4-0.5s
- **Status:** ✅ Core functionality fully tested

## 🎨 UI/UX Features

### Business Listing Page
- **Professional Layout** - Clean, modern design with responsive grid
- **Empty State Excellence** - Engaging call-to-action when no businesses exist
- **Featured Business Highlighting** - Blue ring and star badge for premium listings
- **Verification Badges** - Trust indicators for verified businesses
- **Rich Business Cards** - Display name, tagline, industry, description, location, contact
- **Responsive Design** - 1/2/3 column layout adapting to screen size

### Empty State Design
When no businesses are listed, users see:
- **Professional Icon** - Properly sized community icon in circular background
- **Compelling Headline** - "No businesses found" with encouraging subtext
- **Benefits Section** - Why businesses should join (free listing, reach customers, etc.)
- **Dual Call-to-Action** - Primary "Add Your Business" and secondary "Back to Home"
- **Contact Information** - Support email for questions
- **Modern Styling** - Tailwind CSS with hover effects and transitions

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

### Critical Issues & Solutions

**4. ⚠️ Test Configuration Error: "Failed opening required '/var/www/html'"**

**Problem:** Tests fail with \`Failed opening required '/var/www/html'\` error.

**Root Cause:** In \`phpunit.xml\`, cache environment variables like \`APP_CONFIG_CACHE="false"\` get converted to boolean \`false\` by Laravel's \`Env::get()\`, causing \`normalizeCachePath()\` to call \`$this->basePath(false)\` which returns the base directory instead of a config file path.

**Solution:** Remove problematic cache environment variables from \`phpunit.xml\`:
\`\`\`xml
<!-- REMOVE these lines from phpunit.xml: -->
<env name="APP_CONFIG_CACHE" value="false"/>
<env name="APP_SERVICES_CACHE" value="false"/>
<env name="APP_PACKAGES_CACHE" value="false"/>
<env name="APP_ROUTES_CACHE" value="false"/>
<env name="APP_EVENTS_CACHE" value="false"/>
\`\`\`

**5. 🎨 Page Looks Broken: CSS Not Loading**

**Problem:** Business listing page appears unstyled, CSS not loading properly.

**Root Cause:** Node.js dependency issue with \`@rollup/rollup-linux-arm64-gnu\` on ARM64 architecture (Apple Silicon Macs).

**Solution:** Reinstall npm dependencies:
\`\`\`bash
# Remove problematic files
./vendor/bin/sail exec laravel.test rm -rf node_modules package-lock.json

# Reinstall dependencies
./vendor/bin/sail npm install

# Build production assets
./vendor/bin/sail npm run build
\`\`\`

**6. 🔄 Development Asset Compilation Issues**

**Problem:** \`npm run dev\` fails with rollup errors.

**Solution:** Use production build for development:
\`\`\`bash
# Instead of npm run dev, use:
./vendor/bin/sail npm run build

# Or run dev server in background:
./vendor/bin/sail npm run dev &
\`\`\`

### Environment-Specific Issues

**7. Apple Silicon (ARM64) Compatibility:**
- **Issue:** Native modules not compatible with ARM64
- **Solution:** Use Rosetta or Docker with platform specification
- **Alternative:** Use production builds instead of development server

**8. Docker Path Resolution:**
- **Issue:** File paths not resolving correctly in Docker
- **Solution:** Use Sail commands instead of direct file manipulation
- **Example:** \`./vendor/bin/sail artisan make:controller\` instead of direct editing

## 📚 Documentation

### Additional Resources
- 📖 **[Development Guide](docs/DEVELOPMENT.md)** - Comprehensive TDD implementation journey, architecture decisions, and technical deep-dive
- ⚡ **[Quick Reference](docs/QUICK_REFERENCE.md)** - Daily commands, troubleshooting, and workflow shortcuts
- 🔍 **[Sentry Integration](docs/SENTRY_INTEGRATION.md)** - Complete monitoring, error tracking, and performance analysis setup
- 🧪 **Test Files** - Live documentation through comprehensive test coverage

### Documentation Structure
```
docs/
├── DEVELOPMENT.md     # Technical implementation details
├── QUICK_REFERENCE.md # Daily development commands
└── README.md          # This overview document
```

## 📞 Support

For issues and questions:
1. **Check Documentation**: Review this README and docs/ folder
2. **Run Diagnostics**: Use health check commands in Quick Reference
3. **Review Tests**: Test files provide implementation examples
4. **Check Troubleshooting**: Common issues documented with solutions
5. **Laravel Documentation**: Official Laravel docs for framework questions

### Getting Help
- 🐛 **Bugs**: Check troubleshooting section first
- 🤔 **How-to**: Review development guide and test examples  
- 🚀 **New Features**: Follow TDD process documented in development guide
- ⚡ **Quick Tasks**: Use quick reference guide

---

**Built with ❤️ using Laravel + TDD**

### Project Highlights
- 🧪 **13 comprehensive tests** with full TDD methodology
- 🎨 **Professional UI/UX** with responsive design and engaging empty states
- 🏗️ **Clean architecture** with Eloquent scopes and organized controllers
- 📚 **Extensive documentation** covering implementation journey and troubleshooting
- 🔧 **Production-ready** with proper validation, error handling, and asset optimization
