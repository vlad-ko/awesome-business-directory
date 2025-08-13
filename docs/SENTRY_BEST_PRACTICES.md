# Sentry Best Practices for Laravel

This guide shows how to use Sentry following the latest official SDK patterns.

## Backend (Laravel) Best Practices

### 1. Use `Sentry\startSpan` for Performance Monitoring

Instead of manually creating transactions, use the modern `startSpan` function:

```php
use function Sentry\startSpan;

// ❌ Old pattern - Don't use this
$transaction = \Sentry\startTransaction(['name' => 'operation']);
// ... do work
$transaction->finish();

// ✅ Modern pattern - Use this
startSpan([
    'op' => 'business.create',
    'name' => 'Create Business',
], function ($span) {
    // Your code here
    $span->setAttribute('business.name', $business->name);
    
    return $business;
});
```

### 2. Use Logger for Structured Logging

```php
use App\Services\SentryLogger;

// Log with proper context
SentryLogger::log('info', 'Business created', [
    'business_id' => $business->id,
    'industry' => $business->industry,
]);

// Track operations with spans
SentryLogger::trackBusinessOperation('create', [
    'industry' => $request->input('industry'),
], function ($span) use ($request) {
    // Create business logic here
    $business = Business::create($request->validated());
    
    $span->setAttribute('business.id', $business->id);
    
    return $business;
});
```

### 3. Proper Exception Handling

```php
use function Sentry\captureException;

try {
    // Your code
} catch (\Exception $e) {
    // Capture with context
    captureException($e, [
        'tags' => [
            'component' => 'business_creation',
            'feature' => 'onboarding',
        ],
        'extra' => [
            'input_data' => $request->all(),
            'user_id' => auth()->id(),
        ],
    ]);
    
    throw $e; // Re-throw if needed
}
```

### 4. Use Middleware for Request Context

Add the new context middleware to your routes:

```php
// In routes/web.php or bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\SentryContextMiddleware::class,
    ]);
});
```

## Frontend (JavaScript) Best Practices

### 1. Use Modern Initialization

```javascript
import * as Sentry from "@sentry/browser";

Sentry.init({
    dsn: "your-dsn",
    
    // Enable structured logging
    _experiments: {
        enableLogs: true,
    },
    
    integrations: [
        // Modern browser tracing
        Sentry.browserTracingIntegration({
            tracePropagationTargets: [window.location.hostname, /^\//],
            enableInteractions: true,
            enableLongTask: true,
            enableInp: true,
        }),
        
        // Console logging
        Sentry.consoleLoggingIntegration({ 
            levels: ["log", "error", "warn"] 
        }),
        
        // Session replay (optional)
        Sentry.replayIntegration({
            replaysSessionSampleRate: 0.1,
            replaysOnErrorSampleRate: 1.0,
        }),
    ],
});
```

### 2. Use Logger for Frontend

```javascript
const { logger } = Sentry;

// Use structured logging
logger.info(logger.fmt`User clicked business card: ${businessName}`, {
    businessId: businessId,
    feature: 'business_discovery',
});

// Log errors with context
try {
    await submitForm(data);
} catch (error) {
    logger.error('Form submission failed', {
        error: error.message,
        formData: data,
    });
    
    Sentry.captureException(error);
}
```

### 3. Use `Sentry.startSpan` for Tracking

```javascript
// Track user interactions
Sentry.startSpan({
    op: 'ui.click',
    name: 'Business Card Click',
}, (span) => {
    span.setAttribute('business.id', businessId);
    span.setAttribute('business.name', businessName);
    
    // Perform the action
    navigateToBusinessDetail(businessId);
});

// Track API calls with distributed tracing
Sentry.startSpan({
    op: 'http.client',
    name: `GET /api/businesses/${id}`,
}, async (span) => {
    // Add trace headers for distributed tracing
    const response = await fetch(`/api/businesses/${id}`, {
        headers: {
            'sentry-trace': span.toTraceparent(),
            'baggage': span.toBaggage(),
        }
    });
    
    span.setAttribute('http.status_code', response.status);
    
    return response.json();
});
```

