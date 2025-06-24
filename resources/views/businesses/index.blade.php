@extends('layouts.app')

@section('content')
<div x-data="businessDirectory" class="container mx-auto px-6 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Business Directory</h1>
            <p class="text-lg text-gray-600">Discover amazing local businesses in your area</p>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Businesses</label>
                    <input type="text" 
                           id="search"
                           x-model="searchTerm"
                           @input="search()"
                           placeholder="Search by name or description..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label for="industry" class="block text-sm font-medium text-gray-700 mb-2">Filter by Industry</label>
                    <select id="industry"
                            x-model="selectedIndustry"
                            @change="filterBusinesses()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Industries</option>
                        <option value="restaurant">Restaurant</option>
                        <option value="retail">Retail</option>
                        <option value="services">Services</option>
                        <option value="technology">Technology</option>
                        <option value="healthcare">Healthcare</option>
                        <option value="education">Education</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="isLoading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-600">Loading businesses...</p>
        </div>

        <!-- Results Count -->
        <div x-show="!isLoading" class="mb-6">
            <p class="text-gray-600">
                Showing <span x-text="filteredBusinesses.length" class="font-semibold"></span> 
                of <span x-text="businesses.length" class="font-semibold"></span> businesses
            </p>
        </div>

        <!-- Business Grid -->
        <div x-show="!isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Sample Business Cards (in real app, these would be dynamic) -->
            @forelse($businesses ?? [] as $business)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                                                         <h3 class="text-xl font-semibold text-gray-900">{{ $business->business_name }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ ucfirst($business->industry) }}
                            </span>
                        </div>
                        
                        <p class="text-gray-600 mb-4 line-clamp-3">{{ $business->description }}</p>
                        
                        <div class="space-y-2 mb-4">
                            @if($business->primary_email)
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ $business->primary_email }}
                                </div>
                            @endif
                            
                            @if($business->phone_number)
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    {{ $business->phone_number }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-between items-center">
                                                         <a href="{{ route('business.show', $business) }}" 
                                @click="viewBusiness({{ $business->id }}, '{{ $business->business_name }}')"
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                View Details
                                <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            
                            @if($business->website_url)
                                <a href="{{ $business->website_url }}" 
                                   target="_blank"
                                   class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No businesses found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Be the first to add your business to our directory.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('business.onboard.step', 1) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Add Your Business
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- No Results for Search -->
        <div x-show="!isLoading && filteredBusinesses.length === 0 && (searchTerm || selectedIndustry)" 
             class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
            <p class="mt-1 text-sm text-gray-500">
                Try adjusting your search terms or filters.
            </p>
            <div class="mt-6">
                <button @click="searchTerm = ''; selectedIndustry = ''; filterBusinesses();"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-indigo-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 border-indigo-600">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>
@endsection 