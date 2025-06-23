@extends('layouts.app')

@section('content')
<!-- Success Page -->
<div class="min-h-screen bg-gradient-to-br from-purple-600 via-pink-500 to-yellow-400">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto text-center">
            
            <!-- Success Animation -->
            <div class="mb-12">
                <div class="text-9xl mb-8 animate-bounce">🎉</div>
                <h1 class="text-6xl font-bold text-white mb-6 leading-tight">
                    Congratulations!
                </h1>
                <h2 class="text-3xl font-bold text-white/90 mb-8">
                    Your Business Has Been Submitted Successfully! ✨
                </h2>
            </div>

            @if(session('success'))
                <div class="bg-gradient-to-r from-green-400 to-emerald-500 text-white px-8 py-6 rounded-3xl relative mb-12 shadow-2xl backdrop-blur-sm border border-white/20 max-w-2xl mx-auto" role="alert">
                    <div class="flex items-center justify-center">
                        <span class="text-3xl mr-4">🚀</span>
                        <span class="font-medium text-xl">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- What Happens Next -->
            <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20 mb-12">
                <h3 class="text-3xl font-bold text-white mb-8">What Happens Next?</h3>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="text-6xl mb-4">👀</div>
                        <h4 class="text-xl font-bold text-white mb-4">Review Process</h4>
                        <p class="text-white/80">
                            Our team will carefully review your business information to ensure quality and accuracy.
                        </p>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-6xl mb-4">✅</div>
                        <h4 class="text-xl font-bold text-white mb-4">Approval</h4>
                        <p class="text-white/80">
                            Once approved, your business will be live and discoverable by customers in your area!
                        </p>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-6xl mb-4">📧</div>
                        <h4 class="text-xl font-bold text-white mb-4">Notification</h4>
                        <p class="text-white/80">
                            We'll send you an email confirmation when your business is approved and live.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20 mb-12">
                <h3 class="text-3xl font-bold text-white mb-8">Expected Timeline</h3>
                <div class="flex items-center justify-center space-x-8">
                    <div class="text-center">
                        <div class="text-4xl mb-2">⏰</div>
                        <p class="text-white font-semibold">Review</p>
                        <p class="text-white/70">1-2 Business Days</p>
                    </div>
                    <div class="text-white/50 text-3xl">→</div>
                    <div class="text-center">
                        <div class="text-4xl mb-2">🎯</div>
                        <p class="text-white font-semibold">Go Live</p>
                        <p class="text-white/70">Within 48 Hours</p>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="space-y-6">
                <div class="bg-gradient-to-r from-blue-400/20 to-purple-500/20 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-blue-300/30">
                    <h3 class="text-2xl font-bold text-white mb-4">While You Wait...</h3>
                    <p class="text-white/80 mb-6">
                        Explore other amazing businesses in our directory and see what makes our community special!
                    </p>
                    
                    <div class="flex flex-col sm:flex-row justify-center items-stretch gap-4 sm:gap-6">
                        <a href="{{ route('businesses.index') }}" 
                           class="group relative px-8 py-4 bg-gradient-to-r from-blue-400 to-purple-500 text-white font-bold rounded-2xl hover:from-blue-300 hover:to-purple-400 focus:outline-none focus:ring-4 focus:ring-blue-300/50 transform hover:scale-105 transition-all duration-300 shadow-2xl min-w-[200px]">
                            <span class="flex items-center justify-center">
                                <span class="mr-2">🏪</span>
                                Browse Businesses
                                <span class="ml-2 text-xl group-hover:translate-x-1 transition-transform duration-300">→</span>
                            </span>
                        </a>
                        
                        <a href="{{ route('welcome') }}" 
                           class="group relative px-8 py-4 bg-white/20 backdrop-blur-sm text-white font-bold rounded-2xl hover:bg-white/30 focus:outline-none focus:ring-4 focus:ring-white/50 transform hover:scale-105 transition-all duration-300 shadow-2xl border border-white/30 min-w-[200px]">
                            <span class="flex items-center justify-center">
                                <span class="mr-2">🏠</span>
                                Back to Home
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="mt-12 text-white/70">
                <p class="text-lg">
                    Questions? We're here to help! 
                    <span class="font-semibold text-white">Contact us anytime</span> 💬
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 