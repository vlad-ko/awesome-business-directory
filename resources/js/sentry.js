import * as Sentry from "@sentry/browser";
import { BrowserTracing } from "@sentry/tracing";

// Initialize Sentry with comprehensive configuration
Sentry.init({
    dsn: window.sentryConfig?.dsn || '',
    environment: window.sentryConfig?.environment || 'development',
    
    // Performance Monitoring
    integrations: [
        new BrowserTracing({
            // Capture interactions automatically
            tracePropagationTargets: [window.location.hostname, /^\//],
        }),
    ],
    
    // Performance - Full tracing in development, sampled in production
    tracesSampleRate: window.sentryConfig?.tracesSampleRate || 1.0,
    
    // Session tracking
    autoSessionTracking: true,
    
    // Release tracking
    release: window.sentryConfig?.release || '1.0.0',
    
    // User context
    initialScope: {
        user: window.sentryConfig?.user || null,
        tags: {
            component: 'frontend'
        }
    },
    
    // Enhanced error handling
    beforeSend(event, hint) {
        // Filter out non-essential errors
        if (event.exception) {
            const error = hint.originalException;
            if (error && error.name === 'AbortError') {
                return null; // Don't send cancelled requests
            }
        }
        return event;
    },

    // Enhanced breadcrumb processing
    beforeBreadcrumb(breadcrumb, hint) {
        // Filter and enhance breadcrumbs for better debugging
        if (breadcrumb.category === 'ui.click') {
            breadcrumb.data = {
                ...breadcrumb.data,
                business_feature: getBusinessFeatureFromElement(hint.event?.target)
            };
        }
        return breadcrumb;
    }
});

// Helper function for feature detection
function getBusinessFeatureFromElement(element) {
    if (!element) return 'unknown';
    
    // Check for business-related features
    if (element.closest('[data-business-id]')) {
        return 'business_card';
    }
    if (element.closest('.onboarding-form')) {
        return 'business_onboarding';
    }
    if (element.closest('.search-form')) {
        return 'business_search';
    }
    return 'general';
}

// Helper function to determine page type
function getPageType() {
    const path = window.location.pathname;
    if (path === '/') return 'welcome';
    if (path.includes('/onboard')) return 'onboarding';
    if (path.includes('/businesses')) return 'directory';
    if (path.includes('/admin')) return 'admin';
    return 'other';
}

// Performance Monitoring Utilities
export const SentryPerformance = {
    /**
     * Track page load performance with comprehensive metrics
     */
    trackPageLoad() {
        if (typeof performance !== 'undefined') {
            window.addEventListener('load', () => {
                try {
                    const navigation = performance.getEntriesByType('navigation')[0];
                    if (navigation) {
                        // Set comprehensive performance measurements
                        Sentry.setMeasurement('page.load_time', navigation.loadEventEnd - navigation.loadEventStart, 'millisecond');
                        Sentry.setMeasurement('page.dom_content_loaded', navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart, 'millisecond');
                        
                        const entries = performance.getEntriesByType('navigation')[0];
                        Sentry.addBreadcrumb({
                            category: 'performance',
                            message: 'Page load metrics',
                            data: {
                                loadTime: entries.loadEventEnd - entries.fetchStart,
                                domContentLoaded: entries.domContentLoadedEventEnd - entries.fetchStart,
                                firstPaint: this.getFirstPaint(),
                                firstContentfulPaint: this.getFirstContentfulPaint()
                            },
                            level: 'info'
                        });
                    }
                } catch (error) {
                    console.warn('Performance tracking failed:', error);
                }
            });
        }
    },

    /**
     * Get first paint timing
     */
    getFirstPaint() {
        try {
            const entries = performance.getEntriesByType('paint');
            const firstPaint = entries.find(entry => entry.name === 'first-paint');
            return firstPaint ? firstPaint.startTime : null;
        } catch (error) {
            return null;
        }
    },

    /**
     * Get first contentful paint timing  
     */
    getFirstContentfulPaint() {
        try {
            const entries = performance.getEntriesByType('paint');
            const firstContentfulPaint = entries.find(entry => entry.name === 'first-contentful-paint');
            return firstContentfulPaint ? firstContentfulPaint.startTime : null;
        } catch (error) {
            return null;
        }
    },

    /**
     * Track AJAX requests performance
     */
    trackAjaxRequests() {
        if (window.axios) {
            try {
                // Request interceptor
                window.axios.interceptors.request.use(
                    config => {
                        config.metadata = { startTime: new Date() };
                        return config;
                    },
                    error => Promise.reject(error)
                );

                // Response interceptor
                window.axios.interceptors.response.use(
                    response => {
                        try {
                            const duration = new Date() - response.config.metadata.startTime;
                            
                            // Track slow requests
                            if (duration > 2000) {
                                Sentry.addBreadcrumb({
                                    category: 'http.slow',
                                    message: `Slow request: ${response.config.url}`,
                                    data: {
                                        url: response.config.url,
                                        method: response.config.method,
                                        duration: duration,
                                        status: response.status
                                    },
                                    level: 'warning'
                                });
                            }
                            
                            return response;
                        } catch (error) {
                            console.warn('Response interceptor error:', error);
                            return response;
                        }
                    },
                    error => {
                        try {
                            const duration = error.config ? new Date() - error.config.metadata.startTime : 0;
                            
                            Sentry.addBreadcrumb({
                                category: 'http.error',
                                message: `HTTP Error: ${error.config?.url}`,
                                data: {
                                    url: error.config?.url,
                                    method: error.config?.method,
                                    duration: duration,
                                    status: error.response?.status
                                },
                                level: 'error'
                            });
                            
                            return Promise.reject(error);
                        } catch (trackingError) {
                            console.warn('Error tracking failed:', trackingError);
                            return Promise.reject(error);
                        }
                    }
                );
            } catch (error) {
                console.warn('Failed to set up axios interceptors:', error);
            }
        }
    },

    /**
     * Track form metrics comprehensively
     */
    trackFormMetrics(formElement, formName) {
        if (!formElement) return;

        let startTime = null;
        let fieldInteractions = 0;
        let fieldErrors = 0;

        try {
            // Track form start
            const handleFormFocus = () => {
                if (!startTime) {
                    startTime = new Date();
                    Sentry.addBreadcrumb({
                        category: 'form.progression',
                        message: `Form started: ${formName}`,
                        data: { form_name: formName, timestamp: startTime.toISOString() },
                        level: 'info'
                    });
                }
            };

            // Track field interactions
            const handleFieldInteraction = (event) => {
                fieldInteractions++;
                Sentry.addBreadcrumb({
                    category: 'form.interaction',
                    message: `Field interaction: ${event.target.name || event.target.id}`,
                    data: {
                        form_name: formName,
                        field: event.target.name || event.target.id,
                        field_type: event.target.type,
                        interaction_count: fieldInteractions
                    },
                    level: 'info'
                });
            };

            // Track form submission
            const handleFormSubmit = () => {
                let completionTime = 0;
                if (startTime) {
                    completionTime = new Date() - startTime;
                }
                
                Sentry.addBreadcrumb({
                    category: 'form.progression',
                    message: `Form submitted: ${formName}`,
                    data: {
                        form_name: formName,
                        completion_time: completionTime,
                        field_interactions: fieldInteractions,
                        field_errors: fieldErrors
                    },
                    level: 'info'
                });
                
                // Capture comprehensive form completion
                Sentry.captureMessage(`Form completed: ${formName}`, 'info');
            };

            // Track form abandonment
            const handleBeforeUnload = () => {
                if (startTime && fieldInteractions > 0) {
                    const abandonmentTime = new Date() - startTime;
                    Sentry.addBreadcrumb({
                        category: 'form.abandonment',
                        message: `Form abandoned: ${formName}`,
                        data: {
                            form_name: formName,
                            time_before_abandonment: abandonmentTime,
                            field_interactions: fieldInteractions
                        },
                        level: 'warning'
                    });
                }
            };

            // Attach event listeners
            formElement.addEventListener('focusin', handleFormFocus, { once: true });
            formElement.addEventListener('input', handleFieldInteraction);
            formElement.addEventListener('change', handleFieldInteraction);
            formElement.addEventListener('submit', handleFormSubmit);
            window.addEventListener('beforeunload', handleBeforeUnload);

            // Track form validation errors
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE && 
                            (node.classList?.contains('error') || node.classList?.contains('invalid'))) {
                            fieldErrors++;
                        }
                    });
                });
            });

            observer.observe(formElement, { childList: true, subtree: true });
        } catch (error) {
            console.warn('Form metrics tracking failed:', error);
            Sentry.captureException(error, {
                tags: { source: 'form_metrics_tracking' }
            });
        }
    }
};

