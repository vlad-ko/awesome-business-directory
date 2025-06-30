import * as Sentry from "@sentry/browser";

// Modern Sentry v9 initialization with comprehensive configuration
Sentry.init({
    dsn: window.sentryConfig?.dsn || '',
    environment: window.sentryConfig?.environment || 'development',
    
    // Enable structured logging (new in v9)
    _experiments: {
        enableLogs: true,
    },
    
    // Modern integrations (no deprecated packages)
    integrations: [
        Sentry.browserTracingIntegration({
            // Capture interactions automatically
            tracePropagationTargets: [window.location.hostname, /^\//],
        }),
        // Send console logs as structured logs
        Sentry.consoleLoggingIntegration({ levels: ["log", "error", "warn"] }),
        // Capture unhandled rejections
        Sentry.globalHandlersIntegration({
            onunhandledrejection: true,
            onerror: true,
        }),
    ],
    
    // Performance monitoring
    tracesSampleRate: window.sentryConfig?.tracesSampleRate || 1.0,
    autoSessionTracking: true,
    release: window.sentryConfig?.release || '1.0.0',
    
    // Enhanced user context
    initialScope: {
        user: window.sentryConfig?.user || null,
        tags: {
            component: 'frontend',
            page_type: getPageType()
        },
        contexts: {
            page: window.sentryConfig?.pageContext || {}
        }
    },
    
    // Enhanced error filtering
    beforeSend(event, hint) {
        // Filter out non-essential errors
        if (event.exception) {
            const error = hint.originalException;
            
            // Don't send cancelled requests
            if (error && error.name === 'AbortError') {
                return null;
            }
            
            // Don't send network errors from ad blockers
            if (error && error.message?.includes('Non-Error promise rejection')) {
                return null;
            }
            
            // Don't send ResizeObserver loop errors (common browser issue)
            if (error && error.message?.includes('ResizeObserver loop')) {
                return null;
            }
        }
        
        return event;
    },

    // Enhanced breadcrumb processing
    beforeBreadcrumb(breadcrumb, hint) {
        // Enhance UI interaction breadcrumbs
        if (breadcrumb.category === 'ui.click') {
            const target = hint.event?.target;
            if (target) {
                breadcrumb.data = {
                    ...breadcrumb.data,
                    business_feature: getBusinessFeatureFromElement(target),
                    element_selector: getElementSelector(target),
                    page_section: getPageSection(target)
                };
            }
        }
        
        // Filter out noisy console messages
        if (breadcrumb.category === 'console' && breadcrumb.level === 'debug') {
            return null;
        }
        
        return breadcrumb;
    }
});

// Get structured logger for modern logging patterns
const { logger } = Sentry;

// Helper functions for enhanced context
function getPageType() {
    const path = window.location.pathname;
    if (path === '/') return 'welcome';
    if (path.includes('/onboard')) return 'onboarding';
    if (path.includes('/businesses')) return 'directory';
    if (path.includes('/admin')) return 'admin';
    return 'other';
}

function getBusinessFeatureFromElement(element) {
    if (!element) return 'unknown';
    
    // Check for business-related features
    if (element.closest('[data-business-id]')) return 'business_card';
    if (element.closest('.onboarding-form')) return 'business_onboarding';
    if (element.closest('.search-form')) return 'business_search';
    if (element.closest('.admin-panel')) return 'admin_management';
    return 'general';
}

function getElementSelector(element) {
    if (!element) return '';
    
    // Create a simple selector path
    const tagName = element.tagName.toLowerCase();
    const id = element.id ? `#${element.id}` : '';
    const className = element.className ? `.${element.className.split(' ').join('.')}` : '';
    
    return `${tagName}${id}${className}`.substring(0, 100); // Limit length
}

function getPageSection(element) {
    if (!element) return 'unknown';
    
    // Identify page sections
    const section = element.closest('header, nav, main, footer, aside, section');
    if (section) {
        const sectionTag = section.tagName.toLowerCase();
        const sectionClass = section.className.split(' ')[0] || '';
        return `${sectionTag}${sectionClass ? '.' + sectionClass : ''}`;
    }
    return 'body';
}

// Enhanced Performance Monitoring with Business Metrics
export const SentryPerformance = {
    /**
     * Track page load with comprehensive metrics
     */
    trackPageLoad() {
        if (typeof performance !== 'undefined') {
            window.addEventListener('load', () => {
                Sentry.startSpan({
                    op: "navigation.load",
                    name: "Page Load Complete"
                }, (span) => {
                    try {
                        const navigation = performance.getEntriesByType('navigation')[0];
                        if (navigation) {
                            // Set comprehensive measurements
                            const loadTime = navigation.loadEventEnd - navigation.loadEventStart;
                            const domTime = navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart;
                            const ttfb = navigation.responseStart - navigation.fetchStart;
                            
                            span.setAttribute("load_time_ms", loadTime);
                            span.setAttribute("dom_time_ms", domTime);
                            span.setAttribute("ttfb_ms", ttfb);
                            span.setAttribute("page_type", getPageType());
                            
                            // Set Sentry measurements
                            Sentry.setMeasurement('page.load_time', loadTime, 'millisecond');
                            Sentry.setMeasurement('page.dom_content_loaded', domTime, 'millisecond');
                            Sentry.setMeasurement('page.ttfb', ttfb, 'millisecond');
                            
                            // Structured logging
                            logger.info(logger.fmt`Page load completed in ${loadTime}ms`, {
                                loadTime,
                                domTime,
                                ttfb,
                                pageType: getPageType()
                            });
                        }
                    } catch (error) {
                        Sentry.captureException(error);
                        logger.warn("Performance tracking failed", { error: error.message });
                    }
                });
            });
        }
    },

    /**
     * Track Core Web Vitals with enhanced context
     */
    trackWebVitals() {
        // Track LCP (Largest Contentful Paint)
        new PerformanceObserver((entryList) => {
            const entries = entryList.getEntries();
            const lastEntry = entries[entries.length - 1];
            
            Sentry.setMeasurement('lcp', lastEntry.startTime, 'millisecond');
            logger.info(logger.fmt`LCP recorded: ${lastEntry.startTime}ms`, {
                lcp: lastEntry.startTime,
                element: lastEntry.element?.tagName
            });
        }).observe({ entryTypes: ['largest-contentful-paint'] });

        // Track FID (First Input Delay)
        new PerformanceObserver((entryList) => {
            const entries = entryList.getEntries();
            entries.forEach(entry => {
                Sentry.setMeasurement('fid', entry.processingStart - entry.startTime, 'millisecond');
                logger.info(logger.fmt`FID recorded: ${entry.processingStart - entry.startTime}ms`, {
                    fid: entry.processingStart - entry.startTime,
                    eventType: entry.name
                });
            });
        }).observe({ entryTypes: ['first-input'] });

        // Track CLS (Cumulative Layout Shift)
        let clsValue = 0;
        new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            }
            Sentry.setMeasurement('cls', clsValue);
            logger.info(logger.fmt`CLS updated: ${clsValue}`, { cls: clsValue });
        }).observe({ entryTypes: ['layout-shift'] });
    }
};

