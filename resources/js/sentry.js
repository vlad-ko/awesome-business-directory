import * as Sentry from "@sentry/browser";

// Initialize Sentry with basic configuration
Sentry.init({
    dsn: window.sentryConfig?.dsn || '',
    environment: window.sentryConfig?.environment || 'development',
    
    // Performance Monitoring
    integrations: [
        Sentry.browserTracingIntegration({
            // Capture interactions automatically
            tracePropagationTargets: [window.location.hostname, /^\//],
        }),
    ],
    
    // Performance - Full tracing in development, sampled in production
    tracesSampleRate: window.sentryConfig?.environment === 'production' ? 0.1 : 1.0,
    
    // Session tracking
    autoSessionTracking: true,
    
    // Enhanced release tracking
    release: window.sentryConfig?.release || '1.0.0',
    
    // User context
    beforeSend(event, hint) {
        // Add business directory context
        event.tags = {
            ...event.tags,
            feature: 'business_directory',
            page_type: getPageType(),
        };
        
        // Add user context if available
        if (window.userContext) {
            event.user = {
                id: window.userContext.id,
                email: window.userContext.email,
                is_admin: window.userContext.is_admin
            };
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

// Simplified Distributed Tracing Utilities
export const SentryTracing = {
    // Track form submission with basic tracing
    trackFormSubmission(formElement, formName, submitData = {}) {
        return Sentry.startSpan({
            name: `Form Submission: ${formName}`,
            op: 'form.submit',
            attributes: {
                form_name: formName,
                form_action: formElement.action,
                form_method: formElement.method,
                ...submitData
            }
        }, (span) => {
            // Get form data
            const formData = new FormData(formElement);
            
            // Add trace headers for distributed tracing
            const traceHeaders = {
                'sentry-trace': span.toTraceparent() || '',
            };
            
            // Return data for the calling code
            return { 
                transaction: span, 
                requestSpan: span, 
                traceHeaders 
            };
        });
    },
    
    // Track AJAX requests
    trackAjaxRequest(url, method, requestData = {}) {
        return Sentry.startSpan({
            name: `AJAX ${method.toUpperCase()}: ${url}`,
            op: 'http.request',
            attributes: {
                url: url,
                method: method.toUpperCase(),
                ...requestData
            }
        }, (span) => {
            // Get trace headers for distributed tracing
            const traceHeaders = {
                'sentry-trace': span.toTraceparent() || '',
            };
            
            return { 
                transaction: span, 
                networkSpan: span, 
                traceHeaders 
            };
        });
    },
    
    // Track business operations
    trackBusinessOperation(operation, businessData = {}) {
        return Sentry.startSpan({
            name: `Business Operation: ${operation}`,
            op: 'business.operation',
            attributes: {
                operation: operation,
                business_id: businessData.business_id,
                business_name: businessData.business_name,
                ...businessData
            }
        }, (span) => {
            return span;
        });
    }
};

// Performance tracking utilities
export const SentryPerformance = {
    // Track page load performance
    trackPageLoad() {
        if (typeof window !== 'undefined' && window.performance) {
            const navigation = performance.getEntriesByType('navigation')[0];
            if (navigation) {
                Sentry.setMeasurement('page.load_time', navigation.loadEventEnd - navigation.loadEventStart, 'millisecond');
                Sentry.setMeasurement('page.dom_content_loaded', navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart, 'millisecond');
            }
        }
    },
    
    // Track form metrics
    trackFormMetrics(formElement, formName) {
        if (!formElement) return;
        
        const startTime = performance.now();
        
        formElement.addEventListener('submit', () => {
            const endTime = performance.now();
            const fillTime = endTime - startTime;
            
            Sentry.setMeasurement(`form.${formName}.fill_time`, fillTime, 'millisecond');
            
            Sentry.addBreadcrumb({
                category: 'form',
                message: `Form ${formName} submitted`,
                data: {
                    form_name: formName,
                    fill_time: fillTime,
                    field_count: formElement.elements.length
                }
            });
        });
    }
};

// Business-specific tracking
export const BusinessDirectoryTracking = {
    // Track business card clicks
    trackBusinessCardClick(businessId, businessName) {
        Sentry.addBreadcrumb({
            category: 'business',
            message: `Viewed business: ${businessName}`,
            data: {
                business_id: businessId,
                business_name: businessName,
                page_type: getPageType()
            }
        });
        
        Sentry.setTag('last_viewed_business', businessId);
    },
    
    // Track onboarding progress
    trackOnboardingProgress(step, stepData) {
        Sentry.addBreadcrumb({
            category: 'onboarding',
            message: `Onboarding step ${step} completed`,
            data: {
                step: step,
                data_keys: Object.keys(stepData),
                page_type: getPageType()
            }
        });
        
        Sentry.setTag('onboarding_step', step);
    },
    
    // Track search interactions
    trackSearchInteraction(searchTerm, resultsCount) {
        Sentry.addBreadcrumb({
            category: 'search',
            message: `Search performed: ${searchTerm}`,
            data: {
                search_term: searchTerm,
                results_count: resultsCount,
                page_type: getPageType()
            }
        });
        
        if (resultsCount === 0) {
            Sentry.captureMessage('Empty search results', {
                level: 'info',
                tags: {
                    feature: 'search',
                    page_type: getPageType()
                },
                extra: {
                    search_term: searchTerm
                }
            });
        }
    }
};

// Initialize Alpine.js integration
export function initializeAlpineIntegration() {
    document.addEventListener('alpine:init', () => {
        console.log('Initializing Alpine.js + Sentry integration');
        
        // Add Alpine directive for tracking
        if (window.Alpine) {
            window.Alpine.directive('track', (el, { expression }) => {
                try {
                    const trackingData = expression ? JSON.parse(expression) : {};
                    
                    el.addEventListener('click', () => {
                        Sentry.addBreadcrumb({
                            category: 'user.interaction',
                            message: `User clicked: ${trackingData.action || 'unknown'}`,
                            data: {
                                action: trackingData.action,
                                element: el.tagName,
                                page_type: getPageType()
                            }
                        });
                    });
                } catch (error) {
                    console.warn('Error setting up Sentry tracking directive:', error);
                }
            });
        }
    });
}

// Main initialization function
export function initializeSentryFrontend() {
    console.log('Initializing Sentry frontend integration');
    
    // Track page load
    SentryPerformance.trackPageLoad();
    
    // Initialize Alpine integration
    initializeAlpineIntegration();
    
    // Track unhandled errors
    window.addEventListener('error', (event) => {
        Sentry.captureException(event.error, {
            tags: {
                page_type: getPageType(),
                error_type: 'unhandled'
            }
        });
    });
    
    // Track unhandled promise rejections
    window.addEventListener('unhandledrejection', (event) => {
        Sentry.captureException(event.reason, {
            tags: {
                page_type: getPageType(),
                error_type: 'unhandled_promise'
            }
        });
    });
    
    console.log('Sentry frontend integration initialized successfully');
}

// Auto-initialize if window.sentryConfig is available
if (typeof window !== 'undefined' && window.sentryConfig) {
    initializeSentryFrontend();
} 