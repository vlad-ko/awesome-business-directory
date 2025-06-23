@extends('layouts.app')

@section('content')
<!-- Hero Section with Gradient -->
<div class="bg-gradient-to-br from-purple-600 via-pink-500 to-yellow-400 py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl lg:text-6xl font-bold mb-4 text-white leading-tight">
                Local Business Directory 🏪
            </h1>
            <p class="text-xl text-purple-100 mb-8 leading-relaxed">
                Discover amazing businesses in our vibrant community! From cozy cafes to innovative startups, find your next favorite local spot. ✨
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('business.onboard.step', 1) }}" 
                   class="bg-yellow-400 hover:bg-yellow-300 text-purple-800 px-8 py-4 rounded-full font-bold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                    🚀 Add Your Business
                </a>
                <a href="{{ route('welcome') }}" 
                   class="bg-white/20 hover:bg-white/30 text-white border-2 border-white/30 px-8 py-4 rounded-full font-bold text-lg transition-all duration-200">
                    🏠 Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-6xl mx-auto">

        @if($businesses->count() > 0)
            <!-- Featured Businesses Section -->
            @if($businesses->where('is_featured', true)->count() > 0)
                <div class="mb-16">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-gray-800 mb-4">
                            ⭐ Featured Businesses ⭐
                        </h2>
                        <p class="text-lg text-gray-600">
                            These amazing local businesses are making waves in our community! 🌟
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($businesses->where('is_featured', true) as $business)
                            <div class="relative bg-gradient-to-br from-yellow-100 via-pink-50 to-purple-100 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden transform hover:scale-105 border-2 border-yellow-300">
                                <!-- Featured Badge -->
                                <div class="absolute -top-2 -right-2 z-10">
                                    <div class="bg-gradient-to-r from-yellow-400 to-orange-400 text-white text-sm font-bold px-4 py-2 rounded-full shadow-lg transform rotate-12">
                                        ⭐ FEATURED
                                    </div>
                                </div>
                                
                                <!-- Sparkle decorations -->
                                <div class="absolute top-4 left-4 text-yellow-400 text-2xl opacity-60">✨</div>
                                <div class="absolute bottom-4 right-4 text-pink-400 text-xl opacity-60">💫</div>
                                
                                <div class="p-8 relative z-10">
                                    <div class="flex items-start justify-between mb-4">
                                        <h3 class="text-2xl font-bold text-gray-800 leading-tight">
                                            {{ $business->business_name }}
                                        </h3>
                                        @if($business->is_verified)
                                            <span class="bg-green-400 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                                ✓ Verified
                                            </span>
                                        @endif
                                    </div>

                                    @if($business->tagline)
                                        <p class="text-lg text-purple-700 font-semibold mb-4 italic">
                                            "{{ $business->tagline }}" 💭
                                        </p>
                                    @endif

                                    <div class="mb-4">
                                        <span class="inline-block bg-gradient-to-r from-purple-500 to-pink-500 text-white text-sm font-bold px-4 py-2 rounded-full shadow-md">
                                            {{ $business->industry }}
                                        </span>
                                    </div>

                                    <p class="text-gray-700 text-sm mb-6 line-clamp-3 leading-relaxed">
                                        {{ Str::limit($business->description, 120) }}
                                    </p>

                                    <div class="text-sm text-gray-600 mb-6 space-y-2">
                                        <div class="flex items-center">
                                            <span class="text-lg mr-2">📍</span>
                                            {{ $business->city }}, {{ $business->state_province }}
                                        </div>
                                        @if($business->phone_number)
                                            <div class="flex items-center">
                                                <span class="text-lg mr-2">📞</span>
                                                {{ $business->phone_number }}
                                            </div>
                                        @endif
                                        @if($business->website_url)
                                            <div class="flex items-center">
                                                <span class="text-lg mr-2">🌐</span>
                                                <a href="{{ $business->website_url }}" target="_blank" class="text-purple-600 hover:text-purple-800 font-medium">
                                                    Visit Website
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                                        <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $business->business_type }}</span>
                                        <a href="{{ route('business.show', $business->business_slug) }}" 
                                           class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-6 py-3 rounded-full text-sm font-bold transition-all duration-200 transform hover:scale-105 shadow-lg">
                                            View Details ✨
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- All Businesses Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2 text-center">
                    🏢 All Local Businesses
                </h2>
                <p class="text-center text-gray-600 mb-8">
                    Browse through our complete directory of amazing local businesses
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($businesses as $business)
                    @if(!$business->is_featured)
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:scale-105 border border-white/50">

                            <div class="p-6">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-xl font-semibold text-gray-800 leading-tight">
                                        {{ $business->business_name }}
                                    </h3>
                                    @if($business->is_verified)
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">
                                            ✓ Verified
                                        </span>
                                    @endif
                                </div>

                                @if($business->tagline)
                                    <p class="text-sm text-purple-600 font-medium mb-3 italic">
                                        "{{ $business->tagline }}" 💭
                                    </p>
                                @endif

                                <div class="mb-4">
                                    <span class="inline-block bg-gradient-to-r from-blue-100 to-purple-100 text-purple-700 text-xs font-medium px-3 py-1 rounded-full">
                                        {{ $business->industry }}
                                    </span>
                                </div>

                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                    {{ Str::limit($business->description, 120) }}
                                </p>

                                <div class="text-sm text-gray-600 mb-4 space-y-1">
                                    <div class="flex items-center">
                                        <span class="text-base mr-2">📍</span>
                                        {{ $business->city }}, {{ $business->state_province }}
                                    </div>
                                    @if($business->phone_number)
                                        <div class="flex items-center">
                                            <span class="text-base mr-2">📞</span>
                                            {{ $business->phone_number }}
                                        </div>
                                    @endif
                                    @if($business->website_url)
                                        <div class="flex items-center">
                                            <span class="text-base mr-2">🌐</span>
                                            <a href="{{ $business->website_url }}" target="_blank" class="text-purple-600 hover:text-purple-800 font-medium">
                                                Visit Website
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $business->business_type }}</span>
                                    <a href="{{ route('business.show', $business->business_slug) }}" 
                                       class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 transform hover:scale-105 shadow-md">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Stats Section -->
            <div class="mt-12 text-center">
                <div class="bg-gradient-to-r from-blue-100 to-purple-100 rounded-2xl p-6 max-w-md mx-auto">
                    <p class="text-2xl font-bold text-gray-800 mb-2">{{ $businesses->count() }}</p>
                    <p class="text-gray-600">Amazing Local Businesses 🎉</p>
                </div>
            </div>
        @else
            <!-- Enhanced Empty state -->
            <div class="text-center py-20">
                <div class="max-w-2xl mx-auto">
                    <!-- Fun illustration -->
                    <div class="mx-auto flex items-center justify-center h-32 w-32 rounded-full bg-gradient-to-br from-yellow-100 to-pink-100 mb-8 border-4 border-yellow-300">
                        <div class="text-6xl">🏪</div>
                    </div>

                    <!-- Main heading -->
                    <h3 class="text-4xl font-bold text-gray-800 mb-4">
                        No businesses yet! 🚀
                    </h3>
                    
                    <!-- Subheading -->
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Our directory is just getting started! Be the first to join our growing community of local businesses and connect with customers in your area. ✨
                    </p>

                    <!-- Benefits list -->
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-8 mb-10 border border-blue-200">
                        <h4 class="text-2xl font-bold text-gray-800 mb-6 text-center">Why join our directory? 🌟</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-base text-gray-700">
                            <div class="flex items-center">
                                <span class="text-2xl mr-4">🆓</span>
                                <span class="font-medium">Free business listing</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-2xl mr-4">🎯</span>
                                <span class="font-medium">Reach local customers</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-2xl mr-4">🌐</span>
                                <span class="font-medium">Build your online presence</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-2xl mr-4">🤝</span>
                                <span class="font-medium">Connect with community</span>
                            </div>
                        </div>
                    </div>

                    <!-- Call to action buttons -->
                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <a href="{{ route('business.onboard') }}" 
                           class="inline-flex items-center justify-center px-10 py-4 text-lg font-bold rounded-full text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 transition-all duration-200 transform hover:scale-105 shadow-xl hover:shadow-2xl">
                            <span class="text-xl mr-3">🚀</span>
                            Add Your Business
                        </a>
                        
                        <a href="{{ route('welcome') }}" 
                           class="inline-flex items-center justify-center px-10 py-4 text-lg font-bold rounded-full text-purple-700 bg-white hover:bg-gray-50 border-2 border-purple-300 hover:border-purple-400 transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <span class="text-xl mr-3">🏠</span>
                            Back to Home
                        </a>
                    </div>

                    <!-- Additional info -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-500">
                            Questions about listing your business? 
                            <a href="mailto:hello@businessdirectory.com" class="text-blue-600 hover:text-blue-800 font-medium">
                                Contact us
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Call to action for adding businesses -->
        @if($businesses->count() > 0)
            <div class="mt-16">
                <div class="bg-gradient-to-r from-purple-500 via-pink-500 to-yellow-400 rounded-3xl p-12 text-center shadow-2xl">
                    <h3 class="text-4xl font-bold text-white mb-4">Join Our Directory! 🎉</h3>
                    <p class="text-xl text-purple-100 mb-8 leading-relaxed max-w-2xl mx-auto">
                        Are you a business owner? Join our growing community and reach more customers in your area! ✨
                    </p>
                    <a href="{{ route('business.onboard') }}" 
                       class="inline-flex items-center px-10 py-4 text-lg font-bold rounded-full text-purple-800 bg-yellow-400 hover:bg-yellow-300 transition-all duration-200 transform hover:scale-105 shadow-xl hover:shadow-2xl">
                        <span class="text-xl mr-3">🚀</span>
                        Add Your Business
                    </a>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>
@endsection 