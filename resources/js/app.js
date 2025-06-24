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
    
    init() {
        BusinessDirectoryTracking.trackPageView('welcome');
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
Alpine.data('businessOnboardingForm', () => ({
    currentStep: 1,
    formData: {},
    isSubmitting: false,
    validationErrors: {},
    
    init() {
        BusinessDirectoryTracking.trackFormProgression('onboarding_started', this.currentStep);
    },
    
    async submitStep(stepData) {
        this.isSubmitting = true;
        this.validationErrors = {};
        
        try {
            const response = await fetch(`/onboard/step/${this.currentStep}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(stepData)
            });
            
            if (response.ok) {
                this.formData = { ...this.formData, ...stepData };
                this.currentStep++;
                BusinessDirectoryTracking.trackFormProgression('step_completed', this.currentStep - 1);
            } else {
                const errors = await response.json();
                this.validationErrors = errors.errors || {};
            }
        } catch (error) {
            console.error('Step submission failed:', error);
        } finally {
            this.isSubmitting = false;
        }
    }
}));

// Admin Dashboard Component
Alpine.data('adminDashboard', () => ({
    businesses: [],
    stats: {},
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
