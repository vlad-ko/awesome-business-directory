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
            tracePropagationTargets: [window.location.hostname, /^\\//],
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
    }
});

// Helper function to determine page type
function getPageType() {
    const path = window.location.pathname;
    if (path === '/') return 'welcome';
    if (path.includes('/onboard')) return 'onboarding';
    if (path.includes('/businesses')) return 'directory';
    if (path.includes('/admin')) return 'admin';
    return 'other';
}

// Helper function to extract business feature from DOM element
function getBusinessFeatureFromElement(element) {
    if (!element) return null;
    
    // Check for business-related data attributes
    const businessId = element.getAttribute('data-business-id');
    const businessName = element.getAttribute('data-business-name');
    
    if (businessId || businessName) {
        return { business_id: businessId, business_name: businessName };
    }
    
    return null;
}

// Enhanced Distributed Tracing Utilities
export const SentryTracing = {
    /**
     * Track form submission with distributed tracing
     */
    trackFormSubmission(formElement, formName) {
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
    },
    
    /**
     * Get trace headers for requests
     */
    getTraceHeaders() {
        const span = Sentry.getCurrentHub().getScope()?.getSpan();
        if (span) {
            return {
                'sentry-trace': span.toTraceparent(),
                'baggage': span.toBaggage()
            };
        }
        return {};
    }
};

// Enhanced Performance tracking utilities
export const SentryPerformance = {
    /**
     * Track page load performance
     */
    trackPageLoad() {
        if (typeof performance !== 'undefined') {
            window.addEventListener('load', () => {
                const entries = performance.getEntriesByType('navigation')[0];
                if (entries) {
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
            });
        }
    },
    
    /**
     * Get first paint timing
     */
    getFirstPaint() {
        const entries = performance.getEntriesByType('paint');
        const firstPaint = entries.find(entry => entry.name === 'first-paint');
        return firstPaint ? firstPaint.startTime : null;
    },
    
    /**
     * Get first contentful paint timing
     */
    getFirstContentfulPaint() {
        const entries = performance.getEntriesByType('paint');
        const firstContentfulPaint = entries.find(entry => entry.name === 'first-contentful-paint');
        return firstContentfulPaint ? firstContentfulPaint.startTime : null;
    },
    
    /**
     * Track AJAX requests performance
     */
    trackAjaxRequests() {
        if (window.axios) {
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
                },
                error => {
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
                }
            );
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
            const completionTime = startTime ? new Date() - startTime : 0;
            
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
    }
};

// Business-specific tracking with comprehensive analytics
export const BusinessDirectoryTracking = {
    /**
     * Track page views
     */
    trackPageView(pageName) {
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
    },
    
    /**
     * Track business card interactions
     */
    trackBusinessCardClick(businessId, businessName) {
        Sentry.addBreadcrumb({
            category: 'business.interaction',
            message: `Business card clicked: ${businessName}`,
            data: {
                business_id: businessId,
                business_name: businessName,
                action: 'card_click',
                timestamp: new Date().toISOString()
            },
            level: 'info'
        });

        // Set user context
        Sentry.setTag('last_viewed_business', businessId);
    },
    
    /**
     * Track search interactions
     */
    trackSearchInteraction(searchTerm, resultsCount) {
        Sentry.addBreadcrumb({
            category: 'search.interaction',
            message: `Search performed: ${searchTerm}`,
            data: {
                search_term: searchTerm,
                results_count: resultsCount,
                timestamp: new Date().toISOString()
            },
            level: 'info'
        });
    },
    
    /**
     * Track user interactions
     */
    trackUserInteraction(action, data = {}) {
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
    },
    
    /**
     * Track form progression in multi-step forms
     */
    trackFormProgression(eventType, step, data = {}) {
        const progress_percentage = step ? (step / 4) * 100 : 0;
        
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
    },
    
    /**
     * Track field interactions in forms
     */
    trackFieldInteraction(fieldName, value) {
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
    },
    
    /**
     * Track onboarding progress
     */
    trackOnboardingProgress(step, stepData) {
        const progress_percentage = (step / 4) * 100;
        
        Sentry.addBreadcrumb({
            category: 'onboarding.progress',
            message: `Onboarding step ${step} completed`,
            data: {
                step: step,
                progress_percentage: progress_percentage,
                step_data: stepData,
                timestamp: new Date().toISOString()
            },
            level: 'info'
        });
    },
    
    /**
     * Track admin actions
     */
    trackAdminAction(action, businessId, data = {}) {
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
    }
};

// Alpine.js Integration with comprehensive directives
export const AlpineIntegration = {
    /**
     * Initialize Alpine.js directives for Sentry
     */
    initializeDirectives() {
        // Tracking directive
        Alpine.directive('track', (el, { expression }, { evaluate }) => {
            const trackingData = evaluate(expression);
            
            el.addEventListener('click', () => {
                if (trackingData.action) {
                    BusinessDirectoryTracking.trackUserInteraction(trackingData.action, trackingData);
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
                BusinessDirectoryTracking.trackFieldInteraction(eventName, event.target.value);
            });
        });
    }
};

// Initialize comprehensive frontend tracking
export function initializeSentryFrontend() {
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
        Sentry.captureException(event.error, {
            tags: { source: 'global_error_handler' }
        });
    });

    // Unhandled promise rejection tracking
    window.addEventListener('unhandledrejection', (event) => {
        Sentry.captureException(event.reason, {
            tags: { source: 'unhandled_promise_rejection' }
        });
    });

    console.log('Sentry frontend tracking initialized');
}

// Auto-initialize if window.sentryConfig is available
if (typeof window !== 'undefined' && window.sentryConfig) {
    initializeSentryFrontend();
} 