// Business Directory Specific Tracking
export const BusinessDirectoryTracking = {
    /**
     * Track page views
     */
    trackPageView(pageName) {
        try {
            Sentry.addBreadcrumb({
                category: 'navigation',
                message: `Page viewed: ${pageName}`,
                data: {
                    page_name: pageName,
                    url: window.location.href,
                    referrer: document.referrer,
                    timestamp: new Date().toISOString()
                },
                level: 'info'
            });
        } catch (error) {
            console.warn('Page view tracking failed:', error);
        }
    },

    /**
     * Track business card interactions
     */
    trackBusinessCardClick(businessId, businessName) {
        try {
            Sentry.addBreadcrumb({
                category: 'business.interaction',
                message: `Business card clicked: ${businessName}`,
                data: {
                    business_id: businessId,
                    business_name: businessName,
                    action: 'card_click',
                    feature: 'business_discovery',
                    timestamp: new Date().toISOString()
                },
                level: 'info'
            });

            // Set user context
            Sentry.setTag('last_viewed_business', businessId);
        } catch (error) {
            console.warn('Business card click tracking failed:', error);
        }
    },

    /**
     * Track search interactions
     */
    trackSearchInteraction(searchTerm, resultsCount) {
        try {
            Sentry.addBreadcrumb({
                category: 'search.interaction',
                message: `Search performed: ${searchTerm}`,
                data: {
                    search_term: searchTerm,
                    results_count: resultsCount,
                    feature: 'business_search',
                    timestamp: new Date().toISOString()
                },
                level: 'info'
            });
        } catch (error) {
            console.warn('Search interaction tracking failed:', error);
        }
    },

    /**
     * Track user interactions
     */
    trackUserInteraction(action, data = {}) {
        try {
            Sentry.addBreadcrumb({
                category: 'user.interaction',
                message: `User interaction: ${action}`,
                data: {
                    action: action,
                    ...data,
                    timestamp: new Date().toISOString()
                },
                level: 'info'
            });
        } catch (error) {
            console.warn('User interaction tracking failed:', error);
        }
    },

    /**
     * Track form progression in multi-step forms
     */
    trackFormProgression(eventType, step, data = {}) {
        try {
            const progress_percentage = (step / 4) * 100;
            
            Sentry.addBreadcrumb({
                category: 'form.progression',
                message: `Form progression: ${eventType}`,
                data: {
                    event_type: eventType,
                    step: step,
                    progress_percentage: progress_percentage,
                    ...data,
                    timestamp: new Date().toISOString()
                },
                level: 'info'
            });
            
            // Enhanced context with extra data
            Sentry.setContext('form_progression', {
                current_step: step,
                progress_percentage: progress_percentage,
                event_type: eventType,
                page_type: getPageType(),
                extra: {
                    form_feature: 'business_onboarding',
                    user_engagement: 'active',
                    session_data: data
                }
            });
        } catch (error) {
            console.warn('Form progression tracking failed:', error);
        }
    },

    /**
     * Track field interactions in forms
     */
    trackFieldInteraction(fieldName, value) {
        try {
            Sentry.addBreadcrumb({
                category: 'form.field_interaction',
                message: `Field interaction: ${fieldName}`,
                data: {
                    field_name: fieldName,
                    has_value: !!value,
                    value_length: value ? value.length : 0,
                    timestamp: new Date().toISOString()
                },
                level: 'info'
            });
        } catch (error) {
            console.warn('Field interaction tracking failed:', error);
        }
    },

    /**
     * Track onboarding progress
     */
    trackOnboardingProgress(step, stepData) {
        try {
            const progress_percentage = (step / 4) * 100;
            
            Sentry.addBreadcrumb({
                category: 'onboarding.progress',
                message: `Onboarding step ${step} completed`,
                data: {
                    step: step,
                    progress_percentage: progress_percentage,
                    step_data: stepData,
                    feature: 'business_onboarding',
                    timestamp: new Date().toISOString()
                },
                level: 'info'
            });
        } catch (error) {
            console.warn('Onboarding progress tracking failed:', error);
        }
    },

    /**
     * Track admin actions
     */
    trackAdminAction(action, businessId, data = {}) {
        try {
            Sentry.addBreadcrumb({
                category: 'admin.action',
                message: `Admin action: ${action}`,
                data: {
                    action: action,
                    business_id: businessId,
                    admin_user: window.userContext?.id,
                    ...data,
                    timestamp: new Date().toISOString()
                },
                level: 'info'
            });
        } catch (error) {
            console.warn('Admin action tracking failed:', error);
        }
    }
};

