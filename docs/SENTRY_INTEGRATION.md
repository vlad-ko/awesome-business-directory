# Sentry Integration Guide

This guide covers the Sentry integration for the Awesome Business Directory, focusing on **Critical Experience (CE) tracking** for actionable insights.

## 🎯 Integration Philosophy

Our Sentry integration follows these principles:
1. **Focus on Critical Experiences** - Track what matters for business outcomes
2. **Reduce Noise** - Only log actionable errors and performance issues
3. **Leverage Auto-instrumentation** - Use Sentry's built-in features
4. **Performance Aware** - Minimal overhead in production

## 📊 What We Track

### Critical User Journeys

1. **Business Discovery Path**
   - Homepage → Browse → View Business → Contact
   - Track: Start, business views, conversions

2. **Business Onboarding Path**
   - Start → Steps 1-4 → Review → Success
   - Track: Start, step completions, abandonments, conversions

3. **Admin Operations**
   - Login → Dashboard → Approve/Reject
   - Track: Critical actions only (approve/reject)

### Performance Thresholds

- Page loads > 3 seconds
- Database queries > 1 second
- API calls > 2 seconds

## 🛠️ Implementation

### Backend (Laravel)

#### Critical Experience Tracker
```php
use App\Services\CriticalExperienceTracker;

// Track discovery journey
CriticalExperienceTracker::trackDiscoveryStart();
CriticalExperienceTracker::trackBusinessViewed($business);
CriticalExperienceTracker::trackBusinessContact($business, 'website');

// Track onboarding
CriticalExperienceTracker::trackOnboardingStart();
CriticalExperienceTracker::trackOnboardingStepComplete($step);
CriticalExperienceTracker::trackOnboardingComplete($business);

// Track critical errors
CriticalExperienceTracker::trackCriticalError(
    'business_discovery',
    'listing_slow',
    new Exception('Page load exceeded threshold'),
    ['response_time_ms' => 3500]
);
```

#### Performance Tracking
```php
use App\Services\SentryLogger;

// Track operations with automatic span management
SentryLogger::trackBusinessOperation('listing', [
    'page' => 'index',
    'search_term' => $searchTerm,
], function ($span) {
    // Your business logic here
    return $response;
});
```

### Frontend (JavaScript)

#### Critical Tracking Module
```javascript
import { CriticalFrontendTracker } from './critical-tracking';

// Track discovery
CriticalFrontendTracker.trackDiscoveryStart();
CriticalFrontendTracker.trackBusinessViewed(businessId, businessName);
CriticalFrontendTracker.trackBusinessContact(businessId, 'phone');

// Track errors
CriticalFrontendTracker.trackCriticalError(
    'business_discovery',
    'search_failed',
    error
);
```

## 🔧 Configuration

### Environment Variables
```env
# Core Settings
SENTRY_LARAVEL_DSN=your_dsn_here
SENTRY_ENVIRONMENT=production
SENTRY_TRACES_SAMPLE_RATE=0.1  # 10% in production
SENTRY_PROFILES_SAMPLE_RATE=0.1

# Auto-instrumentation (all enabled)
SENTRY_TRACE_SQL_QUERIES_ENABLED=true
SENTRY_TRACE_VIEWS_ENABLED=true
SENTRY_TRACE_HTTP_CLIENT_REQUESTS_ENABLED=true
SENTRY_TRACE_CACHE_ENABLED=true
```

### Sampling Strategy
```php
// config/sentry.php
'traces_sampler' => function (\Sentry\Tracing\SamplingContext $context): float {
    // Always sample critical paths
    $transactionName = $context->getTransactionContext()->getName();
    
    if (str_contains($transactionName, 'onboarding')) {
        return 1.0; // 100% for onboarding
    }
    
    if (str_contains($transactionName, 'admin')) {
        return 0.5; // 50% for admin
    }
    
    // 10% for everything else
    return 0.1;
},
```

## 📈 Sentry Dashboard Setup

### Recommended Alerts

1. **Slow Page Load**
   - Condition: P95 duration > 3s
   - Filter: transaction.name contains "business"

2. **Onboarding Abandonment**
   - Condition: Count of "onboarding.abandoned" > 10/hour
   - Action: Notify product team

3. **Critical Errors**
   - Condition: Error with tag "critical.error:true"
   - Action: Page on-call engineer

### Custom Dashboards

1. **Business Discovery Funnel**
   - Discovery starts
   - Business views
   - Contact conversions
   - Drop-off rates

2. **Onboarding Success**
   - Step completion rates
   - Time per step
   - Total conversion rate
   - Industry breakdown

## 🚀 Best Practices

### DO:
- ✅ Use `CriticalExperienceTracker` for user journey milestones
- ✅ Let Sentry auto-instrument database, cache, HTTP calls
- ✅ Set meaningful tags for filtering
- ✅ Use breadcrumbs for context
- ✅ Track business metrics, not just technical ones

### DON'T:
- ❌ Log every user interaction
- ❌ Track non-actionable errors
- ❌ Create spans for auto-instrumented operations
- ❌ Use 100% sampling in production
- ❌ Store PII in Sentry

## 🐛 Debugging

### Local Development
```bash
# Enable Spotlight for local debugging
SENTRY_SPOTLIGHT=true

# View Sentry events locally
open http://localhost:8969
```

### Production Issues
1. Check Sentry dashboard for errors
2. Review breadcrumbs for context
3. Check performance tab for slow operations
4. Use discover for custom queries

## 📚 Additional Resources

- [Sentry Laravel SDK Docs](https://docs.sentry.io/platforms/php/guides/laravel/)
- [Performance Monitoring Guide](https://docs.sentry.io/product/performance/)
- [Best Practices](./SENTRY_BEST_PRACTICES.md)
- [Optimization Recommendations](./SENTRY_OPTIMIZATION_RECOMMENDATIONS.md)