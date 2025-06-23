@extends('layouts.app')

@section('content')
<!-- Multi-Step Form - Step 1: Basic Business Information -->
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
                <div class="text-8xl mb-4">🏪</div>
                <h1 class="text-5xl font-bold text-white mb-4 leading-tight">
                    {{ $stepConfig['title'] }}
                </h1>
                <p class="text-xl text-white/90 mb-8">
                    Tell us about your amazing business so we can help customers find you! ✨
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

                <!-- Basic Information Section -->
                <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="business_name">
                                <span class="mr-2">✨</span>
                                What's your business called? *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('business_name') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="business_name" type="text" name="business_name" 
                                value="{{ old('business_name', $data['business_name'] ?? '') }}" 
                                placeholder="e.g., Joe's Amazing Pizza Palace" required>
                            @error('business_name')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="industry">
                                <span class="mr-2">🎯</span>
                                What industry are you in? *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('industry') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="industry" type="text" name="industry" 
                                value="{{ old('industry', $data['industry'] ?? '') }}" 
                                placeholder="e.g., Restaurant, Retail, Services" required>
                            @error('industry')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="business_type">
                                <span class="mr-2">🏢</span>
                                Business Structure *
                            </label>
                            <select class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('business_type') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="business_type" name="business_type" required>
                                <option value="" class="text-gray-800">Choose your business type...</option>
                                <option value="LLC" class="text-gray-800" {{ old('business_type', $data['business_type'] ?? '') == 'LLC' ? 'selected' : '' }}>LLC</option>
                                <option value="Corporation" class="text-gray-800" {{ old('business_type', $data['business_type'] ?? '') == 'Corporation' ? 'selected' : '' }}>Corporation</option>
                                <option value="Sole Proprietorship" class="text-gray-800" {{ old('business_type', $data['business_type'] ?? '') == 'Sole Proprietorship' ? 'selected' : '' }}>Sole Proprietorship</option>
                                <option value="Partnership" class="text-gray-800" {{ old('business_type', $data['business_type'] ?? '') == 'Partnership' ? 'selected' : '' }}>Partnership</option>
                            </select>
                            @error('business_type')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="tagline">
                                <span class="mr-2">💫</span>
                                Your Catchy Tagline
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300"
                                id="tagline" type="text" name="tagline" 
                                value="{{ old('tagline', $data['tagline'] ?? '') }}"
                                placeholder="e.g., Where flavor meets passion!">
                        </div>
                    </div>

                    <div class="mt-6 space-y-2">
                        <label class="flex items-center text-white font-semibold text-lg" for="description">
                            <span class="mr-2">📝</span>
                            Tell everyone what makes your business special! *
                        </label>
                        <textarea class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('description') border-red-300 ring-4 ring-red-300/50 @enderror"
                            id="description" name="description" rows="4" required
                            placeholder="Share your story, what you offer, and what makes your business unique...">{{ old('description', $data['description'] ?? '') }}</textarea>
                        @error('description')
                            <p class="text-red-200 text-sm font-medium flex items-center">
                                <span class="mr-1">⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center text-white/70">
                        <span class="text-sm">Need help? We're here for you! 💬</span>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" 
                                class="group relative px-8 py-4 bg-gradient-to-r from-yellow-400 to-pink-400 text-white font-bold rounded-2xl hover:from-yellow-300 hover:to-pink-300 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 transform hover:scale-105 transition-all duration-300 shadow-2xl">
                            <span class="flex items-center">
                                Continue to Contact Info
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