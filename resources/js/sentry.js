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
            
            // Track page loads and navigation
            routingInstrumentation: Sentry.browserTracingIntegration({
                enableInp: true // Track Interaction to Next Paint
            }),
        }),
    ],
    
    // Performance
    tracesSampleRate: window.sentryConfig?.tracesSampleRate || 1.0,
    
    // Session tracking
    autoSessionTracking: true,
    
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
            event.user = window.userContext;
        }
        
        return event;
    },
    
    // Enhanced error context
    beforeBreadcrumb(breadcrumb, hint) {
        // Enhance breadcrumbs with business context
        if (breadcrumb.category === 'ui.click') {
            const target = hint?.event?.target;
            if (target) {
                breadcrumb.data = {
                    ...breadcrumb.data,
                    element_id: target.id,
                    element_class: target.className,
                    alpine_data: target._x_dataStack?.[0] ? 'present' : 'none'
                };
            }
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

// Alpine.js Error Tracking Integration
export function initializeAlpineIntegration() {
    // Wait for Alpine to be available
    document.addEventListener('alpine:init', () => {
        console.log('Initializing Alpine.js + Sentry integration');
        
        // Custom directive for error tracking
        Alpine.directive('sentry-track', (el, { expression }, { evaluateLater, cleanup }) => {
            const evaluate = evaluateLater(expression);
            
            // Wrap element interactions with Sentry tracking
            const originalAddEventListener = el.addEventListener;
            el.addEventListener = function(type, listener, options) {
                const wrappedListener = function(event) {
                    try {
                        return listener.call(this, event);
                    } catch (error) {
                        Sentry.captureException(error, {
                            tags: {
                                component: 'alpine',
                                directive: expression,
                                event_type: type
                            },
                            extra: {
                                element_tag: el.tagName,
                                element_id: el.id,
                                element_classes: el.className
                            }
                        });
                        throw error;
                    }
                };
                
                return originalAddEventListener.call(this, type, wrappedListener, options);
            };
        });
        
        // Custom directive for interaction tracking
        Alpine.directive('track', (el, { expression }) => {
            const trackingData = expression ? JSON.parse(expression) : {};
            
            el.addEventListener('click', () => {
                Sentry.addBreadcrumb({
                    category: 'user.interaction',
                    message: `User clicked: ${trackingData.action || el.textContent?.substring(0, 30)}`,
                    data: {
                        action: trackingData.action,
                        element: el.tagName,
                        element_id: el.id,
                        page_type: getPageType(),
                        timestamp: new Date().toISOString()
                    },
                    level: 'info'
                });
                
                // Track in Sentry as custom event
                Sentry.captureMessage(`User Interaction: ${trackingData.action}`, {
                    level: 'info',
                    tags: {
                        interaction_type: 'click',
                        feature: 'user_engagement',
                        page_type: getPageType()
                    },
                    extra: trackingData
                });
            });
        });
        
        // Track Alpine data changes
        Alpine.directive('track-change', (el, { expression }) => {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName?.startsWith('x-')) {
                        Sentry.addBreadcrumb({
                            category: 'alpine.state',
                            message: `Alpine state changed: ${expression}`,
                            data: {
                                attribute: mutation.attributeName,
                                old_value: mutation.oldValue,
                                new_value: el.getAttribute(mutation.attributeName)
                            }
                        });
                    }
                });
            });
            
            observer.observe(el, {
                attributes: true,
                attributeOldValue: true,
                attributeFilter: Array.from(el.attributes).map(attr => attr.name).filter(name => name.startsWith('x-'))
            });
        });
    });
    
    // Track Alpine initialization
    document.addEventListener('alpine:initialized', () => {
        Sentry.addBreadcrumb({
            category: 'alpine.lifecycle',
            message: 'Alpine.js fully initialized',
            level: 'info'
        });
        
        console.log('Alpine.js + Sentry integration complete');
    });
}

