# Sentry Integration Guide

## Overview

This guide provides comprehensive documentation for Sentry.io integration in the Awesome Business Directory Laravel application. Sentry provides application performance monitoring (APM), error tracking, and user experience monitoring to help maintain a high-quality user experience.

**Updated to follow the latest Sentry integration rules and best practices.**

## Table of Contents

1. [Benefits & Value](#benefits--value)
2. [Installation & Setup](#installation--setup)
3. [Core Concepts](#core-concepts)
4. [New Rules & Patterns](#new-rules--patterns)
5. [BusinessLogger Service](#businesslogger-service)
6. [Frontend Integration](#frontend-integration)
7. [Exception Handling](#exception-handling)
8. [Testing Strategy](#testing-strategy)
9. [Production Considerations](#production-considerations)
10. [Troubleshooting](#troubleshooting)

## Benefits & Value

### Application Performance Monitoring (APM)
- **Distributed Tracing**: Track requests across your entire application stack
- **Performance Insights**: Identify bottlenecks before they impact users
- **Real User Monitoring**: Understand actual user experience, not just synthetic tests
- **Database Query Monitoring**: Spot slow queries and N+1 problems

### Error Tracking & Context
- **Rich Error Context**: Capture user actions leading to errors
- **Release Tracking**: Correlate errors with deployments
- **User Impact Analysis**: Understand which users are affected by issues
- **Performance Correlation**: See how errors relate to performance degradation

### Business Intelligence
- **Conversion Funnel Analysis**: Track critical experience through business onboarding
- **Feature Usage Analytics**: Understand which features drive engagement
- **User Experience Metrics**: Monitor Core Web Vitals and user satisfaction
- **A/B Testing Support**: Measure impact of changes on user experience

## Installation & Setup

### 1. Package Installation

```bash
composer require sentry/sentry-laravel
```

### 2. Laravel Integration

Update `bootstrap/app.php`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Enable automatic error capture
        Integration::handles($exceptions);
    })
    ->create();
```

### 3. Configuration

Publish configuration:
```bash
./vendor/bin/sail artisan sentry:publish --dsn=YOUR_SENTRY_DSN
```

Environment variables:
```env
# Core Configuration
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project-id
SENTRY_ENVIRONMENT=development
SENTRY_RELEASE=v1.0.0

# Performance Monitoring
SENTRY_TRACES_SAMPLE_RATE=1.0  # 100% for development, 0.1 for production
SENTRY_PROFILES_SAMPLE_RATE=0.1

# Privacy
SENTRY_SEND_DEFAULT_PII=false

# Logs Integration (NEW)
SENTRY_ENABLE_LOGS=true
```

### 4. Verify Installation

```bash
./vendor/bin/sail artisan sentry:test
```

## New Rules & Patterns

### Frontend JavaScript Configuration

The updated JavaScript configuration follows the new rules:

```javascript
import * as Sentry from "@sentry/browser";

Sentry.init({
    dsn: "https://examplePublicKey@o0.ingest.sentry.io/0",

    // Enable new structured logging (REQUIRED)
    _experiments: {
        enableLogs: true,
    },

    integrations: [
        // Send console.log, console.error, and console.warn calls as logs to Sentry
        Sentry.consoleLoggingIntegration({ levels: ["log", "error", "warn"] }),
    ],
});

// Get logger for structured logging
const { logger } = Sentry;
```

### Exception Tracking

Use `Sentry.captureException(error)` in try-catch blocks:

```javascript
try {
    // Some risky operation
    doSomething();
} catch (error) {
    Sentry.captureException(error);
    logger.error("Operation failed", { error: error.message });
}
```

### Tracing with Sentry.startSpan

Use `Sentry.startSpan` for meaningful actions like button clicks, API calls, and function calls:

#### UI Component Actions

```javascript
function TestComponent() {
  const handleTestButtonClick = () => {
    // Create a transaction/span to measure performance
    Sentry.startSpan(
      {
        op: "ui.click",
        name: "Test Button Click",
      },
      (span) => {
        const value = "some config";
        const metric = "some metric";

        // Metrics can be added to the span
        span.setAttribute("config", value);
        span.setAttribute("metric", metric);

        doSomething();
      },
    );
  };

  return (
    <button type="button" onClick={handleTestButtonClick}>
      Test Sentry
    </button>
  );
}
```

#### API Calls

```javascript
async function fetchUserData(userId) {
  return Sentry.startSpan(
    {
      op: "http.client",
      name: `GET /api/users/${userId}`,
    },
    async () => {
      const response = await fetch(`/api/users/${userId}`);
      const data = await response.json();
      return data;
    },
  );
}
```

### Structured Logging

Use `logger.fmt` template literal function for structured logs:

```javascript
const { logger } = Sentry;

logger.trace("Starting database connection", { database: "users" });
logger.debug(logger.fmt`Cache miss for user: ${userId}`);
logger.info("Updated profile", { profileId: 345 });
logger.warn("Rate limit reached for endpoint", {
  endpoint: "/api/results/",
  isEnterprise: false,
});
logger.error("Failed to process payment", {
  orderId: "order_123",
  amount: 99.99,
});
logger.fatal("Database connection pool exhausted", {
  database: "users",
  activeConnections: 100,
});
```

## Core Concepts

### Transactions
Transactions represent complete user operations or requests. They provide the overall context for performance monitoring.

```php
// Start a transaction for business onboarding
$transaction = BusinessLogger::startBusinessTransaction('business_onboarding', [
    'user_id' => auth()->id(),
    'step' => 1
]);

// ... perform operations ...

$transaction->finish();
```

### Spans
Spans are sub-operations within transactions - they break down complex operations to identify bottlenecks.

```php
// Create spans for specific operations
$databaseSpan = BusinessLogger::createDatabaseSpan('business_insert');
// ... database operation ...
$databaseSpan->finish();

$emailSpan = BusinessLogger::createExternalSpan('email_service', 'send_confirmation');
// ... email operation ...
$emailSpan->finish();
```

### Breadcrumbs
Breadcrumbs create a trail of user actions leading up to events, providing context for debugging.

```php
// Automatically added by BusinessLogger methods
BusinessLogger::onboardingStarted($request);
BusinessLogger::onboardingFormProgress('contact', ['completion_percentage' => 65]);
BusinessLogger::businessCreated($business, 1200); // 1.2 seconds
```

### Tags & Context
Tags and context provide searchable metadata for filtering and analysis.

```php
// Tags for filtering
'feature' => 'business_onboarding'
'industry' => 'technology'
'user_type' => 'first_time'

// Context for detailed analysis
'business' => [
    'id' => 123,
    'name' => 'Acme Corp',
    'industry' => 'technology'
]
```

## BusinessLogger Service

The `BusinessLogger` service provides structured logging methods for different business events. All methods automatically send data to both Sentry Logs (for log analysis) and Sentry Issues (for error alerting).

### Business Onboarding

```php
// Track onboarding start
BusinessLogger::onboardingStarted($request);

// Track form progress
BusinessLogger::onboardingFormProgress('basic_info', [
    'completion_percentage' => 25,
    'filled_fields' => ['business_name', 'industry'],
    'time_spent_ms' => 30000
]);

// Track validation errors
BusinessLogger::onboardingValidationError('primary_email', 'invalid_email', [
    'error_message' => 'The primary email must be a valid email address.',
    'field_value_length' => 12,
    'user_input_pattern' => 'missing_at_symbol'
]);

// Track successful completion
BusinessLogger::businessCreated($business, $processingTimeMs);
```

### Multi-Step Process Tracking

```php
// Track individual steps
BusinessLogger::multiStepStepStarted(1, ['form_type' => 'business_onboarding']);
BusinessLogger::multiStepStepCompleted(1, $stepData, $stepTimeMs);

// Track validation errors per step
BusinessLogger::multiStepValidationError(2, $errors, $submittedData);

// Track review and completion
BusinessLogger::multiStepReviewReached($allStepData, $totalExperienceTimeMs);
BusinessLogger::multiStepConversionCompleted($business, $experienceMetrics);
```

### User Experience Monitoring

```php
// Track page views with performance
BusinessLogger::welcomePageViewed($request, $responseTimeMs);

// Track user interactions
BusinessLogger::welcomeCtaClicked('browse_businesses', $request);

// Track performance metrics
BusinessLogger::performanceMetric('form_render', 245, [
    'form_type' => 'business_onboarding',
    'field_count' => 12
]);
```

### Error Handling

```php
// Application errors with context
BusinessLogger::applicationError($exception, 'business_creation', [
    'user_id' => auth()->id(),
    'business_data' => $businessData,
    'step' => 3
]);

// Performance issues
BusinessLogger::slowQuery('business_search', 2500, $sql);

// Security events
BusinessLogger::securityEvent('suspicious_submission', [
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'submission_rate' => 'high'
]);
```

## Frontend Integration

### JavaScript Setup

The frontend integration provides automatic error tracking, performance monitoring, and user interaction tracking with distributed tracing support.

**resources/js/sentry.js**:
```javascript
import * as Sentry from "@sentry/browser";
// BrowserTracing is now part of @sentry/browser (no separate import needed)

Sentry.init({
    dsn: window.sentryConfig?.dsn || '',
    environment: window.sentryConfig?.environment || 'development',
    
    integrations: [
        Sentry.browserTracingIntegration({
            // Enable distributed tracing - this is the key setting
            tracePropagationTargets: [window.location.hostname, /^\//],
        }),
    ],
    
    tracesSampleRate: window.sentryConfig?.tracesSampleRate || 1.0,
    autoSessionTracking: true,
    release: window.sentryConfig?.release || '1.0.0',
    
    beforeSend(event, hint) {
        // Filter out non-essential errors
        if (event.exception) {
            const error = hint.originalException;
            if (error && error.name === 'AbortError') {
                return null;
            }
        }
        return event;
    }
});
```

### Distributed Tracing: Frontend-Backend Connection

The application implements **distributed tracing** to connect frontend user interactions with backend processing. Here's how it works:

#### 1. Trace Propagation Headers

When the frontend makes requests to the backend, Sentry automatically adds trace headers:

```javascript
// Sentry automatically adds these headers to all requests:
{
  'sentry-trace': '12345678901234567890123456789012-1234567890123456-1',
  'baggage': 'sentry-environment=production,sentry-transaction=form.submit'
}
```

The `sentry-trace` header contains:
- **Trace ID**: Links all related operations together
- **Span ID**: The current operation identifier  
- **Sampled**: Whether this trace is being recorded

#### 2. Backend Trace Continuation

The backend middleware (`SentryTracingMiddleware`) receives these headers and continues the trace:

```php
// In SentryTracingMiddleware.php
$sentryTrace = $request->header('sentry-trace');
$baggage = $request->header('baggage');

if ($sentryTrace) {
    // Parse trace data and continue the distributed trace
    $traceData = $this->parseSentryTrace($sentryTrace);
    $transactionContext->setParentSpanId($parentSpanId);
    $transactionContext->setTraceId($traceId);
}
```

#### 3. Form Submission Tracing

For form submissions, trace headers are embedded in the form:

```javascript
// Frontend: Add trace headers to form submission
const { transaction, traceHeaders } = SentryTracing.trackFormSubmission(form, 'onboarding');

// Headers are added as hidden form fields
Object.entries(traceHeaders).forEach(([key, value]) => {
    const hiddenInput = document.createElement('input');
    hiddenInput.name = `_sentry_${key.replace('-', '_')}`;
    hiddenInput.value = value;
    form.appendChild(hiddenInput);
});
```

```php
// Backend: Extract trace data from form
$sentryTrace = $request->input('_sentry_sentry_trace');
$baggage = $request->input('_sentry_baggage');

// Continue the trace from the frontend form submission
```

#### 4. Complete Critical Experience Tracking

This creates a complete trace showing the full critical experience:

```
Frontend Transaction: "User clicks 'Add Business'"
├── Span: Form validation (50ms)
├── Span: HTTP request to /business/store (200ms)
│   └── Backend Transaction: "POST /business/store" [CONNECTED VIA TRACE HEADERS]
│       ├── Span: Validation (25ms)
│       ├── Span: Database insert (150ms)
│       ├── Span: Email notification (300ms)
│       └── Span: Cache update (20ms)
└── Span: Success page render (100ms)
```

### Alpine.js Integration

The application uses Alpine.js directives for automatic tracking:

```html
<!-- Track user interactions with automatic tracing -->
<button x-track='{"action": "browse_businesses", "source": "hero_cta"}'>
    Browse Businesses
</button>

<!-- Form with distributed tracing -->
<form x-data="onboardingForm" @submit="trackFormSubmission">
    <input x-track-change='{"field": "business_name"}' />
</form>
```

### Custom Tracking Functions

```javascript
// Track business interactions with tracing context
BusinessDirectoryTracking.trackBusinessCardClick(businessId, businessName);

// Track onboarding progress with distributed tracing
BusinessDirectoryTracking.trackOnboardingProgress(step, stepData);

// Get trace headers for manual requests
const traceHeaders = SentryTracing.getTraceHeaders();
```

### Configuration for Distributed Tracing

**Layout template (`layouts/app.blade.php`)** includes trace metadata:

```html
<meta name="sentry-trace" content="{{ \Sentry\SentrySdk::getCurrentHub()->getTransaction()?->toTraceparent() ?? '' }}">

<script>
window.sentryConfig = {
    // ... other config
    enableTracing: true,
    pageContext: {
        route: '{{ request()->route()?->getName() }}',
        traceId: '{{ session()->getId() }}'
    }
};
</script>
```

## Exception Handling

Use `Sentry.captureException(error)` in try-catch blocks:

```javascript
try {
    // Some risky operation
    doSomething();
} catch (error) {
    Sentry.captureException(error);
    logger.error("Operation failed", { error: error.message });
}
```

## Testing Strategy

### Backend Testing

Test Sentry integration using [TDD approach][[memory:7879256906068291126]]:

```php
// Test transaction creation
public function test_business_creation_creates_sentry_transaction()
{
    $this->mock(BusinessLogger::class)
        ->shouldReceive('startBusinessTransaction')
        ->once()
        ->with('business_creation', Mockery::any());

    $response = $this->post(route('business.store'), $this->validBusinessData());
    $response->assertRedirect();
}

// Test error tracking
public function test_validation_errors_are_tracked()
{
    $this->mock(BusinessLogger::class)
        ->shouldReceive('onboardingValidationError')
        ->atLeast(1);

    $response = $this->post(route('business.store'), []);
    $response->assertSessionHasErrors();
}
```

### Frontend Testing

Test JavaScript integration and tracking:

```php
public function test_welcome_page_includes_sentry_configuration()
{
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertSee('window.sentryConfig');
    $response->assertSee('dsn:');
    $response->assertSee('environment:');
}

public function test_tracking_attributes_are_present()
{
    $response = $this->get('/');
    
    $response->assertSee('x-track=\'{"action": "browse_businesses"}\'', false);
    $response->assertSee('x-track=\'{"action": "add_business"}\'', false);
}
```

### Validation Commands

```bash
# Test Sentry connectivity
./vendor/bin/sail artisan sentry:test

# Run comprehensive test suite
./vendor/bin/sail test

# Test specific Sentry integration
./vendor/bin/sail test --filter=Sentry
```

## Production Considerations

### Performance Impact

- **Sampling Rates**: Use 10-30% trace sampling in production
- **Error Filtering**: Filter out known non-critical errors
- **Breadcrumb Limits**: Sentry automatically limits breadcrumbs to prevent memory issues

### Sample Rate Configuration

```env
# Development
SENTRY_TRACES_SAMPLE_RATE=1.0

# Production
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.05
```

### Monitoring & Alerts

Set up alerts for:
- Error rate increases
- Performance degradation
- High validation error rates
- Slow database queries (>500ms)

### Data Privacy

- **PII Handling**: `SENTRY_SEND_DEFAULT_PII=false` prevents automatic PII collection
- **Data Scrubbing**: Sensitive fields are automatically scrubbed
- **Custom Filtering**: Use `beforeSend` to filter sensitive data

### Release Tracking

```bash
# Notify Sentry of new deployment
curl -X POST \
  "https://sentry.io/api/0/organizations/YOUR_ORG/releases/" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"version": "1.0.0", "projects": ["YOUR_PROJECT"]}'
```

## Troubleshooting

### Common Issues

**Sentry not receiving events:**
1. Check DSN configuration
2. Verify network connectivity
3. Check sampling rates
4. Review error filtering

**High event volume:**
1. Increase sampling rates
2. Add error filtering
3. Review breadcrumb settings

**Missing context:**
1. Verify BusinessLogger usage
2. Check user authentication context
3. Review tag configuration

### Debug Mode

Enable debug logging:
```env
SENTRY_LOGGER=Sentry\Logger\DebugFileLogger::class
```

Check logs at `storage/logs/sentry.log`.

### Performance Issues

Monitor for:
- Transaction creation overhead
- Breadcrumb memory usage
- Network latency to Sentry
- Database query impact

### Validation Queries

Use these Sentry queries to validate your integration:

```javascript
// Error rate over time
{
  "query": "event.type:error",
  "metrics": ["count()"],
  "groupBy": ["timestamp.to_hour"]
}

// Performance by feature
{
  "query": "event.type:transaction",
  "metrics": ["avg(transaction.duration)"],
  "groupBy": ["tags.feature"]
}

// Onboarding funnel analysis
{
  "query": "tags.feature:business_onboarding",
  "metrics": ["count()"],
  "groupBy": ["tags.onboarding_stage"]
}
```

---

This integration provides comprehensive monitoring of the Awesome Business Directory application, enabling data-driven decisions for performance optimization and user experience improvements. 