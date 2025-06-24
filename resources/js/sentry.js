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
    
    // Enhanced release tracking
    release: window.sentryConfig?.release || '1.0.0',
    
    // Advanced configuration options
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
        
        // Enhanced error processing
        try {
            if (hint.originalException instanceof Error) {
                // Process error for better debugging
                Sentry.captureException(hint.originalException, {
                    tags: { source: 'frontend_error_boundary' }
                });
            }
        } catch (error) {
            console.warn('Sentry beforeSend processing failed:', error);
        }
        
        return event;
    },

    beforeBreadcrumb(breadcrumb, hint) {
        // Filter and enhance breadcrumbs
        if (breadcrumb.category === 'ui.click') {
            breadcrumb.data = {
                ...breadcrumb.data,
                business_feature: getBusinessFeatureFromElement(hint.event?.target)
            };
        }
        return breadcrumb;
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
    // Track form submission with comprehensive tracing
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
    
    // Track AJAX requests with enhanced monitoring
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

// Enhanced Performance tracking utilities
export const SentryPerformance = {
    // Track page load performance with comprehensive metrics
    trackPageLoad() {
        if (typeof window !== 'undefined' && window.performance) {
            const navigation = performance.getEntriesByType('navigation')[0];
            if (navigation) {
                Sentry.setMeasurement('page.load_time', navigation.loadEventEnd - navigation.loadEventStart, 'millisecond');
                Sentry.setMeasurement('page.dom_content_loaded', navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart, 'millisecond');
                
                // Additional performance metrics
                const paintEntries = performance.getEntriesByType('paint');
                paintEntries.forEach(paint => {
                    if (paint.name === 'first-paint') {
                        Sentry.setMeasurement('paint.first_paint', paint.startTime, 'millisecond');
                    }
                });
            }
        }
    },
    
    // Track form metrics with detailed analysis
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

// Business-specific tracking with comprehensive analytics
export const BusinessDirectoryTracking = {
    // Track business card clicks with enhanced context
    trackBusinessCardClick(businessId, businessName) {
        Sentry.addBreadcrumb({
            category: 'business.interaction',
            message: `Viewed business: ${businessName}`,
            data: {
                business_id: businessId,
                business_name: businessName,
                page_type: getPageType(),
                feature: 'business_discovery'
            }
        });
        
        Sentry.setTag('last_viewed_business', businessId);
    },
    
    // Track onboarding progress with detailed metrics
    trackOnboardingProgress(step, stepData) {
        Sentry.addBreadcrumb({
            category: 'onboarding.progress',
            message: `Onboarding step ${step} completed`,
            data: {
                step: step,
                data_keys: Object.keys(stepData),
                page_type: getPageType(),
                feature: 'business_onboarding'
            }
        });
        
        Sentry.setTag('onboarding_step', step);
    },
    
    // Track search interactions with comprehensive data
    trackSearchInteraction(searchTerm, resultsCount) {
        Sentry.addBreadcrumb({
            category: 'business.interaction',
            message: `Business search performed`,
            data: {
                search_term: searchTerm,
                results_count: resultsCount,
                feature: 'business_search'
            }
        });
    }
};

// Alpine.js Integration with comprehensive directives
export function initializeAlpineIntegration() {
    if (typeof window.Alpine !== 'undefined') {
        // Sentry tracking directive
        window.Alpine.directive('sentry-track', (el, { expression }, { evaluate }) => {
            const trackingData = evaluate(expression);
            
            el.addEventListener('click', () => {
                Sentry.addBreadcrumb({
                    category: 'ui.interaction',
                    message: `Element clicked: ${trackingData.action}`,
                    data: trackingData
                });
            });
        });

        // Enhanced tracking directive
        window.Alpine.directive('track', (el, { expression }, { evaluate }) => {
            const trackingData = evaluate(expression);
            
            el.addEventListener('click', () => {
                Sentry.addBreadcrumb({
                    category: 'ui.interaction',
                    message: `Tracked interaction: ${trackingData.action}`,
                    data: {
                        ...trackingData,
                        timestamp: new Date().toISOString(),
                        url: window.location.href
                    }
                });
            });
        });

        // Track change events
        window.Alpine.directive('track-change', (el, { expression }, { evaluate }) => {
            const trackingData = evaluate(expression);
            
            el.addEventListener('change', (event) => {
                Sentry.addBreadcrumb({
                    category: 'ui.input',
                    message: `Input changed: ${trackingData.field}`,
                    data: {
                        ...trackingData,
                        value: event.target.value,
                        timestamp: new Date().toISOString()
                    }
                });
            });
        });
    }
}

// Initialize comprehensive frontend monitoring
export function initializeSentryFrontend() {
    // Track page load performance
    SentryPerformance.trackPageLoad();
    
    // Initialize Alpine integration
    initializeAlpineIntegration();
    
    // Set up AJAX monitoring with axios interceptors
    if (window.axios) {
        // Request interceptor
        window.axios.interceptors.request.use(
            (config) => {
                const startTime = performance.now();
                config.metadata = { startTime };
                
                // Add distributed tracing headers
                const currentTransaction = Sentry.getCurrentHub().getScope()?.getTransaction();
                if (currentTransaction) {
                    config.headers['sentry-trace'] = currentTransaction.toTraceparent();
                }
                
                return config;
            },
            (error) => {
                Sentry.captureException(error);
                return Promise.reject(error);
            }
        );
        
        // Response interceptor with performance monitoring
        window.axios.interceptors.response.use(
            (response) => {
                const endTime = performance.now();
                const duration = endTime - response.config.metadata.startTime;
                
                // Track slow requests
                if (duration > 2000) {
                    Sentry.addBreadcrumb({
                        category: 'http',
                        message: `Slow AJAX request: ${response.config.url}`,
                        data: {
                            url: response.config.url,
                            method: response.config.method,
                            duration: duration,
                            status: response.status
                        }
                    });
                }
                
                return response;
            },
            (error) => {
                Sentry.captureException(error);
                return Promise.reject(error);
            }
        );
    }
    
    // Global error handler
    window.addEventListener('error', (event) => {
        Sentry.captureException(event.error, {
            tags: { source: 'global_error_handler' }
        });
    });
    
    // Promise rejection handler
    window.addEventListener('unhandledrejection', (event) => {
        Sentry.captureException(event.reason, {
            tags: { source: 'unhandled_promise_rejection' }
        });
    });
}

// Auto-initialize if window.sentryConfig is available
if (typeof window !== 'undefined' && window.sentryConfig) {
    initializeSentryFrontend();
} 