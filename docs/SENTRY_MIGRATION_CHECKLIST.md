# Sentry Integration Migration & Checklist

## 🚀 **Migration Overview**

This checklist ensures your Sentry integration follows the latest patterns and best practices. Use this as a comprehensive guide for migrating from older Sentry versions or implementing new integrations.

---

## ✅ **Pre-Migration Checklist**

### 1. **Package Versions**
- [ ] **Frontend**: Using `@sentry/browser` v9.31.0+
- [ ] **Backend**: Using `sentry/sentry-laravel` v4.15+
- [ ] **Removed**: All deprecated packages (e.g., `@sentry/tracing`)
- [ ] **Verified**: No version conflicts in package-lock.json

### 2. **Environment Setup**
- [ ] **DSN configured** in environment variables
- [ ] **Environment-specific sampling rates** configured
- [ ] **Release tracking** configured with proper version strings
- [ ] **Structured logging** enabled (`SENTRY_ENABLE_LOGS=true`)

---

## 🌐 **Frontend Migration Checklist**

### Modern JavaScript Patterns
- [ ] **Import statement**: Using `import * as Sentry from "@sentry/browser"`
- [ ] **No deprecated imports**: Removed `@sentry/tracing` imports
- [ ] **Modern integration**: Using `Sentry.browserTracingIntegration()`
- [ ] **Structured logging**: Enabled `_experiments: { enableLogs: true }`
- [ ] **Console integration**: Added `Sentry.consoleLoggingIntegration()`

### Tracing & Spans
- [ ] **Modern spans**: Using `Sentry.startSpan()` pattern
- [ ] **Span attributes**: Using `span.setAttribute()` for context
- [ ] **Meaningful operations**: Using descriptive `op` and `name` values
- [ ] **Distributed tracing**: Proper trace header handling

### Error Handling
- [ ] **Exception capture**: Using `Sentry.captureException()` in try-catch blocks
- [ ] **Error filtering**: Implemented `beforeSend` for noise reduction
- [ ] **Context enrichment**: Added relevant business context to errors

### Logging
- [ ] **Structured logger**: Using `const { logger } = Sentry;`
- [ ] **Template literals**: Using `logger.fmt` for dynamic messages
- [ ] **Log levels**: Appropriate use of info, warn, error levels
- [ ] **Context data**: Adding structured data to log messages

### Performance Monitoring
- [ ] **Core Web Vitals**: Tracking LCP, FID, CLS
- [ ] **Custom metrics**: Business-specific performance measurements
- [ ] **Page load tracking**: Comprehensive navigation timing
- [ ] **User interactions**: Tracking clicks, form submissions, searches

---

## 🔧 **Backend Migration Checklist**

### Laravel Configuration
- [ ] **Config file**: Updated `config/sentry.php` with latest options
- [ ] **Bootstrap**: Proper integration in `bootstrap/app.php`
- [ ] **Middleware**: Custom tracing middleware configured
- [ ] **Exception handling**: Using `Integration::handles()` pattern

### Exception Handling
- [ ] **Modern capture**: Using `\Sentry\captureException()` in try-catch
- [ ] **Context tags**: Adding component and operation tags
- [ ] **Extra data**: Including relevant request/business context
- [ ] **Error classification**: Proper error severity levels

### Performance Tracing
- [ ] **Database queries**: SQL tracing enabled and configured
- [ ] **HTTP requests**: Client request tracing enabled
- [ ] **Queue jobs**: Background job tracing configured
- [ ] **Cache operations**: Cache hit/miss tracking enabled
- [ ] **View rendering**: Blade template rendering tracked

### Business Logic Integration
- [ ] **BusinessLogger service**: Enhanced with modern patterns
- [ ] **Multi-step processes**: Comprehensive onboarding tracking
- [ ] **Conversion funnels**: Business event tracking implemented
- [ ] **Performance metrics**: Business-specific measurements

---

## 🔗 **Distributed Tracing Checklist**

### Frontend to Backend Correlation
- [ ] **Trace headers**: Frontend generating proper trace context
- [ ] **Form submissions**: Hidden inputs for trace correlation
- [ ] **AJAX requests**: Automatic header injection configured
- [ ] **Meta tags**: Blade templates include trace metadata

### Backend Processing
- [ ] **Header extraction**: Middleware extracts trace from headers/forms
- [ ] **Span correlation**: Child spans properly linked to parent traces
- [ ] **Transaction naming**: Meaningful transaction names
- [ ] **Context propagation**: User and request context maintained

---

## 📊 **Configuration Checklist**

### Environment-Specific Settings

#### Development
- [ ] **Full sampling**: `SENTRY_TRACES_SAMPLE_RATE=1.0`
- [ ] **Full logging**: `SENTRY_ENABLE_LOGS=true`
- [ ] **SQL bindings**: `SENTRY_BREADCRUMBS_SQL_BINDINGS_ENABLED=true`
- [ ] **Debug mode**: Enhanced error details enabled

