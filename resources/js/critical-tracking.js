/**
 * Critical Experience Tracking - Frontend
 * 
 * Focused tracking for business-critical user journeys only.
 * No noise, just signal.
 */

import * as Sentry from "@sentry/browser";

const CRITICAL_TRACKING_SESSION_KEY = 'critical_tracking_session';

function getSessionData() {
    try {
        return JSON.parse(sessionStorage.getItem(CRITICAL_TRACKING_SESSION_KEY) || '{}');
    } catch (e) {
        console.error("Failed to parse session data:", e);
        return {};
    }
}

function setSessionData(data) {
    try {
        sessionStorage.setItem(CRITICAL_TRACKING_SESSION_KEY, JSON.stringify(data));
    } catch (e) {
        console.error("Failed to set session data:", e);
    }
}

function clearSessionData() {
    sessionStorage.removeItem(CRITICAL_TRACKING_SESSION_KEY);
}

export const CriticalFrontendTracker = {
    // Discovery Path
    trackDiscoveryStart() {
        let session = getSessionData();
        if (!session.discovery_tracked) {
            Sentry.addBreadcrumb({
                category: 'critical.discovery',
                message: 'Critical: Discovery journey started',
                data: { checkpoint: 'start' },
                level: 'info'
            });
            session.discovery_tracked = true;
            setSessionData(session);
        }
    },

    trackBusinessViewed(businessId, businessName) {
        Sentry.addBreadcrumb({
            category: 'critical.discovery',
            message: `Critical: Business viewed: ${businessName}`,
            data: { checkpoint: 'business_view', business_id: businessId, business_name: businessName },
            level: 'info'
        });
    },

    trackBusinessContact(businessId, contactMethod) {
        Sentry.addBreadcrumb({
            category: 'critical.discovery',
            message: `Critical: Business contact initiated: ${businessId}`,
            data: { checkpoint: 'conversion', business_id: businessId, contact_method: contactMethod },
            level: 'info'
        });
    },

    // Onboarding Path
    trackOnboardingStart() {
        let session = getSessionData();
        if (!session.onboarding_start_time) {
            session.onboarding_start_time = Date.now();
            setSessionData(session);
            Sentry.addBreadcrumb({
                category: 'critical.onboarding',
                message: 'Critical: Onboarding started',
                data: { checkpoint: 'start' },
                level: 'info'
            });
        }
    },

    trackOnboardingStepComplete(step) {
        let session = getSessionData();
        const duration = session.onboarding_start_time ? (Date.now() - session.onboarding_start_time) / 1000 : null;
        Sentry.addBreadcrumb({
            category: 'critical.onboarding',
            message: `Critical: Onboarding step ${step} completed`,
            data: { checkpoint: `step_${step}_complete`, step: step, duration_seconds: duration },
            level: 'info'
        });
    },

    trackOnboardingAbandoned(lastStep) {
        let session = getSessionData();
        const duration = session.onboarding_start_time ? (Date.now() - session.onboarding_start_time) / 1000 : null;
        Sentry.addBreadcrumb({
            category: 'critical.onboarding',
            message: `Critical: Onboarding abandoned at step ${lastStep}`,
            data: { checkpoint: 'abandoned', last_step: lastStep, duration_seconds: duration },
            level: 'warning'
        });
        clearSessionData();
    },

    trackOnboardingComplete(businessId) {
        let session = getSessionData();
        const duration = session.onboarding_start_time ? (Date.now() - session.onboarding_start_time) / 1000 : null;
        Sentry.addBreadcrumb({
            category: 'critical.onboarding',
            message: `Critical: Onboarding completed for business ${businessId}`,
            data: { checkpoint: 'conversion', business_id: businessId, duration_seconds: duration },
            level: 'info'
        });
        clearSessionData();
    },

    // Critical Error Tracking
    trackCriticalError(experience, checkpoint, error) {
        Sentry.captureException(error, {
            tags: {
                critical_experience: experience,
                critical_checkpoint: checkpoint
            },
            extra: {
                error_message: error.message,
                error_type: error.name,
            }
        });
        Sentry.addBreadcrumb({
            category: 'critical.error',
            message: `Critical: Experience blocked by error at ${checkpoint} in ${experience}`,
            data: { experience, checkpoint, error_message: error.message },
            level: 'error'
        });
    }
};