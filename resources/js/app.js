import './bootstrap';
import './sentry';
import { CriticalFrontendTracker } from './critical-tracking';
import Alpine from 'alpinejs';

// Make Alpine available globally for Sentry integration
window.Alpine = Alpine;

// Welcome Page Component
Alpine.data('welcomePage', () => ({
    demoStep: 1,
    progressPercent: 0,
    
    init() {
        // No tracking needed for welcome page - not a critical path
        this.$watch('demoStep', (step) => {
            this.progressPercent = (step / 3) * 100;
        });
    },
    
    trackCTA(action) {
        // Only track if it leads to a critical path
        if (action === 'browse_businesses') {
            CriticalFrontendTracker.trackDiscoveryStart();
        }
    }
}));

// Business Directory Component
Alpine.data('businessDirectory', () => ({
    searchTerm: '',
    selectedIndustry: '',
    filteredBusinesses: [],
    businesses: [],
    isLoading: false,
    
    init() {
        this.loadBusinesses();
        this.$watch('searchTerm', () => this.filterBusinesses());
        this.$watch('selectedIndustry', () => this.filterBusinesses());
    },
    
    async loadBusinesses() {
        this.isLoading = true;
        try {
            const response = await fetch('/api/businesses');
            this.businesses = await response.json();
            this.filteredBusinesses = [...this.businesses];
        } catch (error) {
            // Only track if it blocks the critical path
            CriticalFrontendTracker.trackCriticalError(
                'business_discovery',
                'listing_load_failed',
                error
            );
        } finally {
            this.isLoading = false;
        }
    },
    
    filterBusinesses() {
        this.filteredBusinesses = this.businesses.filter(business => {
            const matchesSearch = !this.searchTerm || 
                business.business_name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                business.description.toLowerCase().includes(this.searchTerm.toLowerCase());
            
            const matchesIndustry = !this.selectedIndustry || 
                business.industry === this.selectedIndustry;
            
            return matchesSearch && matchesIndustry;
        });
    },
    
    viewBusiness(businessId, businessName) {
        // Track critical conversion point
        CriticalFrontendTracker.trackBusinessViewed(businessId, businessName);
        window.location.href = `/businesses/${businessId}`;
    }
}));

// Business Onboarding Form Component
Alpine.data('onboardingForm', () => ({
    currentStep: 1,
    totalSteps: 4,
    errors: {},
    step1: {
        business_name: '',
        industry: '',
        description: ''
    },
    step2: {
        email: '',
        phone: '',
        website: ''
    },
    step3: {
        address: '',
        city: '',
        hours: ''
    },
    step4: {
        services: '',
        accepts_appointments: false,
        verified: false
    },
    
    init() {
        try {
            // Initialize required fields configuration
            const requiredFields = {
                1: ['business_name', 'industry', 'description'],
                2: ['email', 'phone'],
                3: ['address', 'city'],
                4: ['services']
            };
            this.requiredFields = requiredFields;
            
            // Track critical onboarding start
            CriticalFrontendTracker.trackOnboardingStart();
        } catch (error) {
            this.errors = { general: 'Failed to initialize form' };
        }
    },

    nextStep() {
        try {
            if (this.validateCurrentStep()) {
                if (this.currentStep < this.totalSteps) {
                    // Track critical step completion
                    CriticalFrontendTracker.trackOnboardingStepComplete(this.currentStep);
                    this.currentStep++;
                }
            }
        } catch (error) {
            CriticalFrontendTracker.trackCriticalError(
                'business_onboarding',
                'step_progression_failed',
                error
            );
            this.errors = { general: 'Failed to proceed to next step' };
        }
    },

    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
        }
    },

    validateCurrentStep() {
        try {
            this.errors = {};
            const stepData = this[`step${this.currentStep}`];
            const required = this.requiredFields[this.currentStep];

            let isValid = true;
            required.forEach(field => {
                if (!this.isRequired(field, stepData[field])) {
                    this.errors[field] = `${this.getFieldLabel(field)} is required`;
                    isValid = false;
                }
            });

            return isValid;
        } catch (error) {
            this.errors = { general: 'Validation failed' };
            return false;
        }
    },

    isRequired(field, value) {
        return value && value.toString().trim().length > 0;
    },

    getFieldLabel(field) {
        const labels = {
            business_name: 'Business Name',
            industry: 'Industry',
            description: 'Description',
            email: 'Email',
            phone: 'Phone',
            website: 'Website',
            address: 'Address',
            city: 'City',
            hours: 'Hours',
            services: 'Services'
        };
        return labels[field] || field;
    },

    getStepData(step) {
        return this[`step${step}`] || {};
    },

    submitForm() {
        try {
            if (this.validateCurrentStep()) {
                // Track critical onboarding completion
                // Note: Business ID would come from server response
                CriticalFrontendTracker.trackOnboardingComplete('pending');
                // Form submission logic here
            }
        } catch (error) {
            CriticalFrontendTracker.trackCriticalError(
                'business_onboarding',
                'submission_failed',
                error
            );
            this.errors = { general: 'Failed to submit form' };
        }
    },

    abandonForm() {
        // Track critical abandonment
        CriticalFrontendTracker.trackOnboardingAbandoned(this.currentStep);
    }
}));