// Enhanced Business Directory Tracking with Modern Patterns
export const BusinessDirectoryTracking = {
    /**
     * Track business card interactions with comprehensive context
     */
    trackBusinessCardClick(businessId, businessName) {
        Sentry.startSpan(
            {
                op: "ui.click",
                name: "Business Card Click",
            },
            (span) => {
                // Attach comprehensive attributes
                span.setAttribute("business_id", businessId);
                span.setAttribute("business_name", businessName);
                span.setAttribute("feature", "business_discovery");
                span.setAttribute("page_type", getPageType());
                
                // Structured logging with template literals
                logger.info(logger.fmt`Business card clicked: ${businessName}`, {
                    businessId,
                    businessName,
                    pageType: getPageType(),
                    timestamp: new Date().toISOString()
                });
                
                // Add breadcrumb for user journey
                Sentry.addBreadcrumb({
                    category: 'business.interaction',
                    message: `Business card viewed: ${businessName}`,
                    data: {
                        business_id: businessId,
                        business_name: businessName,
                        feature: 'discovery'
                    },
                    level: 'info'
                });
            }
        );
    },

    /**
     * Enhanced form submission tracking with distributed tracing
     */
    trackFormSubmission(formElement, formName) {
        return Sentry.startSpan(
            {
                op: "form.submit",
                name: `Form Submission: ${formName}`,
            },
            (span) => {
                // Comprehensive form attributes
                span.setAttribute("form_name", formName);
                span.setAttribute("form_method", formElement.method || 'POST');
                span.setAttribute("form_action", formElement.action || window.location.href);
                span.setAttribute("form_fields_count", formElement.elements.length);
                span.setAttribute("page_type", getPageType());
                
                // Analyze form data for insights (without PII)
                const formData = new FormData(formElement);
                const fieldCount = Array.from(formData.keys()).length;
                const hasFileUploads = Array.from(formElement.elements).some(el => el.type === 'file');
                
                span.setAttribute("filled_fields_count", fieldCount);
                span.setAttribute("has_file_uploads", hasFileUploads);
                
                // Structured logging
                logger.info(logger.fmt`Form submission started: ${formName}`, {
                    formName,
                    fieldCount,
                    hasFileUploads,
                    pageType: getPageType()
                });
                
                // Return trace headers for distributed tracing
                return {
                    span,
                    traceHeaders: {
                        'sentry-trace': span.toTraceparent(),
                        'baggage': span.toBaggage()
                    }
                };
            }
        );
    },

    /**
     * Track onboarding progress with enhanced analytics
     */
    trackOnboardingProgress(step, stepData = {}) {
        Sentry.startSpan(
            {
                op: "business.onboarding.progress",
                name: `Onboarding Step ${step}`,
            },
            (span) => {
                // Step-specific attributes
                span.setAttribute("step", step);
                span.setAttribute("total_steps", 4);
                span.setAttribute("completion_percentage", (step / 4) * 100);
                
                // Enhanced step data
                if (stepData.timeSpent) {
                    span.setAttribute("time_spent_ms", stepData.timeSpent);
                }
                if (stepData.fieldInteractions) {
                    span.setAttribute("field_interactions", stepData.fieldInteractions);
                }
                if (stepData.validationErrors) {
                    span.setAttribute("validation_errors", stepData.validationErrors);
                }
                
                // Business insights
                const completionRate = (step / 4) * 100;
                logger.info(logger.fmt`Onboarding progress: Step ${step} (${completionRate}% complete)`, {
                    step,
                    completionRate,
                    ...stepData
                });
                
                // Track conversion funnel
                Sentry.addBreadcrumb({
                    category: 'business.onboarding',
                    message: `Completed step ${step}`,
                    data: {
                        step,
                        completion_percentage: completionRate,
                        ...stepData
                    },
                    level: 'info'
                });
            }
        );
    },

    /**
     * Track validation errors with enhanced context
     */
    trackValidationError(fieldName, errorType, context = {}) {
        Sentry.startSpan(
            {
                op: "form.validation.error",
                name: `Validation Error: ${fieldName}`,
            },
            (span) => {
                span.setAttribute("field_name", fieldName);
                span.setAttribute("error_type", errorType);
                span.setAttribute("step", context.step || 'unknown');
                
                // Log validation error for analytics
                logger.warn(logger.fmt`Validation error on field ${fieldName}: ${errorType}`, {
                    fieldName,
                    errorType,
                    context
                });
                
                // Don't capture as exception, just track as breadcrumb
                Sentry.addBreadcrumb({
                    category: 'form.validation',
                    message: `Validation error: ${fieldName}`,
                    data: {
                        field_name: fieldName,
                        error_type: errorType,
                        ...context
                    },
                    level: 'warning'
                });
            }
        );
    },

    /**
     * Track search interactions with comprehensive metrics
     */
    trackSearchInteraction(searchTerm, resultsCount, searchTime = null) {
        Sentry.startSpan(
            {
                op: "business.search",
                name: "Business Directory Search",
            },
            (span) => {
                // Search attributes (avoid PII in search terms)
                span.setAttribute("search_term_length", searchTerm.length);
                span.setAttribute("results_count", resultsCount);
                span.setAttribute("has_results", resultsCount > 0);
                
                if (searchTime) {
                    span.setAttribute("search_time_ms", searchTime);
                }
                
                // Search analytics (without exposing search terms)
                const searchAnalytics = {
                    termLength: searchTerm.length,
                    resultsCount,
                    hasResults: resultsCount > 0,
                    searchTime
                };
                
                logger.info(logger.fmt`Search performed: ${resultsCount} results`, searchAnalytics);
                
                // Track search patterns for insights
                Sentry.addBreadcrumb({
                    category: 'business.search',
                    message: `Search completed with ${resultsCount} results`,
                    data: searchAnalytics,
                    level: 'info'
                });
            }
        );
    }
};

