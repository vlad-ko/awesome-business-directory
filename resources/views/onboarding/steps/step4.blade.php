@extends('layouts.app')

@section('content')
<!-- Multi-Step Form - Step 4: Owner Information -->
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
                <div class="text-8xl mb-4">👤</div>
                <h1 class="text-5xl font-bold text-white mb-4 leading-tight">
                    {{ $stepConfig['title'] }}
                </h1>
                <p class="text-xl text-white/90 mb-8">
                    Tell us about the amazing person behind this business! 🌟
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

                <!-- Owner Information Section -->
                <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="owner_name">
                                <span class="mr-2">👨‍💼</span>
                                Owner Name *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('owner_name') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="owner_name" type="text" name="owner_name" 
                                value="{{ old('owner_name', $data['owner_name'] ?? '') }}" 
                                placeholder="John Doe" required>
                            @error('owner_name')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="owner_email">
                                <span class="mr-2">📧</span>
                                Owner Email *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('owner_email') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="owner_email" type="email" name="owner_email" 
                                value="{{ old('owner_email', $data['owner_email'] ?? '') }}" 
                                placeholder="john@yourbusiness.com" required>
                            @error('owner_email')
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
                        <a href="{{ route('business.onboard.step', 3) }}" 
                           class="group relative px-8 py-4 bg-white/20 backdrop-blur-sm text-white font-bold rounded-2xl hover:bg-white/30 focus:outline-none focus:ring-4 focus:ring-white/50 transform hover:scale-105 transition-all duration-300 shadow-2xl border border-white/30">
                            <span class="flex items-center">
                                <span class="mr-2 text-xl group-hover:-translate-x-1 transition-transform duration-300">←</span>
                                Back to Location
                            </span>
                        </a>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" 
                                class="group relative px-8 py-4 bg-gradient-to-r from-yellow-400 to-pink-400 text-white font-bold rounded-2xl hover:from-yellow-300 hover:to-pink-300 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 transform hover:scale-105 transition-all duration-300 shadow-2xl">
                            <span class="flex items-center">
                                Review & Submit
                                <span class="ml-2 text-xl group-hover:translate-x-1 transition-transform duration-300">🎯</span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 