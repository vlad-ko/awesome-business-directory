# Sentry Optimization Recommendations

Based on review of our implementation against [Sentry Laravel SDK best practices](https://github.com/getsentry/sentry-laravel), here are the recommended improvements:

## 1. Use Built-in Laravel Integrations

The Sentry Laravel SDK automatically instruments many Laravel components. We should leverage these instead of manual tracking:

### Current (Manual) Approach:
```php
// We're manually creating spans for database operations
SentryLogger::trackDatabaseOperation('business_queries', function ($dbSpan) {
    // queries
});
```

### Recommended Approach:
```php
// Let Sentry auto-instrument database queries
// Just ensure these are enabled in config/sentry.php:
'tracing' => [
    'sql_queries' => true,
    'sql_bindings' => false,
    'sql_origin' => true,
];
```

## 2. Simplify Span Creation

The Sentry PHP SDK (v4.x) uses a simpler pattern than we're implementing:

### Current Implementation:
```php
// Our complex manual transaction/span management
$hub = \Sentry\SentrySdk::getCurrentHub();
$parent = $hub->getSpan();
if ($parent === null) {
    $context = new \Sentry\Tracing\TransactionContext();
    // ... complex setup
}
```

### Recommended Pattern:
```php
use function Sentry\trace;

// Simple function-based approach
$result = \Sentry\trace(
    function () {
        // Your code here
        return $result;
    },
    [
        'op' => 'business.create',
        'name' => 'Create Business',
        'tags' => ['industry' => 'tech'],
    ]
);
```

## 3. Use Middleware for Automatic Context

Instead of manually setting context in each controller:

### Current:
```php
// In each controller method
CriticalExperienceTracker::trackDiscoveryStart();
```

### Recommended:
```php
// In middleware (we have this, but could enhance)
class SentryContextMiddleware
{
    public function handle($request, Closure $next)
    {
        // Set user context
        if ($user = $request->user()) {
            \Sentry\configureScope(function ($scope) use ($user) {
                $scope->setUser([
                    'id' => $user->id,
                    'email' => $user->email,
                ]);
            });
        }
        
        // Set tags based on route
        \Sentry\configureScope(function ($scope) use ($request) {
            $scope->setTag('route', $request->route()->getName());
            $scope->setTag('method', $request->method());
        });
        
        return $next($request);
    }
}
```

## 4. Leverage Laravel's Built-in Events

Instead of manual tracking, use Laravel events that Sentry auto-instruments:

```php
// These are automatically tracked when enabled:
- Cache operations (hits, misses, writes)
- Queue jobs
- HTTP client requests
- Notifications
- Redis commands
- View rendering
```

## 5. Optimize Critical Experience Tracker

Current implementation uses logs instead of proper Sentry APIs:

### Current:
```php
public static function trackDiscoveryStart(): void
{
    \Sentry\addBreadcrumb(
        'critical.discovery',
        'Discovery journey started',
        ['checkpoint' => 'start'],
        'info'
    );
}
```

### Enhanced:
```php
public static function trackDiscoveryStart(): void
{
    // Add breadcrumb
    \Sentry\addBreadcrumb(
        'critical.discovery',
        'Discovery journey started',
        ['checkpoint' => 'start'],
        'info'
    );
    
    // Set measurement on current transaction
    $transaction = \Sentry\SentrySdk::getCurrentHub()->getTransaction();
    if ($transaction) {
        $transaction->setMeasurement('discovery.started', 1, 'none');
    }
}
```

## 6. Use Performance Metrics

Add custom performance metrics to transactions:

```php
$transaction = \Sentry\SentrySdk::getCurrentHub()->getTransaction();
if ($transaction) {
    // Track custom metrics
    $transaction->setMeasurement('business.count', $count, 'none');
    $transaction->setMeasurement('query.duration', $duration, 'millisecond');
    $transaction->setMeasurement('cache.hit_rate', $hitRate, 'ratio');
}
```

## 7. Implement Sampling Strategies

For high-traffic applications, implement intelligent sampling:

```php
// In config/sentry.php
'traces_sampler' => function (\Sentry\Tracing\SamplingContext $context): float {
    // Always sample critical paths
    if (str_contains($context->getTransactionContext()->getName(), 'onboarding')) {
        return 1.0;
    }
    
    // Sample 10% of regular traffic
    return 0.1;
},
```

## 8. Use Sentry's Native Logger

Instead of our custom logger wrapper:

```php
// Get Sentry's logger directly
$logger = \Sentry\SentrySdk::getCurrentHub()->getClient()?->getLogger();

// Use it with PSR-3 interface
$logger->info('Business created', [
    'business_id' => $business->id,
    'industry' => $business->industry,
]);
```

## Implementation Priority

1. **High Priority**:
   - Enable all relevant auto-instrumentation in `config/sentry.php`
   - Simplify span creation using `\Sentry\trace()` function
   - Add performance measurements to critical paths

2. **Medium Priority**:
   - Enhance middleware for automatic context
   - Implement intelligent sampling
   - Use native Sentry logger

3. **Low Priority**:
   - Remove redundant manual tracking
   - Consolidate logging services

## Next Steps

1. Review and enable all auto-instrumentation options in `config/sentry.php`
2. Refactor `SentryLogger::startSpan()` to use `\Sentry\trace()`
3. Add performance measurements to `CriticalExperienceTracker`
4. Implement sampling strategy for production
5. Remove redundant manual tracking from controllers

## References

- [Sentry Laravel SDK Documentation](https://docs.sentry.io/platforms/php/guides/laravel/)
- [Sentry PHP SDK v4 Documentation](https://docs.sentry.io/platforms/php/)
- [Performance Monitoring Best Practices](https://docs.sentry.io/product/performance/)
