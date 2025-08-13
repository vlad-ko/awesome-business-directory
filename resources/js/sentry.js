/**
 * Enhanced Sentry Frontend Implementation
 * Following Latest Best Practices from Official SDK
 */

import * as Sentry from "@sentry/browser";

// Initialize Sentry with modern configuration
Sentry.init({
    dsn: window.sentryConfig?.dsn || '',
    environment: window.sentryConfig?.environment || 'development',
    
    // Enable structured logging (required for new patterns)
    _experiments: {
        enableLogs: true,
    },
    
    // Modern integrations
    integrations: [
        // Browser tracing with proper configuration
        Sentry.browserTracingIntegration({
            tracePropagationTargets: [window.location.hostname, /^\//],
            // Enable interaction tracing
            enableInteractions: true,
            // Enable long task instrumentation
            enableLongTask: true,
            // Enable INP (Interaction to Next Paint) tracking
            enableInp: true,
        }),
        // Console logging integration
        Sentry.consoleLoggingIntegration({ 
            levels: ["log", "error", "warn"] 
        }),
        // Replay integration for session recording (optional)
        Sentry.replayIntegration({
            maskAllText: false,
            blockAllMedia: false,
            // Only record on errors in production
            replaysSessionSampleRate: window.sentryConfig?.environment === 'production' ? 0 : 0.1,
            replaysOnErrorSampleRate: 1.0,
        }),
    ],
    
    // Performance monitoring
    tracesSampleRate: window.sentryConfig?.tracesSampleRate || 1.0,
    
    // Session tracking
    autoSessionTracking: true,
    
    // Release tracking
    release: window.sentryConfig?.release || '1.0.0',
    
    // User context
    initialScope: {
        user: window.sentryConfig?.user || null,
        tags: {
            component: 'frontend',
            app_version: window.sentryConfig?.release || '1.0.0',
        }
    },
    
    // Enhanced error filtering
    beforeSend(event, hint) {
        // Filter out non-essential errors
        if (event.exception) {
            const error = hint.originalException;
            
            // Skip network cancellation errors
            if (error?.name === 'AbortError' || error?.message?.includes('cancelled')) {
                return null;
            }
            
            // Skip common browser extension errors
            if (error?.stack?.includes('chrome-extension://') || 
                error?.stack?.includes('moz-extension://')) {
                return null;
            }
        }
        
        // Add custom context
        event.contexts = {
            ...event.contexts,
            custom: {
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight,
                },
                screen: {
                    width: window.screen.width,
                    height: window.screen.height,
                },
            }
        };
        
        return event;
    },
    
    // Enhanced breadcrumb processing
    beforeBreadcrumb(breadcrumb, hint) {
        // Enhance UI click breadcrumbs
        if (breadcrumb.category === 'ui.click') {
            const target = hint.event?.target;
            if (target) {
                breadcrumb.data = {
                    ...breadcrumb.data,
                    tag: target.tagName,
                    id: target.id,
                    'class': target.className,
                    text: target.innerText?.substring(0, 100),
                    business_feature: getBusinessFeatureFromElement(target),
                };
            }
        }
        
        // Enhance navigation breadcrumbs
        if (breadcrumb.category === 'navigation') {
            breadcrumb.data = {
                ...breadcrumb.data,
                referrer: document.referrer,
                viewport_width: window.innerWidth,
                page_load_time: performance.timing.loadEventEnd - performance.timing.navigationStart,
            };
        }
        
        return breadcrumb;
    }
});

// Get logger instance for structured logging
const { logger } = Sentry;

/**
 * Modern Performance Monitoring using Sentry.startSpan
 */
export const ModernPerformanceMonitoring = {
    /**
     * Track page interactions with proper span
     */
    trackInteraction(interactionType, interactionData, callback) {
        return Sentry.startSpan({
            op: `ui.interaction.${interactionType}`,
            name: `User Interaction: ${interactionType}`,
        }, (span) => {
            // Set interaction attributes
            span.setAttribute('interaction.type', interactionType);
            Object.entries(interactionData).forEach(([key, value]) => {
                span.setAttribute(`interaction.${key}`, value);
            });
            
            try {
                const result = callback(span);
                
                // Log the interaction
                logger.info(logger.fmt`User interaction tracked: ${interactionType}`, {
                    type: interactionType,
                    data: interactionData,
                });
                
                return result;
            } catch (error) {
                Sentry.captureException(error);
                logger.error('Interaction tracking failed', { 
                    error: error.message,
                    interactionType 
                });
                throw error;
            }
        });
    },
    
    /**
     * Track API calls with distributed tracing
     */
    trackApiCall(url, method, requestConfig = {}) {
        return Sentry.startSpan({
            op: 'http.client',
            name: `${method} ${url}`,
        }, async (span) => {
            // Set HTTP attributes
            span.setAttribute('http.method', method);
            span.setAttribute('http.url', url);
            
            // Add trace headers for distributed tracing
            const headers = {
                ...requestConfig.headers,
                'sentry-trace': span.toTraceparent(),
                'baggage': span.toBaggage(),
            };
            
            try {
                const startTime = performance.now();
                const response = await fetch(url, {
                    ...requestConfig,
                    method,
                    headers,
                });
                
                const duration = performance.now() - startTime;
                
                // Set response attributes
                span.setAttribute('http.status_code', response.status);
                span.setAttribute('http.response_content_length', response.headers.get('content-length'));
                span.setAttribute('http.duration', duration);
                
                // Log the API call
                logger.info(logger.fmt`API call completed: ${method} ${url}`, {
                    method,
                    url,
                    status: response.status,
                    duration,
                });
                
                return response;
            } catch (error) {
                span.setStatus('internal_error');
                Sentry.captureException(error);
                logger.error('API call failed', {
                    error: error.message,
                    method,
                    url,
                });
                throw error;
            }
        });
    },
    
    /**
     * Track form submissions with validation
     */
    trackFormSubmission(formName, formData, submitCallback) {
        return Sentry.startSpan({
            op: 'form.submit',
            name: `Form Submission: ${formName}`,
        }, async (span) => {
            // Set form attributes
            span.setAttribute('form.name', formName);
            span.setAttribute('form.fields_count', Object.keys(formData).length);
            
            // Track field completion
            const completedFields = Object.entries(formData)
                .filter(([_, value]) => value && value.toString().trim())
                .length;
            
            span.setAttribute('form.completed_fields', completedFields);
            span.setAttribute('form.completion_rate', (completedFields / Object.keys(formData).length) * 100);
            
            try {
                const result = await submitCallback(span, formData);
                
                logger.info(logger.fmt`Form submitted successfully: ${formName}`, {
                    formName,
                    fieldsCount: Object.keys(formData).length,
                    completedFields,
                });
                
                return result;
            } catch (error) {
                span.setStatus('internal_error');
                
                // Check if it's a validation error
                if (error.name === 'ValidationError' || error.status === 422) {
                    logger.warn('Form validation failed', {
                        formName,
                        errors: error.errors || error.message,
                    });
                } else {
                    Sentry.captureException(error);
                    logger.error('Form submission failed', {
                        error: error.message,
                        formName,
                    });
                }
                
                throw error;
            }
        });
    },
};

