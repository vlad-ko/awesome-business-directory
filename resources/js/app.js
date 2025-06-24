import './bootstrap';
import './sentry';
import { initializeSentryFrontend, SentryPerformance, BusinessDirectoryTracking } from './sentry';
import Alpine from 'alpinejs';

// Make Alpine available globally for Sentry integration
window.Alpine = Alpine;

// Initialize Sentry frontend tracking
initializeSentryFrontend();

// Simplified Business Directory Component (search moved to server-side)
Alpine.data('businessDirectory', () => ({
    init() {
        console.log('Business Directory component initialized');
    },
    
    viewBusiness(businessId, businessName) {
        // Track business card interactions
        if (typeof BusinessDirectoryTracking !== 'undefined') {
            BusinessDirectoryTracking.trackBusinessCardClick(businessId, businessName);
        }
    }
}));

// Multi-Step Onboarding Form Component
Alpine.data('onboardingForm', () => ({
    currentStep: 1,
    totalSteps: 4,
    isSubmitting: false,
    errors: {},
    
    // Form data for each step
    step1: {
        business_name: '',
        industry: '',
        business_type: '',
        description: '',
        tagline: ''
    },
    
    step2: {
        primary_email: '',
        phone_number: '',
        website_url: ''
    },
    
    step3: {
        street_address: '',
        city: '',
        state_province: '',
        postal_code: '',
        country: 'United States'
    },
    
    step4: {
        owner_name: '',
        owner_email: ''
    },
    
    init() {
        console.log('Onboarding form component initialized');
        
        // Track form initialization
        BusinessDirectoryTracking.trackOnboardingProgress(0, {});
        
        // Initialize form performance tracking
        const formElement = this.$el.querySelector('form');
        if (formElement) {
            SentryPerformance.trackFormMetrics(formElement, 'business_onboarding');
        }
    },
    
    get progressPercentage() {
        return (this.currentStep / this.totalSteps) * 100;
    },
    
    get currentStepData() {
        return this[`step${this.currentStep}`];
    },
    
    nextStep() {
        if (this.validateCurrentStep()) {
            // Track step completion
            BusinessDirectoryTracking.trackOnboardingProgress(
                this.currentStep, 
                this.currentStepData
            );
            
            this.currentStep++;
            this.scrollToTop();
        }
    },
    
    previousStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.scrollToTop();
        }
    },
    
    validateCurrentStep() {
        this.errors = {};
        const stepData = this.currentStepData;
        
        // Basic validation - in real app this would be more comprehensive
        for (const [key, value] of Object.entries(stepData)) {
            if (!value && this.isRequired(key)) {
                this.errors[key] = `${this.getFieldLabel(key)} is required`;
            }
        }
        
        return Object.keys(this.errors).length === 0;
    },
    
    isRequired(field) {
        const requiredFields = {
            1: ['business_name', 'industry', 'business_type', 'description'],
            2: ['primary_email', 'phone_number'],
            3: ['street_address', 'city', 'state_province', 'postal_code'],
            4: ['owner_name', 'owner_email']
        };
        
        return requiredFields[this.currentStep]?.includes(field) || false;
    },
    
    getFieldLabel(field) {
        const labels = {
            business_name: 'Business Name',
            industry: 'Industry',
            business_type: 'Business Type',
            description: 'Description',
            primary_email: 'Email',
            phone_number: 'Phone Number',
            street_address: 'Street Address',
            city: 'City',
            state_province: 'State/Province',
            postal_code: 'Postal Code',
            owner_name: 'Owner Name',
            owner_email: 'Owner Email'
        };
        
        return labels[field] || field;
    },
    
    async submitForm() {
        if (!this.validateCurrentStep()) return;
        
        this.isSubmitting = true;
        
        try {
            const formData = {
                ...this.step1,
                ...this.step2,
                ...this.step3,
                ...this.step4
            };
            
            // Track form submission attempt
            BusinessDirectoryTracking.trackOnboardingProgress(5, formData);
            
            // Submit to server
            const response = await window.axios.post('/onboard/submit', formData);
            
            if (response.status === 200) {
                // Redirect to success page
                window.location.href = '/onboard/success';
            }
        } catch (error) {
            console.error('Form submission failed:', error);
            
            if (error.response?.data?.errors) {
                this.errors = error.response.data.errors;
            } else {
                this.errors = { general: 'An error occurred. Please try again.' };
            }
        } finally {
            this.isSubmitting = false;
        }
    },
    
    scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}));

// Welcome Page Interaction Tracking
Alpine.data('welcomePage', () => ({
    init() {
        console.log('Welcome page component initialized');
    },
    
    trackCTA(action) {
        // This will be tracked by the x-track directive
        console.log(`CTA clicked: ${action}`);
    }
}));

// Admin Dashboard Component
Alpine.data('adminDashboard', () => ({
    pendingBusinesses: [],
    stats: {
        pending: 0,
        approved: 0,
        total: 0
    },
    isLoading: true,
    
    async init() {
        console.log('Admin dashboard component initialized');
        await this.loadDashboardData();
    },
    
    async loadDashboardData() {
        try {
            // In a real app, this would fetch from the server
            // For now, we'll simulate the data
            this.isLoading = false;
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
            this.isLoading = false;
        }
    },
    
    async approveBusiness(businessId) {
        try {
            await window.axios.patch(`/admin/businesses/${businessId}/approve`);
            // Reload dashboard data
            await this.loadDashboardData();
        } catch (error) {
            console.error('Failed to approve business:', error);
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

// Start Alpine.js
Alpine.start();

// Make components available globally for debugging
window.BusinessDirectoryTracking = BusinessDirectoryTracking;
window.SentryPerformance = SentryPerformance;