// SentryTracing utilities for distributed tracing
export const SentryTracing = {
    /**
     * Track form submission with distributed tracing
     */
    trackFormSubmission(formElement, formName) {
        try {
            const transaction = Sentry.startTransaction({
                name: `Form Submission: ${formName}`,
                op: 'form.submit'
            });

            // Add trace headers for distributed tracing
            const traceHeaders = {
                'sentry-trace': transaction.toTraceparent(),
                'baggage': transaction.toBaggage()
            };

            return { transaction, traceHeaders };
        } catch (error) {
            console.warn('Form submission tracking failed:', error);
            return { transaction: null, traceHeaders: {} };
        }
    },

    /**
     * Get trace headers for requests
     */
    getTraceHeaders() {
        try {
            const span = Sentry.getCurrentHub().getScope()?.getSpan();
            if (span) {
                return {
                    'sentry-trace': span.toTraceparent(),
                    'baggage': span.toBaggage()
                };
            }
            return {};
        } catch (error) {
            console.warn('Failed to get trace headers:', error);
            return {};
        }
    }
};

// Alpine.js Integration with comprehensive directives
export const AlpineIntegration = {
    /**
     * Initialize Alpine.js directives for Sentry
     */
    initializeDirectives() {
        try {
            // Enhanced tracking directive
            Alpine.directive('sentry-track', (el, { expression }, { evaluate }) => {
                const trackingData = evaluate(expression);
                
                el.addEventListener('click', () => {
                    try {
                        Sentry.addBreadcrumb({
                            category: 'ui.interaction',
                            message: `Element clicked: ${trackingData.action}`,
                            data: trackingData
                        });
                    } catch (error) {
                        console.warn('Sentry track directive failed:', error);
                    }
                });
            });

            // Tracking directive
            Alpine.directive('track', (el, { expression }, { evaluate }) => {
                const trackingData = evaluate(expression);
                
                el.addEventListener('click', () => {
                    try {
                        if (trackingData.action) {
                            BusinessDirectoryTracking.trackUserInteraction(trackingData.action, trackingData);
                        }
                    } catch (error) {
                        console.warn('Track directive failed:', error);
                    }
                });
            });

            // Form tracking directive
            Alpine.directive('track-form', (el, { expression }, { evaluate }) => {
                const formName = evaluate(expression);
                SentryPerformance.trackFormMetrics(el, formName);
            });

            // Change tracking directive
            Alpine.directive('track-change', (el, { expression }, { evaluate }) => {
                const eventName = evaluate(expression);
                
                el.addEventListener('change', (event) => {
                    try {
                        BusinessDirectoryTracking.trackFieldInteraction(eventName, event.target.value);
                    } catch (error) {
                        console.warn('Track change directive failed:', error);
                    }
                });
            });
        } catch (error) {
            console.warn('Alpine directive initialization failed:', error);
        }
    }
};

