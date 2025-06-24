@extends('layouts.app')

@section('content')
<div x-data="welcomePage" class="bg-white">
    <!-- Hero Section -->
    <div class="relative isolate px-6 pt-14 lg:px-8">
        <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
            <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
        </div>
        
        <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
            <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                <div class="relative rounded-full px-3 py-1 text-sm leading-6 text-gray-600 ring-1 ring-gray-900/10 hover:ring-gray-900/20">
                    Discover amazing local businesses. 
                    <a href="#features" class="font-semibold text-indigo-600">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Learn more <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
            
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                    Awesome Business Directory
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Connect with local businesses, discover new services, and grow your community. 
                    Whether you're a business owner or a customer, we've got you covered.
                </p>
                
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="/businesses" 
                       x-track='{"action": "browse_businesses", "source": "hero_cta", "position": "primary"}'
                       class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors duration-200">
                        Browse Businesses
                    </a>
                    <a href="{{ route('business.onboard.step', 1) }}" 
                       x-track='{"action": "add_business", "source": "hero_cta", "position": "secondary"}'
                       class="text-sm font-semibold leading-6 text-gray-900 hover:text-indigo-600 transition-colors duration-200">
                        Add Your Business <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
            <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-base font-semibold leading-7 text-indigo-600">Everything you need</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    Comprehensive business directory platform
                </p>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Built with modern technologies and comprehensive monitoring to ensure the best experience for both businesses and customers.
                </p>
            </div>
            
            <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                    <div class="relative pl-16">
                        <dt class="text-base font-semibold leading-7 text-gray-900">
                            <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                            </div>
                            Fast & Reliable
                        </dt>
                        <dd class="mt-2 text-base leading-7 text-gray-600">
                            Built with Laravel and optimized for performance. Real-time monitoring ensures everything runs smoothly.
                        </dd>
                    </div>
                    
                    <div class="relative pl-16">
                        <dt class="text-base font-semibold leading-7 text-gray-900">
                            <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </div>
                            Secure & Monitored
                        </dt>
                        <dd class="mt-2 text-base leading-7 text-gray-600">
                            Enterprise-grade security with comprehensive error tracking and performance monitoring via Sentry.
                        </dd>
                    </div>
                    
                    <div class="relative pl-16">
                        <dt class="text-base font-semibold leading-7 text-gray-900">
                            <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            User-Friendly
                        </dt>
                        <dd class="mt-2 text-base leading-7 text-gray-600">
                            Intuitive interface powered by Alpine.js for smooth interactions and seamless user experience.
                        </dd>
                    </div>
                    
                    <div class="relative pl-16">
                        <dt class="text-base font-semibold leading-7 text-gray-900">
                            <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </div>
                            Analytics & Insights
                        </dt>
                        <dd class="mt-2 text-base leading-7 text-gray-600">
                            Comprehensive analytics and business insights to help you understand user behavior and optimize performance.
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Demo Section -->
    <div class="bg-gray-50 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-base font-semibold leading-7 text-indigo-600">Interactive Demo</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    See it in action
                </p>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Experience our platform's features with this interactive demo. All interactions are tracked and monitored.
                </p>
            </div>
            
            <!-- Interactive Demo Component -->
            <div x-data="{ 
                demoStep: 1, 
                businessName: '', 
                searchTerm: '',
                showSuccess: false,
                
                nextStep() {
                    this.demoStep++;
                    if (this.demoStep > 3) {
                        this.showSuccess = true;
                        setTimeout(() => {
                            this.resetDemo();
                        }, 3000);
                    }
                },
                
                resetDemo() {
                    this.demoStep = 1;
                    this.businessName = '';
                    this.searchTerm = '';
                    this.showSuccess = false;
                }
            }" class="mt-16">
                
                <div class="mx-auto max-w-2xl">
                    <!-- Demo Progress -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                            <span>Demo Progress</span>
                            <span x-text="`${demoStep}/3`"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" 
                                 :style="`width: ${(demoStep / 3) * 100}%`"></div>
                        </div>
                    </div>
                    
                    <!-- Demo Steps -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <!-- Step 1: Business Search -->
                        <div x-show="demoStep === 1" x-transition>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Step 1: Search for Businesses
                            </h3>
                            <div class="space-y-4">
                                <input type="text" 
                                       x-model="searchTerm"
                                       x-track='{"action": "demo_search", "step": 1}'
                                       placeholder="Try searching for 'restaurant' or 'coffee'"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <button @click="nextStep()" 
                                        x-track='{"action": "demo_next_step", "from_step": 1}'
                                        :disabled="!searchTerm"
                                        :class="searchTerm ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                                        class="w-full px-4 py-2 text-white rounded-md transition-colors">
                                    Continue to Step 2
                                </button>
                            </div>
                        </div>
                        
                        <!-- Step 2: Add Business -->
                        <div x-show="demoStep === 2" x-transition>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Step 2: Add Your Business
                            </h3>
                            <div class="space-y-4">
                                <input type="text" 
                                       x-model="businessName"
                                       x-track='{"action": "demo_business_name", "step": 2}'
                                       placeholder="Enter your business name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <select x-track='{"action": "demo_industry_select", "step": 2}'
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Select Industry</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="retail">Retail</option>
                                    <option value="services">Services</option>
                                    <option value="technology">Technology</option>
                                </select>
                                <button @click="nextStep()" 
                                        x-track='{"action": "demo_next_step", "from_step": 2}'
                                        :disabled="!businessName"
                                        :class="businessName ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                                        class="w-full px-4 py-2 text-white rounded-md transition-colors">
                                    Continue to Step 3
                                </button>
                            </div>
                        </div>
                        
                        <!-- Step 3: Contact Information -->
                        <div x-show="demoStep === 3" x-transition>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Step 3: Contact Information
                            </h3>
                            <div class="space-y-4">
                                <input type="email" 
                                       x-track='{"action": "demo_email_input", "step": 3}'
                                       placeholder="business@example.com"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <input type="tel" 
                                       x-track='{"action": "demo_phone_input", "step": 3}'
                                       placeholder="(555) 123-4567"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <button @click="nextStep()" 
                                        x-track='{"action": "demo_complete", "business_name": businessName}'
                                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors">
                                    Complete Demo
                                </button>
                            </div>
                        </div>
                        
                        <!-- Success Message -->
                        <div x-show="showSuccess" x-transition>
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Demo Completed!</h3>
                                <p class="text-gray-600 mb-4">
                                    Great job! All your interactions have been tracked and analyzed.
                                </p>
                                <p class="text-sm text-gray-500">
                                    Resetting demo in 3 seconds...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-indigo-600">
        <div class="px-6 py-24 sm:px-6 sm:py-32 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Ready to get started?
                </h2>
                <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-indigo-200">
                    Join our growing community of businesses and customers. Start your experience today.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="{{ route('business.onboard.step', 1) }}" 
                       x-track='{"action": "add_business", "source": "bottom_cta", "position": "primary"}'
                       class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white transition-colors duration-200">
                        Add Your Business
                    </a>
                    <a href="/businesses" 
                       x-track='{"action": "browse_businesses", "source": "bottom_cta", "position": "secondary"}'
                       class="text-sm font-semibold leading-6 text-white hover:text-indigo-200 transition-colors duration-200">
                        Browse Directory <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 