// Enhanced Error Boundary for Alpine.js Components
export const SentryErrorBoundary = {
    /**
     * Wrap Alpine.js components with error boundaries
     */
    wrapAlpineComponent(componentName, componentData) {
        return {
            ...componentData,
            
            // Enhanced init with error handling
            init() {
                try {
                    if (componentData.init) {
                        componentData.init.call(this);
                    }
                    
                    logger.debug(logger.fmt`Alpine component initialized: ${componentName}`);
                } catch (error) {
                    Sentry.captureException(error, {
                        tags: {
                            component: 'alpine',
                            component_name: componentName,
                            lifecycle: 'init'
                        }
                    });
                    
                    logger.error(logger.fmt`Alpine component init failed: ${componentName}`, {
                        error: error.message,
                        componentName
                    });
                }
            },
            
            // Wrap all methods with error boundaries
            ...Object.fromEntries(
                Object.entries(componentData)
                    .filter(([key, value]) => typeof value === 'function' && key !== 'init')
                    .map(([key, method]) => [
                        key,
                        function(...args) {
                            try {
                                return method.apply(this, args);
                            } catch (error) {
                                Sentry.captureException(error, {
                                    tags: {
                                        component: 'alpine',
                                        component_name: componentName,
                                        method: key
                                    },
                                    extra: {
                                        method_args: args
                                    }
                                });
                                
                                logger.error(logger.fmt`Alpine method failed: ${componentName}.${key}`, {
                                    error: error.message,
                                    componentName,
                                    methodName: key
                                });
                                
                                throw error; // Re-throw to maintain expected behavior
                            }
                        }
                    ])
            )
        };
    }
};

