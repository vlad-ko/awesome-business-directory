import './bootstrap';
import './sentry';
import { initializeSentryFrontend, SentryPerformance, BusinessDirectoryTracking } from './sentry';
import Alpine from 'alpinejs';

// Make Alpine available globally for Sentry integration
window.Alpine = Alpine;

// Initialize Sentry frontend tracking
document.addEventListener('DOMContentLoaded', () => {
    initializeSentryFrontend();
});

// Welcome Page Component
Alpine.data('welcomePage', () => ({
    demoStep: 1,
    progressPercent: 0,
    
    init() {
        try {
            BusinessDirectoryTracking.trackPageView('welcome');
            this.$watch('demoStep', (step) => {
                this.progressPercent = (step / 3) * 100;
                BusinessDirectoryTracking.trackFormProgression('demo_step', step, {
                    progress_percent: this.progressPercent
                });
            });
        } catch (error) {
            console.error('Welcome page initialization error:', error);
        }
    },
    
    trackCTA(action) {
        try {
            BusinessDirectoryTracking.trackUserInteraction(action, {
                source: 'welcome_page',
                position: 'cta'
            });
        } catch (error) {
            console.error('CTA tracking error:', error);
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
        
        // Track component initialization
        BusinessDirectoryTracking.trackSearchInteraction('component_initialized', this.searchTerm);
    },
    
    async loadBusinesses() {
        this.isLoading = true;
        try {
            const response = await fetch('/api/businesses');
            this.businesses = await response.json();
            this.filteredBusinesses = [...this.businesses];
        } catch (error) {
            console.error('Failed to load businesses:', error);
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
        
        // Track search interaction
        BusinessDirectoryTracking.trackSearchInteraction(this.searchTerm, this.filteredBusinesses.length);
    },
    
    viewBusiness(businessId, businessName) {
        BusinessDirectoryTracking.trackBusinessCardClick(businessId, businessName);
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
            
            BusinessDirectoryTracking.trackFormProgression('onboarding_started', this.currentStep);
        } catch (error) {
            console.error('Onboarding form initialization error:', error);
            this.errors = { general: 'Failed to initialize form' };
        }
    },

    nextStep() {
        try {
            if (this.validateCurrentStep()) {
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                    BusinessDirectoryTracking.trackFormProgression('step_completed', this.currentStep - 1);
                    BusinessDirectoryTracking.trackOnboardingProgress(this.currentStep - 1, this.getStepData(this.currentStep - 1));
                }
            }
        } catch (error) {
            console.error('Next step error:', error);
            this.errors = { general: 'Failed to proceed to next step' };
        }
    },

    prevStep() {
        try {
            if (this.currentStep > 1) {
                this.currentStep--;
                BusinessDirectoryTracking.trackFormProgression('step_back', this.currentStep);
            }
        } catch (error) {
            console.error('Previous step error:', error);
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
            console.error('Validation error:', error);
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

    trackCTA(action) {
        try {
            BusinessDirectoryTracking.trackUserInteraction(action, {
                source: 'welcome_page',
                position: 'cta'
            });
        } catch (error) {
            console.error('CTA tracking error:', error);
        }
    },

    submitForm() {
        try {
            if (this.validateCurrentStep()) {
                BusinessDirectoryTracking.trackFormProgression('form_submitted', this.totalSteps);
                // Form submission logic here
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.errors = { general: 'Failed to submit form' };
        }
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
        BusinessDirectoryTracking.trackPageView('admin_dashboard');
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
            BusinessDirectoryTracking.trackAdminAction('business_approved', businessId);
        } catch (error) {
            console.error('Failed to approve business:', error);
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
            BusinessDirectoryTracking.trackAdminAction('business_rejected', businessId);
        } catch (error) {
            console.error('Failed to reject business:', error);
        }
    }
}));

// Alpine.js tracking directive
Alpine.directive('track', (el, { expression }, { evaluate }) => {
    const trackingData = evaluate(expression);
    
    el.addEventListener('click', () => {
        if (trackingData.action) {
            BusinessDirectoryTracking.trackUserInteraction(trackingData.action, trackingData);
        }
    });
});

// Alpine.js form tracking directive
Alpine.directive('track-form', (el, { expression }, { evaluate }) => {
    const formName = evaluate(expression);
    
    SentryPerformance.trackFormMetrics(el, formName);
});

// Alpine.js change tracking directive
Alpine.directive('track-change', (el, { expression }, { evaluate }) => {
    const eventName = evaluate(expression);
    
    el.addEventListener('change', (event) => {
        BusinessDirectoryTracking.trackFieldInteraction(eventName, event.target.value);
    });
});

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

// Start Alpine.js
Alpine.start();

// Make components available globally for debugging
window.BusinessDirectoryTracking = BusinessDirectoryTracking;
window.SentryPerformance = SentryPerformance;

// Initialize form tracking on page load
document.addEventListener('DOMContentLoaded', function() {
    // Track forms with comprehensive metrics
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const formName = form.getAttribute('data-form') || form.id || 'unknown';
        SentryPerformance.trackFormMetrics(form, formName);
    });
});

// Enhanced error handling
window.addEventListener('error', (event) => {
    try {
        console.error('Global error:', event.error);
    } catch (error) {
        console.warn('Error in global error handler:', error);
    }
});

// Initialize comprehensive tracking
console.log('Alpine.js and Sentry integration initialized');