#### Staging
- [ ] **Partial sampling**: `SENTRY_TRACES_SAMPLE_RATE=0.5`
- [ ] **Security**: SQL bindings disabled
- [ ] **PII protection**: `SENTRY_SEND_DEFAULT_PII=false`
- [ ] **Production-like**: Similar to production configuration

#### Production
- [ ] **Optimized sampling**: `SENTRY_TRACES_SAMPLE_RATE=0.1`
- [ ] **Profiling**: `SENTRY_PROFILES_SAMPLE_RATE=0.1`
- [ ] **Privacy**: No PII in error reports
- [ ] **Performance**: Minimal overhead configuration

---

## 🧪 **Testing Checklist**

### Automated Tests
- [ ] **Sentry initialization**: Tests verify Sentry is properly configured
- [ ] **Exception handling**: Tests confirm errors are captured
- [ ] **Transaction creation**: Tests verify spans are created
- [ ] **Distributed tracing**: Tests confirm trace correlation works

### Manual Testing
- [ ] **Error capture**: Manually trigger errors and verify in Sentry
- [ ] **Performance data**: Verify transactions appear in Performance tab
- [ ] **Breadcrumbs**: Confirm user journey tracking works
- [ ] **Distributed traces**: Verify frontend-backend correlation

### Integration Testing
- [ ] **Form submissions**: End-to-end trace correlation
- [ ] **Business flows**: Multi-step process tracking
- [ ] **Error scenarios**: Various error types captured correctly
- [ ] **Performance edge cases**: Slow queries, timeouts tracked

---

## 📋 **Code Quality Checklist**

### Modern Patterns
- [ ] **No deprecated code**: All old patterns removed
- [ ] **Consistent naming**: Standardized operation and span names
- [ ] **Proper context**: Rich context data without PII
- [ ] **Error boundaries**: Graceful error handling everywhere

### Performance Considerations
- [ ] **Sampling configured**: Appropriate rates for traffic volume
- [ ] **Filtering implemented**: Noise reduction in place
- [ ] **Breadcrumb limits**: Reasonable breadcrumb retention
- [ ] **Context size**: Avoiding oversized payloads

### Security & Privacy
- [ ] **No sensitive data**: PII filtered from all reports
- [ ] **Secure DSN**: DSN properly secured and rotated
- [ ] **GDPR compliance**: User consent mechanisms if required
- [ ] **Data retention**: Configured according to policies

---

## 🚀 **Deployment Checklist**

### Pre-Deployment
- [ ] **Environment variables**: All Sentry vars configured
- [ ] **Release version**: Proper release tagging configured
- [ ] **Testing complete**: All tests passing
- [ ] **Performance tested**: No significant overhead introduced

### Post-Deployment
- [ ] **Sentry dashboard**: Verify data appearing correctly
- [ ] **Error rates**: Monitor for error rate increases
- [ ] **Performance impact**: Check application performance
- [ ] **Distributed traces**: Verify trace correlation working

### Monitoring Setup
- [ ] **Alerts configured**: Error rate and performance alerts
- [ ] **Dashboard setup**: Business-specific dashboards created
- [ ] **Team access**: Proper team member access configured
- [ ] **Integration setup**: Slack/email notifications configured

---

## 🔧 **Troubleshooting Checklist**

### Common Issues
- [ ] **Missing traces**: Check sampling rates and DSN configuration
- [ ] **No correlation**: Verify trace headers in requests
- [ ] **High overhead**: Reduce sampling or disable verbose features
- [ ] **Missing context**: Check scope configuration and context setting

### Debugging Steps
- [ ] **Console logging**: Enable debug logging temporarily
- [ ] **Network inspection**: Check Sentry API calls in dev tools
- [ ] **Configuration validation**: Verify all config values
- [ ] **Version compatibility**: Ensure all packages are compatible

---

## ✅ **Final Verification**

### Data Quality
- [ ] **Meaningful errors**: Errors provide actionable information
- [ ] **Rich context**: Sufficient context for debugging
- [ ] **Performance insights**: Meaningful performance data
- [ ] **Business metrics**: Business-specific tracking working

### Team Adoption
- [ ] **Documentation updated**: Team has access to updated docs
- [ ] **Training completed**: Team understands new patterns
- [ ] **Processes updated**: Development workflows include Sentry
- [ ] **Monitoring established**: Regular Sentry dashboard reviews

---

## 📚 **Resources**

- [Complete Integration Guide](./SENTRY_COMPLETE_INTEGRATION_GUIDE.md)
- [Sentry JavaScript Documentation](https://docs.sentry.io/platforms/javascript/)
- [Sentry Laravel Documentation](https://docs.sentry.io/platforms/php/guides/laravel/)
- [Project Sentry Dashboard](https://sentry.io/organizations/your-org/projects/)

---

*Use this checklist as a living document - update it as your integration evolves and new patterns emerge.* 