@extends('layouts.app')

@section('title', $business->business_name . ' - ' . $business->tagline)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-4">
                            <li>
                                <a href="{{ route('welcome') }}" class="text-gray-400 hover:text-gray-500">
                                    Home
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                                    </svg>
                                    <a href="{{ route('businesses.index') }}" class="ml-4 text-gray-400 hover:text-gray-500">
                                        Businesses
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                                    </svg>
                                    <span class="ml-4 text-sm font-medium text-gray-500">{{ $business->business_name }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <div class="mt-2 flex items-center">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $business->business_name }}</h1>
                        <div class="ml-4 flex items-center space-x-2">
                            @if($business->is_verified)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Verified
                                </span>
                            @endif
                            @if($business->is_featured)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                    Featured
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($business->tagline)
                        <p class="mt-1 text-lg text-gray-600">{{ $business->tagline }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Business Overview -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">About This Business</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Learn more about {{ $business->business_name }}</p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                        <p class="text-gray-900">{{ $business->description }}</p>
                        
                        <div class="mt-6 grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Industry</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $business->industry }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Business Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $business->business_type }}</dd>
                            </div>
                            @if($business->founded_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Founded</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($business->founded_date)->format('F Y') }}</dd>
                            </div>
                            @endif
                            @if($business->employee_count)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Employees</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $business->employee_count }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Services -->
                @if($business->services_offered)
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Services Offered</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">What {{ $business->business_name }} can do for you</p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach(json_decode($business->services_offered, true) as $service)
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm text-gray-900">{{ $service }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Business Hours -->
                @if($business->business_hours)
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Business Hours</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">When you can reach us</p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                        <dl class="space-y-2">
                            @foreach(json_decode($business->business_hours, true) as $day => $hours)
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">{{ ucfirst($day) }}</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if(isset($hours['closed']) && $hours['closed'])
                                            Closed
                                        @elseif(isset($hours['open']) && isset($hours['close']))
                                            {{ $hours['open'] }} - {{ $hours['close'] }}
                                        @else
                                            {{ is_array($hours) ? 'Contact for hours' : $hours }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Contact Information -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Contact Information</h3>
                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1">
                                    <a href="mailto:{{ $business->primary_email }}" class="text-indigo-600 hover:text-indigo-500">
                                        {{ $business->primary_email }}
                                    </a>
                                </dd>
                            </div>
                            @if($business->phone_number)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1">
                                    <a href="tel:{{ $business->phone_number }}" class="text-indigo-600 hover:text-indigo-500">
                                        {{ $business->phone_number }}
                                    </a>
                                </dd>
                            </div>
                            @endif
                            @if($business->website_url)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Website</dt>
                                <dd class="mt-1">
                                    <a href="{{ $business->website_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-500">
                                        Visit Website
                                        <svg class="inline w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                                            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                                        </svg>
                                    </a>
                                </dd>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Location</h3>
                        <div class="text-sm text-gray-900">
                            {{ $business->street_address }}<br>
                            {{ $business->city }}, {{ $business->state_province }} {{ $business->postal_code }}<br>
                            {{ $business->country }}
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                @if($business->facebook_url || $business->twitter_url || $business->instagram_url || $business->linkedin_url)
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Follow Us</h3>
                        <div class="flex space-x-4">
                            @if($business->facebook_url)
                                <a href="{{ $business->facebook_url }}" target="_blank" class="text-gray-400 hover:text-blue-600">
                                    <span class="sr-only">Facebook</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M20 10C20 4.477 15.523 0 10 0S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            @endif
                            @if($business->twitter_url)
                                <a href="{{ $business->twitter_url }}" target="_blank" class="text-gray-400 hover:text-blue-400">
                                    <span class="sr-only">Twitter</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"></path>
                                    </svg>
                                </a>
                            @endif
                            @if($business->instagram_url)
                                <a href="{{ $business->instagram_url }}" target="_blank" class="text-gray-400 hover:text-pink-600">
                                    <span class="sr-only">Instagram</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            @endif  
                            @if($business->linkedin_url)
                                <a href="{{ $business->linkedin_url }}" target="_blank" class="text-gray-400 hover:text-blue-700">
                                    <span class="sr-only">LinkedIn</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Back to Directory -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <a href="{{ route('businesses.index') }}" class="inline-flex items-center justify-center w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                            </svg>
                            Back to Directory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 