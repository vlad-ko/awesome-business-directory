@extends('layouts.app')

@section('content')
<!-- Multi-Step Form - Step 3: Location Information -->
<div class="min-h-screen bg-gradient-to-br from-purple-600 via-pink-500 to-yellow-400">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            
            <!-- Progress Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-white font-semibold">Step {{ $step }} of {{ $totalSteps }}</span>
                    <span class="text-white font-semibold">{{ $progress }}% Complete</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-3">
                    <div class="bg-gradient-to-r from-yellow-300 to-pink-300 h-3 rounded-full transition-all duration-300" 
                         style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <!-- Step Header -->
            <div class="text-center mb-12">
                <div class="text-8xl mb-4">📍</div>
                <h1 class="text-5xl font-bold text-white mb-4 leading-tight">
                    {{ $stepConfig['title'] }}
                </h1>
                <p class="text-xl text-white/90 mb-8">
                    Help customers find your amazing business! 🗺️
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

            <form action="{{ route('business.onboard.step.store', $step) }}" method="POST" class="space-y-8">
                @csrf

                <!-- Address Section -->
                <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2 md:col-span-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="street_address">
                                <span class="mr-2">🏠</span>
                                Street Address *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('street_address') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="street_address" type="text" name="street_address" 
                                value="{{ old('street_address', $data['street_address'] ?? '') }}" 
                                placeholder="123 Main Street" required>
                            @error('street_address')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="city">
                                <span class="mr-2">🏙️</span>
                                City *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('city') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="city" type="text" name="city" 
                                value="{{ old('city', $data['city'] ?? '') }}" 
                                placeholder="Your City" required>
                            @error('city')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="state_province">
                                <span class="mr-2">🗺️</span>
                                State/Province *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('state_province') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="state_province" type="text" name="state_province" 
                                value="{{ old('state_province', $data['state_province'] ?? '') }}" 
                                placeholder="State or Province" required>
                            @error('state_province')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="postal_code">
                                <span class="mr-2">📮</span>
                                Postal Code *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('postal_code') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="postal_code" type="text" name="postal_code" 
                                value="{{ old('postal_code', $data['postal_code'] ?? '') }}" 
                                placeholder="12345" required>
                            @error('postal_code')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="country">
                                <span class="mr-2">🌍</span>
                                Country *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('country') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="country" type="text" name="country" 
                                value="{{ old('country', $data['country'] ?? '') }}" 
                                placeholder="United States" required>
                            @error('country')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between items-center">
                    <div class="flex space-x-4">
                        <a href="{{ route('business.onboard.step', 2) }}" 
                           class="group relative px-8 py-4 bg-white/20 backdrop-blur-sm text-white font-bold rounded-2xl hover:bg-white/30 focus:outline-none focus:ring-4 focus:ring-white/50 transform hover:scale-105 transition-all duration-300 shadow-2xl border border-white/30">
                            <span class="flex items-center">
                                <span class="mr-2 text-xl group-hover:-translate-x-1 transition-transform duration-300">←</span>
                                Back to Contact Info
                            </span>
                        </a>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" 
                                class="group relative px-8 py-4 bg-gradient-to-r from-yellow-400 to-pink-400 text-white font-bold rounded-2xl hover:from-yellow-300 hover:to-pink-300 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 transform hover:scale-105 transition-all duration-300 shadow-2xl">
                            <span class="flex items-center">
                                Continue to Owner Info
                                <span class="ml-2 text-xl group-hover:translate-x-1 transition-transform duration-300">→</span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 