/**
 * Business Directory Specific Tracking with Modern Patterns
 */
export const BusinessTracking = {
    /**
     * Track business card interactions
     */
    trackBusinessCardInteraction(businessId, businessName, interactionType) {
        return ModernPerformanceMonitoring.trackInteraction('business_card', {
            business_id: businessId,
            business_name: businessName,
            action: interactionType,
        }, (span) => {
            // Add business-specific context
            Sentry.setContext('business_interaction', {
                business_id: businessId,
                business_name: businessName,
                interaction_type: interactionType,
                timestamp: new Date().toISOString(),
            });
            
            // Set user preference
            Sentry.setTag('last_viewed_business', businessId);
        });
    },
    
    /**
     * Track search with results
     */
    trackSearch(searchTerm, filters, resultsCount) {
        return Sentry.startSpan({
            op: 'search',
            name: 'Business Search',
        }, (span) => {
            span.setAttribute('search.term', searchTerm);
            span.setAttribute('search.filters_count', Object.keys(filters).length);
            span.setAttribute('search.results_count', resultsCount);
            
            // Log search analytics
            logger.info(logger.fmt`Search performed: "${searchTerm}"`, {
                term: searchTerm,
                filters,
                resultsCount,
            });
            
            // Track zero results
            if (resultsCount === 0) {
                logger.warn('Search returned no results', {
                    term: searchTerm,
                    filters,
                });
            }
        });
    },
    
    /**
     * Track onboarding progress
     */
    trackOnboardingProgress(step, stepData) {
        const progress = (step / 4) * 100;
        
        return Sentry.startSpan({
            op: 'onboarding.step',
            name: `Onboarding Step ${step}`,
        }, (span) => {
            span.setAttribute('onboarding.step', step);
            span.setAttribute('onboarding.progress', progress);
            span.setAttribute('onboarding.fields_completed', Object.keys(stepData).length);
            
            // Set milestone context
            Sentry.setContext('onboarding_progress', {
                current_step: step,
                progress_percentage: progress,
                completed_fields: Object.keys(stepData),
            });
            
            logger.info(logger.fmt`Onboarding step ${step} completed`, {
                step,
                progress,
                fieldsCompleted: Object.keys(stepData).length,
            });
        });
    },
};

/**
 * Performance Observer for Core Web Vitals
 */
export const WebVitalsTracking = {
    init() {
        // Track Largest Contentful Paint (LCP)
        new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                logger.info('Core Web Vital: LCP', {
                    value: entry.startTime,
                    element: entry.element?.tagName,
                });
                
                Sentry.setMeasurement('lcp', entry.startTime, 'millisecond');
            }
        }).observe({ entryTypes: ['largest-contentful-paint'] });
        
        // Track First Input Delay (FID)
        new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                logger.info('Core Web Vital: FID', {
                    value: entry.processingStart - entry.startTime,
                    eventType: entry.name,
                });
                
                Sentry.setMeasurement('fid', entry.processingStart - entry.startTime, 'millisecond');
            }
        }).observe({ entryTypes: ['first-input'] });
        
        // Track Cumulative Layout Shift (CLS)
        let clsValue = 0;
        new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                    logger.info('Core Web Vital: CLS', {
                        value: clsValue,
                        sources: entry.sources?.length || 0,
                    });
                    
                    Sentry.setMeasurement('cls', clsValue, 'none');
                }
            }
        }).observe({ entryTypes: ['layout-shift'] });
    },
};

// Helper function to determine business feature from element
function getBusinessFeatureFromElement(element) {
    if (!element) return 'unknown';
    
    const features = {
        '[data-business-id]': 'business_card',
        '.onboarding-form': 'business_onboarding',
        '.search-form': 'business_search',
        '.admin-panel': 'admin_dashboard',
        '.business-detail': 'business_detail',
    };
    
    for (const [selector, feature] of Object.entries(features)) {
        if (element.closest(selector)) {
            return feature;
        }
    }
    
    return 'general';
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        WebVitalsTracking.init();
        logger.info('Sentry enhanced tracking initialized');
    });
} else {
    WebVitalsTracking.init();
    logger.info('Sentry enhanced tracking initialized');
}

// Export for use in other modules
export { Sentry, logger };