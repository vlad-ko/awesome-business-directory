/**
 * Critical Experience Tracking - Frontend
 * 
 * Focused tracking for business-critical user journeys only.
 * No noise, just signal.
 */

import { Sentry, logger } from './sentry';

export const CriticalTracking = {
    /**
     * Track business discovery journey checkpoints
     */
    discovery: {
        // Track when user shows intent to browse businesses
        browseStarted() {
            // Only track once per session
            if (sessionStorage.getItem('discovery_started')) return;
            sessionStorage.setItem('discovery_started', 'true');
            
            logger.info('Critical: User started browsing businesses');
        },
        
        // Track when user clicks to view a specific business
        businessClicked(businessId, businessName) {
            Sentry.startSpan({
                op: 'navigation',
                name: 'Business View Navigation',
            }, (span) => {
                span.setAttribute('business_id', businessId);
                span.setAttribute('critical_path', 'discovery');
                
                logger.info(logger.fmt`Critical: Business clicked - ${businessName}`, {
                    businessId,
                    checkpoint: 'business_view'
                });
            });
        },
        
        // Track conversion: user contacts business
        contactInitiated(businessId, method) {
            logger.info('Critical: Business contact conversion', {
                businessId,
                method, // 'website', 'phone', 'email'
                checkpoint: 'conversion'
            });
        }
    },
    
    /**
     * Track onboarding journey checkpoints
     */
    onboarding: {
        // Track onboarding start
        started() {
            sessionStorage.setItem('onboarding_start', Date.now());
            logger.info('Critical: Onboarding journey started');
        },
        
        // Track step completion (not individual fields)
        stepCompleted(step) {
            const startTime = sessionStorage.getItem('onboarding_start');
            const duration = startTime ? (Date.now() - startTime) / 1000 : null;
            
            logger.info(logger.fmt`Critical: Onboarding step ${step} completed`, {
                step,
                duration_seconds: duration,
                checkpoint: `step_${step}_complete`
            });
        },
        
        // Track final submission
        submitted() {
            const startTime = sessionStorage.getItem('onboarding_start');
            const duration = startTime ? (Date.now() - startTime) / 1000 : null;
            
            logger.info('Critical: Onboarding submitted', {
                duration_seconds: duration,
                checkpoint: 'submission'
            });
        },
        
        // Track abandonment
        abandoned(lastStep) {
            const startTime = sessionStorage.getItem('onboarding_start');
            const duration = startTime ? (Date.now() - startTime) / 1000 : null;
            
            logger.warn('Critical: Onboarding abandoned', {
                last_step: lastStep,
                duration_seconds: duration,
                checkpoint: 'abandoned'
            });
            
            sessionStorage.removeItem('onboarding_start');
        }
    },
    
    /**
     * Track critical errors that block user progress
     */
    error(experience, checkpoint, error) {
        logger.error(logger.fmt`Critical: ${experience} blocked at ${checkpoint}`, {
            experience,
            checkpoint,
            error_message: error.message,
            error_stack: error.stack
        });
        
        Sentry.captureException(error, {
            tags: {
                critical_experience: experience,
                checkpoint
            }
        });
    }
};

// Attach to window for Alpine.js access
window.CriticalTracking = CriticalTracking;