// Admin Dashboard Component
Alpine.data('adminDashboard', () => ({
    businesses: [],
    stats: {
        total: 0,
        pending: 0,
        approved: 0
    },
    selectedBusiness: null,
    isLoading: false,
    
    init() {
        this.loadDashboardData();
    },
    
    async loadDashboardData() {
        this.isLoading = true;
        try {
            const [businessesResponse, statsResponse] = await Promise.all([
                fetch('/api/admin/businesses'),
                fetch('/api/admin/stats')
            ]);
            
            this.businesses = await businessesResponse.json();
            this.stats = await statsResponse.json();
        } catch (error) {
            // Only track if it blocks critical admin actions
            console.error('Failed to load dashboard data:', error);
        } finally {
            this.isLoading = false;
        }
    },
    
    async approveBusiness(businessId) {
        try {
            await fetch(`/api/admin/businesses/${businessId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            await this.loadDashboardData();
            // Critical admin actions are tracked on backend
        } catch (error) {
            CriticalFrontendTracker.trackCriticalError(
                'admin_operations',
                'approval_failed',
                error
            );
        }
    },
    
    async rejectBusiness(businessId, reason) {
        try {
            await fetch(`/api/admin/businesses/${businessId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ reason })
            });
            
            await this.loadDashboardData();
            // Critical admin actions are tracked on backend
        } catch (error) {
            CriticalFrontendTracker.trackCriticalError(
                'admin_operations',
                'rejection_failed',
                error
            );
        }
    }
}));

// Business Detail Component for contact tracking
Alpine.data('businessDetail', () => ({
    business: null,
    
    init() {
        // Business data would be passed from server
        this.business = window.businessData || null;
    },
    
    contactBusiness(method) {
        if (this.business) {
            // Track critical conversion
            CriticalFrontendTracker.trackBusinessContact(this.business.id, method);
            
            // Handle the actual contact action
            switch(method) {
                case 'website':
                    window.open(this.business.website_url, '_blank');
                    break;
                case 'phone':
                    window.location.href = `tel:${this.business.phone_number}`;
                    break;
                case 'email':
                    window.location.href = `mailto:${this.business.primary_email}`;
                    break;
            }
        }
    }
}));

// Global error handler for Alpine.js
window.addEventListener('alpine:init', () => {
    Alpine.store('errors', {
        items: {},
        
        add(field, message) {
            this.items[field] = message;
        },
        
        remove(field) {
            delete this.items[field];
        },
        
        clear() {
            this.items = {};
        },
        
        has(field) {
            return this.items.hasOwnProperty(field);
        },
        
        get(field) {
            return this.items[field];
        }
    });
});

// Handle page unload for onboarding abandonment
window.addEventListener('beforeunload', (event) => {
    // Check if we're on an onboarding page and haven't completed
    const onboardingForm = document.querySelector('[x-data*="onboardingForm"]');
    if (onboardingForm && window.Alpine) {
        const component = Alpine.$data(onboardingForm);
        if (component && component.currentStep < component.totalSteps) {
            component.abandonForm();
        }
    }
});

// Start Alpine.js
Alpine.start();

// Make critical tracker available for debugging
window.CriticalFrontendTracker = CriticalFrontendTracker;

console.log('Alpine.js with Critical Experience tracking initialized');