// Legacy function name for backward compatibility
export function initializeAlpineIntegration() {
    AlpineIntegration.initializeDirectives();
}

// Initialize comprehensive frontend tracking
export function initializeSentryFrontend() {
    try {
        // Set user context if available
        if (window.userContext) {
            Sentry.setUser(window.userContext);
        }

        // Initialize performance tracking
        SentryPerformance.trackPageLoad();
        SentryPerformance.trackAjaxRequests();

        // Initialize Alpine.js integration
        if (window.Alpine) {
            AlpineIntegration.initializeDirectives();
        }

        // Track initial page load
        BusinessDirectoryTracking.trackPageView(
            document.title || window.location.pathname
        );

        // Global error handling
        window.addEventListener('error', (event) => {
            try {
                Sentry.captureException(event.error, {
                    tags: { source: 'global_error_handler' }
                });
            } catch (error) {
                console.warn('Global error handler failed:', error);
            }
        });

        // Unhandled promise rejection tracking
        window.addEventListener('unhandledrejection', (event) => {
            try {
                Sentry.captureException(event.reason, {
                    tags: { source: 'unhandled_promise_rejection' }
                });
            } catch (error) {
                console.warn('Unhandled rejection tracking failed:', error);
            }
        });

        console.log('Sentry frontend tracking initialized');
    } catch (error) {
        console.error('Failed to initialize Sentry frontend:', error);
    }
}

// Auto-initialize if window.sentryConfig is available
if (typeof window !== 'undefined' && window.sentryConfig) {
    initializeSentryFrontend();
} 