### 4. Track Core Web Vitals

```javascript
// Automatic tracking with proper attribution
new PerformanceObserver((list) => {
    for (const entry of list.getEntries()) {
        logger.info('Core Web Vital: LCP', {
            value: entry.startTime,
            element: entry.element?.tagName,
        });
        
        Sentry.setMeasurement('lcp', entry.startTime, 'millisecond');
    }
}).observe({ entryTypes: ['largest-contentful-paint'] });
```

## Configuration Best Practices

### 1. Environment-Specific Settings

```env
# Development
SENTRY_TRACES_SAMPLE_RATE=1.0      # 100% tracing
SENTRY_PROFILES_SAMPLE_RATE=1.0    # 100% profiling
SENTRY_SPOTLIGHT=true              # Enable Spotlight

# Production
SENTRY_TRACES_SAMPLE_RATE=0.1      # 10% tracing
SENTRY_PROFILES_SAMPLE_RATE=0.01   # 1% profiling
SENTRY_SPOTLIGHT=false             # Disable Spotlight
```

### 2. Enable All Features

```php
// config/sentry.php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    
    // Enable Spotlight in development
    'spotlight' => env('SENTRY_SPOTLIGHT', env('APP_DEBUG', false)),
    
    // Enable structured logging
    'enable_logs' => true,
    
    // Enable all breadcrumbs
    'breadcrumbs' => [
        'logs' => true,
        'cache' => true,
        'livewire' => true,
        'sql_queries' => true,
        'sql_bindings' => false, // PII concern
        'queue_info' => true,
        'command_info' => true,
        'http_client_requests' => true,
        'notifications' => true,
    ],
    
    // Enable all tracing
    'tracing' => [
        'queue_job_transactions' => true,
        'queue_jobs' => true,
        'sql_queries' => true,
        'sql_origin' => true,
        'views' => true,
        'livewire' => true,
        'http_client_requests' => true,
        'cache' => true,
        'redis_commands' => true,
        'notifications' => true,
        'continue_after_response' => true,
        'default_integrations' => true,
    ],
];
```

## Testing Sentry Integration

### 1. Test Backend Integration

```bash
# Test Sentry is working
./vendor/bin/sail artisan sentry:test

# Test with Spotlight (local development)
# Install Spotlight: https://spotlightjs.com/
# Then errors will appear in the Spotlight app
```

### 2. Test Frontend Integration

```javascript
// In browser console
Sentry.captureMessage('Test message from frontend');
Sentry.captureException(new Error('Test error from frontend'));

// Check browser console for logger output
const { logger } = Sentry;
logger.info('Test log message', { test: true });
```

## Common Pitfalls to Avoid

1. **Don't use deprecated packages**
   - ❌ `@sentry/tracing` (deprecated)
   - ✅ `@sentry/browser` with `browserTracingIntegration`

2. **Don't manually create transactions**
   - ❌ `startTransaction()` / `finishTransaction()`
   - ✅ `startSpan()` with callback

3. **Don't mix logging approaches**
   - ❌ Using both Laravel Log and Sentry separately
   - ✅ Use SentryLogger or configure Laravel to use Sentry channel

4. **Don't forget distributed tracing**
   - ❌ Frontend and backend traces disconnected
   - ✅ Pass trace headers between frontend and backend

5. **Don't ignore performance**
   - ❌ No performance monitoring
   - ✅ Track Core Web Vitals and custom metrics

## Debugging Tips

1. **Enable debug mode in development**:
   ```php
   // In .env
   SENTRY_DEBUG=true
   ```

2. **Use Spotlight for local development**:
   - Download from https://spotlightjs.com/
   - Enable in config: `SENTRY_SPOTLIGHT=true`
   - See errors in real-time locally

3. **Check Sentry dashboard**:
   - Verify events are being received
   - Check for rate limiting
   - Review performance data

4. **Use browser DevTools**:
   - Network tab: Check for Sentry requests
   - Console: Look for Sentry debug messages
   - Performance tab: Verify spans are created