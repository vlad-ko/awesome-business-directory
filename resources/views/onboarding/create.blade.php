@extends('layouts.app')

@section('content')
<div x-data="onboardingForm" class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-6 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Add Your Business</h1>
                <p class="text-lg text-gray-600">Join our growing community of local businesses</p>
            </div>

            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                    <span>Step <span x-text="currentStep"></span> of <span x-text="totalSteps"></span></span>
                    <span x-text="`${progressPercentage}% Complete`"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" 
                         :style="`width: ${progressPercentage}%`"></div>
                </div>
            </div>

            <!-- Form Container -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <form @submit.prevent="currentStep === totalSteps ? submitForm() : nextStep()">
                    <!-- Step 1: Business Information -->
                    <div x-show="currentStep === 1" x-transition>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Business Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Name *
                                </label>
                                <input type="text" 
                                       id="business_name"
                                       x-model="step1.business_name"
                                       :class="errors.business_name ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                       placeholder="Enter your business name">
                                <p x-show="errors.business_name" x-text="errors.business_name" class="mt-1 text-sm text-red-600"></p>
                            </div>
                            
                            <div>
                                <label for="industry" class="block text-sm font-medium text-gray-700 mb-2">
                                    Industry *
                                </label>
                                <select id="industry"
                                        x-model="step1.industry"
                                        :class="errors.industry ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2">
                                    <option value="">Select an industry</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="retail">Retail</option>
                                    <option value="services">Services</option>
                                    <option value="technology">Technology</option>
                                    <option value="healthcare">Healthcare</option>
                                    <option value="education">Education</option>
                                </select>
                                <p x-show="errors.industry" x-text="errors.industry" class="mt-1 text-sm text-red-600"></p>
                            </div>
                            
                            <div>
                                <label for="business_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Type *
                                </label>
                                <select id="business_type"
                                        x-model="step1.business_type"
                                        :class="errors.business_type ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2">
                                    <option value="">Select business type</option>
                                    <option value="LLC">LLC</option>
                                    <option value="Corporation">Corporation</option>
                                    <option value="Sole Proprietorship">Sole Proprietorship</option>
                                    <option value="Partnership">Partnership</option>
                                </select>
                                <p x-show="errors.business_type" x-text="errors.business_type" class="mt-1 text-sm text-red-600"></p>
                            </div>
                            
                            <div>
                                <label for="tagline" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tagline
                                </label>
                                <input type="text" 
                                       id="tagline"
                                       x-model="step1.tagline"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Your business tagline">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description *
                            </label>
                            <textarea id="description"
                                      x-model="step1.description"
                                      :class="errors.description ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                      class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                      rows="4"
                                      placeholder="Describe your business and what makes it special"></textarea>
                            <p x-show="errors.description" x-text="errors.description" class="mt-1 text-sm text-red-600"></p>
                        </div>
                    </div>

                    <!-- Step 2: Contact Information -->
                    <div x-show="currentStep === 2" x-transition>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Contact Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="primary_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Primary Email *
                                </label>
                                <input type="email" 
                                       id="primary_email"
                                       x-model="step2.primary_email"
                                       :class="errors.primary_email ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                       placeholder="business@example.com">
                                <p x-show="errors.primary_email" x-text="errors.primary_email" class="mt-1 text-sm text-red-600"></p>
                            </div>
                            
                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number *
                                </label>
                                <input type="tel" 
                                       id="phone_number"
                                       x-model="step2.phone_number"
                                       :class="errors.phone_number ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                       placeholder="(555) 123-4567">
                                <p x-show="errors.phone_number" x-text="errors.phone_number" class="mt-1 text-sm text-red-600"></p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="website_url" class="block text-sm font-medium text-gray-700 mb-2">
                                    Website URL
                                </label>
                                <input type="url" 
                                       id="website_url"
                                       x-model="step2.website_url"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="https://www.yourbusiness.com">
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Address Information -->
                    <div x-show="currentStep === 3" x-transition>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Business Address</h2>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="street_address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Street Address *
                                </label>
                                <input type="text" 
                                       id="street_address"
                                       x-model="step3.street_address"
                                       :class="errors.street_address ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                       placeholder="123 Main Street">
                                <p x-show="errors.street_address" x-text="errors.street_address" class="mt-1 text-sm text-red-600"></p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                        City *
                                    </label>
                                    <input type="text" 
                                           id="city"
                                           x-model="step3.city"
                                           :class="errors.city ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                           placeholder="City">
                                    <p x-show="errors.city" x-text="errors.city" class="mt-1 text-sm text-red-600"></p>
                                </div>
                                
                                <div>
                                    <label for="state_province" class="block text-sm font-medium text-gray-700 mb-2">
                                        State/Province *
                                    </label>
                                    <input type="text" 
                                           id="state_province"
                                           x-model="step3.state_province"
                                           :class="errors.state_province ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                           placeholder="State/Province">
                                    <p x-show="errors.state_province" x-text="errors.state_province" class="mt-1 text-sm text-red-600"></p>
                                </div>
                                
                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                                        Postal Code *
                                    </label>
                                    <input type="text" 
                                           id="postal_code"
                                           x-model="step3.postal_code"
                                           :class="errors.postal_code ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                           placeholder="12345">
                                    <p x-show="errors.postal_code" x-text="errors.postal_code" class="mt-1 text-sm text-red-600"></p>
                                </div>
                            </div>
                            
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                    Country
                                </label>
                                <input type="text" 
                                       id="country"
                                       x-model="step3.country"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Country">
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Owner Information -->
                    <div x-show="currentStep === 4" x-transition>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Owner Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Owner Name *
                                </label>
                                <input type="text" 
                                       id="owner_name"
                                       x-model="step4.owner_name"
                                       :class="errors.owner_name ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                       placeholder="Full name">
                                <p x-show="errors.owner_name" x-text="errors.owner_name" class="mt-1 text-sm text-red-600"></p>
                            </div>
                            
                            <div>
                                <label for="owner_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Owner Email *
                                </label>
                                <input type="email" 
                                       id="owner_email"
                                       x-model="step4.owner_email"
                                       :class="errors.owner_email ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2"
                                       placeholder="owner@example.com">
                                <p x-show="errors.owner_email" x-text="errors.owner_email" class="mt-1 text-sm text-red-600"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                        <button type="button" 
                                @click="previousStep()"
                                x-show="currentStep > 1"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Previous
                        </button>
                        
                        <div x-show="currentStep === 1" class="w-full"></div>
                        
                        <button type="submit"
                                :disabled="isSubmitting"
                                :class="isSubmitting ? 'opacity-50 cursor-not-allowed' : ''"
                                class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span x-show="!isSubmitting">
                                <span x-show="currentStep < totalSteps">Next Step</span>
                                <span x-show="currentStep === totalSteps">Submit Application</span>
                            </span>
                            <span x-show="isSubmitting">Submitting...</span>
                        </button>
                    </div>

                    <!-- General Error Message -->
                    <div x-show="errors.general" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
                        <p x-text="errors.general" class="text-sm text-red-600"></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
