@extends('layouts.app')

@section('content')
<!-- Multi-Step Form - Review Page -->
<div class="min-h-screen bg-gradient-to-br from-purple-600 via-pink-500 to-yellow-400">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="text-center mb-12">
                <div class="text-8xl mb-4">📋</div>
                <h1 class="text-5xl font-bold text-white mb-4 leading-tight">
                    Review Your Information
                </h1>
                <p class="text-xl text-white/90 mb-8">
                    Take a moment to review everything before we submit your business! ✨
                </p>
            </div>

            @if(session('error'))
                <div class="bg-gradient-to-r from-red-400 to-red-500 text-white px-6 py-4 rounded-2xl relative mb-8 shadow-2xl backdrop-blur-sm border border-white/20" role="alert">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">⚠️</span>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <form action="{{ route('business.onboard.submit') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Review Sections -->
                <div class="space-y-6">
                    
                    <!-- Business Information -->
                    <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <span class="text-4xl mr-4">🏪</span>
                                <h2 class="text-3xl font-bold text-white">Business Information</h2>
                            </div>
                            <a href="{{ route('business.onboard.step', 1) }}" 
                               class="text-yellow-300 hover:text-yellow-200 font-semibold transition-colors duration-300">
                                Edit ✏️
                            </a>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 text-white">
                            <div>
                                <span class="font-semibold text-white/70">Business Name:</span>
                                <p class="text-lg">{{ $data['business_name'] ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-white/70">Industry:</span>
                                <p class="text-lg">{{ $data['industry'] ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-white/70">Business Type:</span>
                                <p class="text-lg">{{ $data['business_type'] ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-white/70">Tagline:</span>
                                <p class="text-lg">{{ $data['tagline'] ?? 'Not provided' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="font-semibold text-white/70">Description:</span>
                                <p class="text-lg">{{ $data['description'] ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <span class="text-4xl mr-4">📞</span>
                                <h2 class="text-3xl font-bold text-white">Contact Information</h2>
                            </div>
                            <a href="{{ route('business.onboard.step', 2) }}" 
                               class="text-yellow-300 hover:text-yellow-200 font-semibold transition-colors duration-300">
                                Edit ✏️
                            </a>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 text-white">
                            <div>
                                <span class="font-semibold text-white/70">Business Email:</span>
                                <p class="text-lg">{{ $data['primary_email'] ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-white/70">Phone Number:</span>
                                <p class="text-lg">{{ $data['phone_number'] ?? 'Not provided' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="font-semibold text-white/70">Website:</span>
                                <p class="text-lg">{{ $data['website_url'] ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <span class="text-4xl mr-4">📍</span>
                                <h2 class="text-3xl font-bold text-white">Location</h2>
                            </div>
                            <a href="{{ route('business.onboard.step', 3) }}" 
                               class="text-yellow-300 hover:text-yellow-200 font-semibold transition-colors duration-300">
                                Edit ✏️
                            </a>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 text-white">
                            <div class="md:col-span-2">
                                <span class="font-semibold text-white/70">Street Address:</span>
                                <p class="text-lg">{{ $data['street_address'] ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-white/70">City:</span>
                                <p class="text-lg">{{ $data['city'] ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-white/70">State/Province:</span>
                                <p class="text-lg">{{ $data['state_province'] ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-white/70">Postal Code:</span>
                                <p class="text-lg">{{ $data['postal_code'] ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-white/70">Country:</span>
                                <p class="text-lg">{{ $data['country'] ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Owner Information -->
                    <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <span class="text-4xl mr-4">👤</span>
                                <h2 class="text-3xl font-bold text-white">Owner Information</h2>
                            </div>
                            <a href="{{ route('business.onboard.step', 4) }}" 
                               class="text-yellow-300 hover:text-yellow-200 font-semibold transition-colors duration-300">
                                Edit ✏️
                            </a>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 text-white">
                            <div>
                                <span class="font-semibold text-white/70">Owner Name:</span>
                                <p class="text-lg">{{ $data['owner_name'] ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-white/70">Owner Email:</span>
                                <p class="text-lg">{{ $data['owner_email'] ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final Submission -->
                <div class="bg-gradient-to-r from-green-400/20 to-emerald-500/20 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-green-300/30">
                    <div class="text-center">
                        <div class="text-6xl mb-4">🎉</div>
                        <h3 class="text-2xl font-bold text-white mb-4">Ready to Submit?</h3>
                        <p class="text-white/80 mb-6">
                            Once you submit, our team will review your business information and get back to you soon!
                        </p>
                        
                        <div class="flex justify-center space-x-4">
                            <a href="{{ route('business.onboard.step', 4) }}" 
                               class="px-8 py-4 bg-white/20 backdrop-blur-sm text-white font-bold rounded-2xl hover:bg-white/30 focus:outline-none focus:ring-4 focus:ring-white/50 transform hover:scale-105 transition-all duration-300 shadow-2xl border border-white/30">
                                Back to Edit
                            </a>
                            
                            <button type="submit" 
                                    class="group relative px-12 py-4 bg-gradient-to-r from-green-400 to-emerald-500 text-white font-bold rounded-2xl hover:from-green-300 hover:to-emerald-400 focus:outline-none focus:ring-4 focus:ring-green-300/50 transform hover:scale-105 transition-all duration-300 shadow-2xl">
                                <span class="flex items-center">
                                    🚀 Submit My Business
                                    <span class="ml-2 text-xl group-hover:translate-x-1 transition-transform duration-300">✨</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 