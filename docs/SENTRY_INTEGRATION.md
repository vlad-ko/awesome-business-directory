# Sentry Integration - Critical Experience Tracking

This guide explains our **Critical Experience (CE) tracking** approach using Sentry for the Awesome Business Directory. Instead of tracking everything, we focus on key user journeys that matter for business outcomes.

## 🎯 What is Critical Experience Tracking?

**Critical Experience tracking** means monitoring only the most important user paths through your application - the ones that directly impact conversions and business success.

### Think of it like this:
- **Traditional monitoring**: Track every click, page view, and interaction (lots of noise)
- **Critical Experience tracking**: Track only the journey from "interested user" to "successful conversion" (pure signal)

## 🛣️ Our Critical Paths

We track 3 key user journeys:

### 1. **Business Discovery Journey** (Consumer Path)
```
Homepage → Browse Businesses → View Business → Contact Business ✅
```
**What we track**: Discovery start, business views, contact conversions

### 2. **Business Onboarding Journey** (Business Owner Path)  
```
Landing → Step 1 → Step 2 → Step 3 → Step 4 → Success ✅
```
**What we track**: Onboarding start, step completions, abandonments, final conversion

### 3. **Admin Operations** (Admin Path)
```
Login → Dashboard → Approve/Reject Business ✅
```
**What we track**: Critical admin actions only (approve/reject)

## 🔍 What We Capture

### Critical Checkpoints
- **Discovery Start**: User shows intent to browse businesses
- **Business View**: User clicks to see business details
- **Contact**: User takes action to contact a business (CONVERSION)
- **Onboarding Start**: User begins registration process
- **Step Complete**: User finishes each onboarding step
- **Onboarding Success**: User completes full registration (CONVERSION)

### Performance Metrics
- Page load times > 3 seconds
- Database queries > 1 second
- Step completion durations
- Error patterns at critical points

### Context Data
- Business type (featured vs regular)
- Contact methods (website, phone, email)
- Industry information
- User session tracking

## 🛠️ How We Implement It

### Backend (Laravel)

**Critical Experience Tracker**
```php
use App\Services\CriticalExperienceTracker;

// Track discovery
CriticalExperienceTracker::trackDiscoveryStart();
CriticalExperienceTracker::trackBusinessViewed($business);
CriticalExperienceTracker::trackBusinessContact($business, 'website');

// Track onboarding  
CriticalExperienceTracker::trackOnboardingStart();
CriticalExperienceTracker::trackOnboardingStepComplete($step);
CriticalExperienceTracker::trackOnboardingComplete($business);

// Track critical errors only
CriticalExperienceTracker::trackCriticalError(
    'business_discovery',
    'listing_slow',
    $error,
    ['response_time_ms' => 3500]
);
```

**What This Creates in Sentry:**
- **Breadcrumbs**: Trail of user actions leading to events
- **Child Spans**: Measurable operations with timing
- **Tags**: Filterable metadata (checkpoint, business type)
- **Measurements**: Custom metrics for analysis

### Frontend (JavaScript)

**Critical Frontend Tracker**
```javascript
import { CriticalFrontendTracker } from './critical-tracking';

// Track discovery
CriticalFrontendTracker.trackDiscoveryStart();
CriticalFrontendTracker.trackBusinessViewed(businessId, businessName);
CriticalFrontendTracker.trackBusinessContact(businessId, 'phone');

// Track onboarding
CriticalFrontendTracker.trackOnboardingStart();
CriticalFrontendTracker.trackOnboardingStepComplete(step);
CriticalFrontendTracker.trackOnboardingAbandoned(lastStep);
```

**What This Creates:**
- **Session tracking**: Prevents duplicate events
- **Breadcrumbs**: User journey context
- **Error capture**: When critical paths fail

## 📊 Visualizing in Sentry

### Accessing Your Data

**Go to Sentry → Explore → Traces**

### Key Queries

**Discovery Funnel:**
```
span.op:critical.discovery.start OR span.op:critical.discovery.view OR span.op:critical.discovery.conversion
```

**Onboarding Funnel:**
```
span.op:critical.onboarding.start OR span.op:critical.onboarding.step_1 OR span.op:critical.onboarding.step_2 OR span.op:critical.onboarding.step_3 OR span.op:critical.onboarding.step_4
```

**Business Performance:**
```
span.op:critical.discovery.view
```
Group by: `business.name` or `business.featured`

**Contact Methods:**
```
span.op:critical.discovery.conversion
```
Group by: `contact.method`