// Performance tracking utilities
export const SentryPerformance = {
    // Track page load performance
    trackPageLoad() {
        window.addEventListener('load', () => {
            const navigation = performance.getEntriesByType('navigation')[0];
            
            Sentry.captureMessage('Page Load Performance', {
                level: 'info',
                tags: {
                    performance_metric: 'page_load',
                    page_type: getPageType()
                },
                extra: {
                    load_time: navigation.loadEventEnd - navigation.loadEventStart,
                    dom_content_loaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                    first_paint: performance.getEntriesByName('first-paint')[0]?.startTime,
                    first_contentful_paint: performance.getEntriesByName('first-contentful-paint')[0]?.startTime
                }
            });
        });
    },
    
    // Track AJAX requests
    trackAjaxRequests() {
        // Intercept axios requests
        if (window.axios) {
            window.axios.interceptors.request.use(
                (config) => {
                    config.metadata = { startTime: new Date() };
                    return config;
                },
                (error) => {
                    Sentry.captureException(error, {
                        tags: { request_phase: 'setup' }
                    });
                    return Promise.reject(error);
                }
            );
            
            window.axios.interceptors.response.use(
                (response) => {
                    const duration = new Date() - response.config.metadata.startTime;
                    
                    Sentry.addBreadcrumb({
                        category: 'http.request',
                        message: `${response.config.method?.toUpperCase()} ${response.config.url}`,
                        data: {
                            status_code: response.status,
                            duration_ms: duration,
                            url: response.config.url
                        }
                    });
                    
                    if (duration > 2000) { // Slow request threshold
                        Sentry.captureMessage('Slow HTTP Request', {
                            level: 'warning',
                            tags: {
                                performance_issue: 'slow_request',
                                request_type: 'ajax'
                            },
                            extra: {
                                url: response.config.url,
                                duration_ms: duration,
                                status: response.status
                            }
                        });
                    }
                    
                    return response;
                },
                (error) => {
                    Sentry.captureException(error, {
                        tags: {
                            request_phase: 'response',
                            request_url: error.config?.url
                        },
                        extra: {
                            status: error.response?.status,
                            data: error.response?.data
                        }
                    });
                    return Promise.reject(error);
                }
            );
        }
    },
    
    // Track form performance
    trackFormMetrics(formElement, formName) {
        const startTime = new Date();
        let fieldInteractions = 0;
        
        // Track field interactions
        formElement.addEventListener('input', () => {
            fieldInteractions++;
        });
        
        // Track form submission
        formElement.addEventListener('submit', () => {
            const completionTime = new Date() - startTime;
            
            Sentry.captureMessage('Form Completion Metrics', {
                level: 'info',
                tags: {
                    form_name: formName,
                    metric_type: 'form_completion'
                },
                extra: {
                    completion_time_ms: completionTime,
                    field_interactions: fieldInteractions,
                    form_fields: formElement.querySelectorAll('input, select, textarea').length
                }
            });
        });
        
        // Track form abandonment (user leaves without submitting)
        let formSubmitted = false;
        formElement.addEventListener('submit', () => { formSubmitted = true; });
        
        window.addEventListener('beforeunload', () => {
            if (!formSubmitted && fieldInteractions > 0) {
                Sentry.captureMessage('Form Abandonment', {
                    level: 'warning',
                    tags: {
                        form_name: formName,
                        user_behavior: 'abandonment'
                    },
                    extra: {
                        time_spent_ms: new Date() - startTime,
                        field_interactions: fieldInteractions,
                        abandonment_point: 'page_unload'
                    }
                });
            }
        });
    }
};

// Business Directory specific tracking
export const BusinessDirectoryTracking = {
    // Track business listing interactions
    trackBusinessCardClick(businessId, businessName) {
        Sentry.addBreadcrumb({
            category: 'business.interaction',
            message: `Viewed business: ${businessName}`,
            data: { business_id: businessId, business_name: businessName }
        });
        
        Sentry.captureMessage('Business Listing Interaction', {
            level: 'info',
            tags: {
                feature: 'business_discovery',
                interaction_type: 'business_view'
            },
            extra: {
                business_id: businessId,
                business_name: businessName,
                page_type: getPageType()
            }
        });
    },
    
    // Track onboarding progress
    trackOnboardingProgress(step, stepData) {
        // Track as form progression for UX analytics
        Sentry.addBreadcrumb({
            category: 'form.progression',
            message: `Onboarding step ${step} completed`,
            data: {
                step: step,
                progress_percentage: (step / 4) * 100,
                fields_completed: Object.keys(stepData).length
            }
        });
        
        // Track as onboarding progress for business analytics
        Sentry.addBreadcrumb({
            category: 'onboarding.progress',
            message: `Business onboarding at step ${step}`,
            data: {
                step: step,
                progress_percentage: (step / 4) * 100,
                step_data: stepData
            }
        });
    },
    
    // Track search and filtering
    trackSearchInteraction(searchTerm, resultsCount) {
        Sentry.captureMessage('Business Directory Search', {
            level: 'info',
            tags: {
                feature: 'business_search',
                interaction_type: 'search'
            },
            extra: {
                search_term: searchTerm,
                results_count: resultsCount,
                search_timestamp: new Date().toISOString()
            }
        });
    }
};

// Initialize everything
export function initializeSentryFrontend() {
    console.log('Initializing Sentry frontend instrumentation...');
    
    // Initialize Alpine integration
    initializeAlpineIntegration();
    
    // Initialize performance tracking
    SentryPerformance.trackPageLoad();
    SentryPerformance.trackAjaxRequests();
    
    // Track page view
    Sentry.addBreadcrumb({
        category: 'navigation',
        message: `Page viewed: ${window.location.pathname}`,
        data: {
            url: window.location.href,
            page_type: getPageType(),
            referrer: document.referrer
        }
    });
    
    console.log('Sentry frontend instrumentation initialized');
} 