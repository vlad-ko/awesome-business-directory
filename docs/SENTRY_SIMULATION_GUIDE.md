# Sentry Critical Experience Simulation Guide

This guide explains how to use the simulation commands to generate realistic user traffic for testing and visualizing Critical Experience tracking in Sentry.

## 🎯 Purpose

The simulation commands help you:
- Generate realistic user behavior data
- Test Critical Experience tracking
- Populate Sentry dashboards with meaningful metrics
- Validate conversion funnels and drop-off rates

## 📊 Available Commands

### 1. Business Discovery Simulation

Simulates visitors browsing and contacting businesses:

```bash
./vendor/bin/sail artisan simulate:discovery --count=200 --view-rate=0.4 --contact-rate=0.15
```

**Options:**
- `--count`: Number of visitors to simulate (default: 200)
- `--view-rate`: Percentage who view businesses (default: 0.4 = 40%)
- `--contact-rate`: Percentage of viewers who contact (default: 0.15 = 15%)

**What it simulates:**
- Users arriving at the site
- Browsing business listings
- Viewing individual businesses
- Contacting businesses (conversion)

### 2. Business Onboarding Simulation

Simulates users registering their businesses:

```bash
./vendor/bin/sail artisan simulate:onboarding --count=100 --drop-rate=0.3 --error-rate=0.05
```

**Options:**
- `--count`: Number of users to simulate (default: 100)
- `--drop-rate`: Base abandonment rate (default: 0.3 = 30%)
- `--error-rate`: Error occurrence rate (default: 0.05 = 5%)

**What it simulates:**
- Multi-step form completion
- Drop-offs at each step
- Validation errors
- Successful completions

### 3. Combined Simulation

Run both journeys with realistic patterns:

```bash
./vendor/bin/sail artisan simulate:all --realistic --discovery-count=200 --onboarding-count=50
```

**Options:**
- `--realistic`: Use real-world traffic patterns
- `--discovery-count`: Discovery visitors (default: 200)
- `--onboarding-count`: Onboarding users (default: 50)

### 4. Seed Test Businesses

Create businesses for simulation:

```bash
./vendor/bin/sail artisan seed:simulation-businesses --count=20 --featured=5 --verified=8
```

## 📈 Realistic Traffic Patterns

When using `--realistic` flag:

**Discovery Journey:**
- 80% of visitors start browsing
- 35% view businesses
- 12% of viewers contact (4.2% overall conversion)

**Onboarding Journey:**
- Step 1: 15% drop-off
- Step 2: 25% drop-off
- Step 3: 20% drop-off
- Step 4: 10% drop-off
- ~3% error rate
- ~45% overall completion rate

## 🔍 Viewing Results in Sentry

### Performance Dashboard
1. Go to **Performance** → **Transactions**
2. Filter by:
   - Transaction: `business.*`
   - Tags: `simulation:true`

### Critical Experience Metrics
1. Go to **Discover**
2. Query for:
   - `critical.experience:*`
   - `critical.checkpoint:*`
   - `critical.conversion:*`

### Funnel Analysis
1. Check breadcrumbs for user journeys
2. Look for patterns in:
   - `critical.discovery` events
   - `critical.onboarding` events
   - `critical.error` events

### Custom Dashboards
Create widgets for:
- Conversion rates by checkpoint
- Drop-off analysis
- Error patterns
- Performance by business type

## 💡 Tips for Effective Simulation

### Start Small
```bash
# Test with small numbers first
./vendor/bin/sail artisan simulate:discovery --count=10
```

### Gradual Increase
```bash
# Then scale up
./vendor/bin/sail artisan simulate:all --realistic --discovery-count=500 --onboarding-count=100
```

### Continuous Traffic
```bash
# Run in background for continuous data
./vendor/bin/sail artisan simulate:all --realistic &
```

### Clean Test Data
```bash
# Remove simulated businesses after testing
./vendor/bin/sail artisan tinker
>>> App\Models\Business::where('owner_email', 'like', '%example.%')->delete();
```

## 🎨 Customizing Simulations

### Adjust Drop-off Rates
Edit `SimulateOnboardingTraffic.php`:
```php
private const STEP_DROP_RATES = [
    1 => 0.15,  // Adjust these values
    2 => 0.25,
    3 => 0.20,
    4 => 0.10,
];
```

### Add Custom Behaviors
Extend the simulation commands to add:
- Different user personas
- Time-based patterns
- Geographic variations
- Device-specific behaviors

## 🚨 Important Notes

1. **Development Only**: These commands are for development/testing
2. **Resource Usage**: Large simulations can be resource-intensive
3. **Data Cleanup**: Remember to clean up test data
4. **Rate Limits**: Be mindful of Sentry rate limits

## 📊 Expected Outcomes

After running simulations, you should see in Sentry:

1. **Transaction data** showing performance metrics
2. **Breadcrumb trails** for user journeys
3. **Error patterns** at critical checkpoints
4. **Conversion funnels** with realistic drop-offs
5. **Performance measurements** for each step

This data helps validate that Critical Experience tracking is working correctly and provides realistic visualizations for stakeholder demos.