// Modern Alpine.js Integration with Enhanced Tracking
export function initializeAlpineIntegration() {
    if (typeof window.Alpine !== 'undefined') {
        const Alpine = window.Alpine;
        
        // Enhanced tracking directives
        Alpine.directive('sentry-track', (el, { expression }, { evaluate }) => {
            try {
                const data = evaluate(expression);
                
                el.addEventListener('click', (event) => {
                    Sentry.startSpan({
                        op: "ui.click",
                        name: "Tracked Element Click"
                    }, (span) => {
                        span.setAttribute("element_type", el.tagName.toLowerCase());
                        span.setAttribute("tracking_data", JSON.stringify(data));
                        
                        logger.info("Tracked element clicked", {
                            element: el.tagName.toLowerCase(),
                            data
                        });
                        
                        Sentry.addBreadcrumb({
                            category: 'user.interaction',
                            message: 'Tracked element clicked',
                            data: {
                                element: el.tagName.toLowerCase(),
                                ...data
                            }
                        });
                    });
                });
            } catch (error) {
                Sentry.captureException(error, {
                    tags: { component: 'alpine_directive', directive: 'sentry-track' }
                });
            }
        });
        
        Alpine.directive('track-change', (el, { expression }, { evaluate }) => {
            try {
                const data = evaluate(expression);
                
                el.addEventListener('change', (event) => {
                    BusinessDirectoryTracking.trackFieldInteraction(data.field, el.value);
                });
            } catch (error) {
                Sentry.captureException(error, {
                    tags: { component: 'alpine_directive', directive: 'track-change' }
                });
            }
        });
        
        // Global Alpine error handler
        Alpine.magic('sentry', () => ({
            captureException: Sentry.captureException,
            logger,
            track: BusinessDirectoryTracking
        }));
        
        logger.info("Alpine.js Sentry integration initialized");
    }
}

// Enhanced initialization function
export function initializeSentryFrontend() {
    try {
        // Initialize performance monitoring
        SentryPerformance.trackPageLoad();
        SentryPerformance.trackWebVitals();
        
        // Initialize Alpine integration
        initializeAlpineIntegration();
        
        // Track page view
        logger.info(logger.fmt`Page viewed: ${getPageType()}`, {
            pageType: getPageType(),
            url: window.location.href,
            userAgent: navigator.userAgent.substring(0, 100) // Truncate for privacy
        });
        
        // Global unhandled error catcher
        window.addEventListener('unhandledrejection', (event) => {
            Sentry.captureException(event.reason, {
                tags: { error_type: 'unhandled_promise_rejection' }
            });
        });
        
        logger.info("Sentry frontend integration fully initialized");
        
    } catch (error) {
        // Fallback error handling
        console.error('Sentry initialization failed:', error);
        Sentry.captureException(error, {
            tags: { component: 'sentry_init' }
        });
    }
}

// Auto-initialize if config is available
if (typeof window !== 'undefined' && window.sentryConfig) {
    document.addEventListener('DOMContentLoaded', initializeSentryFrontend);
}

// Export everything for external use
export {
    Sentry,
    logger
}; 