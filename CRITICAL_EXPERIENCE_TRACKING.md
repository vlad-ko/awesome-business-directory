# Critical Experience Tracking Strategy

## 🎯 Critical User Paths

### 1. Business Discovery Path (Consumer Journey)
**Critical Path**: `Homepage → Browse Businesses → View Business → Contact Business`

**What to Track**:
- **Page Load**: Homepage view (once per session)
- **Browse Intent**: First interaction with business listing
- **Business Interest**: Click on specific business
- **Conversion**: Contact/website click from business page

**What NOT to Track**:
- ❌ Every search keystroke
- ❌ Filter changes
- ❌ Scroll events
- ❌ Component initializations

### 2. Business Onboarding Path (Business Owner Journey)
**Critical Path**: `Homepage → Start Onboarding → Complete Steps → Submit → Success`

**What to Track**:
- **Onboarding Start**: First step view
- **Step Completion**: Each step submission (success/failure)
- **Drop-off Points**: Where users abandon
- **Final Submission**: Success/failure with timing
- **Validation Errors**: Only on final submit

**What NOT to Track**:
- ❌ Every field interaction
- ❌ Back navigation between steps
- ❌ Individual field validations
- ❌ UI performance metrics

### 3. Admin Approval Path (Admin Journey)
**Critical Path**: `Login → Dashboard → Review → Approve/Reject`

**What to Track**:
- **Admin Login**: Success/failure
- **Business Review**: Which business viewed
- **Critical Actions**: Approve/Reject with reasons
- **Bulk Actions**: If multiple businesses processed

**What NOT to Track**:
- ❌ Dashboard refresh
- ❌ Toggle featured/verified (unless bulk)
- ❌ Navigation between pages

## 📊 Implementation Strategy

### Backend (Laravel)

```php
// Only track critical checkpoints
class CriticalExperienceTracker
{
    // Consumer Journey
    public static function trackBusinessDiscoveryStart() {}
    public static function trackBusinessViewed($businessId) {}
    public static function trackBusinessContact($businessId, $method) {}
    
    // Onboarding Journey
    public static function trackOnboardingStart() {}
    public static function trackOnboardingStepComplete($step, $success) {}
    public static function trackOnboardingComplete($businessId, $duration) {}
    public static function trackOnboardingAbandoned($lastStep, $duration) {}
    
    // Admin Journey
    public static function trackAdminAction($action, $businessId, $result) {}
}
```

### Frontend (JavaScript)

```javascript
// Simplified tracking focused on conversions
const CriticalTracking = {
    // Only track meaningful interactions
    businessViewed(businessId) {
        // One event per business view
    },
    
    onboardingMilestone(milestone) {
        // Only major milestones: start, 50%, complete
    },
    
    conversionEvent(type, metadata) {
        // Only track actual conversions
    }
};
```

## 🔧 Sentry Configuration

### Sampling Strategy

```env
# Production settings
SENTRY_TRACES_SAMPLE_RATE=0.1  # 10% for general transactions
SENTRY_PROFILES_SAMPLE_RATE=0.01  # 1% for profiling

# Critical paths get 100% sampling via dynamic sampling
```

### Dynamic Sampling Rules

```javascript
Sentry.init({
    tracesSampler: (samplingContext) => {
        // 100% for critical paths
        if (samplingContext.name.includes('onboarding_submit') ||
            samplingContext.name.includes('business_contact') ||
            samplingContext.name.includes('admin_approve')) {
            return 1.0;
        }
        
        // 10% for browsing
        if (samplingContext.name.includes('listing')) {
            return 0.1;
        }
        
        // 1% for everything else
        return 0.01;
    }
});
```

## 📉 Noise Reduction Checklist

- [ ] Remove all `console.log` tracking
- [ ] Remove component initialization tracking
- [ ] Remove UI performance metrics (except critical pages)
- [ ] Consolidate duplicate tracking between services
- [ ] Remove micro-interaction tracking
- [ ] Implement sampling for non-critical paths
- [ ] Focus breadcrumbs on user actions, not system events

## 🎯 Success Metrics

Track these KPIs to measure critical experience health:

1. **Onboarding Funnel**:
   - Start → Step 1: X%
   - Step 1 → Step 2: X%
   - Step 2 → Step 3: X%
   - Step 3 → Step 4: X%
   - Step 4 → Complete: X%

2. **Business Discovery**:
   - Homepage → Listing: X%
   - Listing → Business View: X%
   - Business View → Contact: X%

3. **Admin Efficiency**:
   - Pending → Reviewed: X%
   - Reviewed → Actioned: X%
   - Time to Action: X minutes

## 🚀 Migration Plan

1. **Phase 1**: Add new focused tracking (1 day)
2. **Phase 2**: Remove noisy tracking (1 day)
3. **Phase 3**: Implement sampling rules (1 day)
4. **Phase 4**: Monitor and adjust (ongoing)

This approach will give us **signal, not noise** - focusing on what matters for business success.