### Creating Funnel Visualizations

1. **In Visualize section**: Add `count()` grouped by `span.op`
2. **Chart type**: Bar chart shows funnel drop-offs clearly
3. **Time range**: Last hour/day to see trends
4. **Multiple series**: Compare different time periods

### Example Results
From our simulation data:
- **Discovery**: 44 starts → 54 views → 16 conversions (36% overall conversion)
- **Onboarding**: 50 starts → 43 step 1 → 28 step 2 → 21 step 3 → 16 step 4 (32% completion)

## 🚀 Generating Test Data

We've built simulation commands to populate Sentry with realistic data:

```bash
# Discovery simulation
./vendor/bin/sail artisan simulate:discovery --count=50 --view-rate=0.6 --contact-rate=0.3

# Onboarding simulation  
./vendor/bin/sail artisan simulate:onboarding --count=40 --drop-rate=0.25 --error-rate=0.03

# Combined realistic simulation
./vendor/bin/sail artisan simulate:all --realistic
```

**Web endpoint for testing:**
```
GET /test/sentry-spans
```

## 🔧 Configuration

### Environment Setup
```env
SENTRY_TRACES_SAMPLE_RATE=1.0  # 100% for development
SENTRY_PROFILES_SAMPLE_RATE=1.0
SENTRY_ENABLE_LOGS=true
```

### Auto-instrumentation Enabled
- SQL queries
- HTTP requests
- Cache operations
- View rendering

## 💡 Why This Approach Works

### Business Benefits
1. **Clear ROI**: Focus on revenue-impacting metrics
2. **Actionable Insights**: See exactly where users drop off
3. **Optimization Targets**: Know which steps need improvement
4. **Conversion Tracking**: Measure what matters

### Technical Benefits
1. **Reduced Noise**: Only meaningful events
2. **Better Performance**: Less overhead
3. **Easier Debugging**: Clear breadcrumb trails
4. **Scalable**: Works with high traffic

## 🎨 Dashboard Examples

### Discovery Dashboard
- **Funnel Chart**: Start → View → Convert over time
- **Business Performance**: Which businesses get most views/contacts
- **Contact Method**: Phone vs website vs email preferences

### Onboarding Dashboard  
- **Step Completion**: Where users drop off most
- **Time Analysis**: Which steps take longest
- **Error Patterns**: What causes onboarding failures

### Performance Dashboard
- **Critical Path Speed**: How fast are key operations
- **Error Rates**: Critical failures over time
- **Conversion Trends**: Success rates by time period

## 🚨 Alerts You Should Set

1. **Conversion Rate Drop**: When start-to-contact rate falls below 20%
2. **High Abandonment**: When onboarding completion drops below 30%
3. **Critical Errors**: Any error blocking key paths
4. **Slow Performance**: When key operations exceed 3 seconds

## 🎯 Success Metrics

With this approach, you can answer questions like:
- **"Are people finding our businesses?"** → Check discovery start rates
- **"Why aren't people contacting businesses?"** → Analyze view-to-contact drop-off
- **"Where do people quit onboarding?"** → See step-by-step abandonment
- **"Which businesses perform best?"** → Group views by business attributes

This isn't just monitoring - it's **business intelligence** that directly impacts your bottom line.

---

## ⏰ Automated Traffic Simulation

The system automatically generates realistic traffic data for Sentry visualization:

**Hourly Simulation** (24/7):
- 50 discovery visitors + 20 onboarding users
- Realistic conversion rates and drop-offs
- Logs to: `storage/logs/simulation.log`

**Business Hours Boost** (9 AM - 5 PM, weekdays):
- Additional 20 discovery visitors every 15 minutes
- Simulates higher daytime traffic

**Manual Override:**
```bash
# Run simulation manually anytime
./vendor/bin/sail artisan simulate:all --realistic

# Custom parameters
./vendor/bin/sail artisan simulate:discovery --count=100 --view-rate=0.6 --contact-rate=0.3
```

**To Start the Scheduler:**
```bash
# In production, add to crontab:
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

# For development with Sail:
./vendor/bin/sail artisan schedule:work
```

## 📚 Quick Reference

**Query discovery:** `span.op:critical.discovery.*`  
**Query onboarding:** `span.op:critical.onboarding.*`  
**Create funnel:** Group by `span.op`, visualize `count()`  
**Find conversions:** `span.op:*.conversion`  
**Monitor scheduler:** `./vendor/bin/sail artisan schedule:list`