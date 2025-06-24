@extends('layouts.app')

@push('head')
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
@endpush

@section('title', $business->business_name . ' - ' . $business->tagline)

@section('content')
<style>
    .retro-business-detail-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #f5576c);
        background-size: 400% 400%;
        animation: gradientShift 15s ease-in-out infinite;
        min-height: 100vh;
    }
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .retro-business-card {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .retro-business-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.2);
    }
    
    .hero-business-card {
        background: linear-gradient(135deg, #ff6b6b, #4ecdc4, #45b7d1);
        border: 3px solid rgba(255,255,255,0.4);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 50px rgba(0,0,0,0.3);
    }
    
    .hero-business-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        animation: shine 8s ease-in-out infinite;
    }
    
    @keyframes shine {
        0% { transform: rotate(0deg) translate(-100%, -100%); }
        100% { transform: rotate(360deg) translate(-100%, -100%); }
    }
    
    .retro-business-title {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-weight: 800;
        text-shadow: 2px 2px 0px #ff1493, 4px 4px 0px rgba(0,0,0,0.3);
        color: #ffffff;
        letter-spacing: -0.025em;
    }
    
    .funky-breadcrumb {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        padding: 12px 20px;
        border: 2px solid rgba(255,255,255,0.2);
    }
    
    .retro-badge {
        background: linear-gradient(45deg, #ffff00, #ff6b6b);
        color: #000;
        font-weight: 700;
        text-shadow: none;
        border-radius: 20px;
        padding: 8px 16px;
        border: 2px solid rgba(255,255,255,0.8);
        animation: glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes glow {
        from { box-shadow: 0 0 5px rgba(255,255,0,0.5); }
        to { box-shadow: 0 0 20px rgba(255,107,107,0.8), 0 0 30px rgba(255,255,0,0.5); }
    }
    
    .contact-button {
        background: linear-gradient(45deg, #4ecdc4, #ff6b6b);
        border: 2px solid #ffffff;
        color: #ffffff;
        font-weight: 600;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        border-radius: 25px;
    }
    
    .contact-button:hover {
        background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    
    .retro-link {
        color: #ffff00;
        font-weight: 600;
        text-decoration: none;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        transition: all 0.3s ease;
    }
    
    .retro-link:hover {
        color: #00ffff;
        text-shadow: 0 0 10px rgba(0,255,255,0.8);
    }
    
    .business-text {
        font-weight: 500;
        line-height: 1.6;
        letter-spacing: -0.025em;
    }
    
    .business-label {
        font-weight: 600;
        font-size: 0.875rem;
        letter-spacing: -0.025em;
        color: rgba(255,255,255,0.8);
    }
    
    .business-value {
        font-weight: 500;
        color: #ffffff;
        font-size: 0.95rem;
    }
    
    .star {
        position: absolute;
        color: #ffff00;
        animation: twinkle 2s infinite;
    }
    
    @keyframes twinkle {
        0%, 100% { opacity: 0.3; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.2); }
    }
    
    .floating-emoji {
        position: fixed;
        font-size: 2rem;
        z-index: 100;
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    .section-title {
        font-weight: 700;
        font-size: 1.25rem;
        color: #ffffff;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
</style>

<div class="retro-business-detail-page relative">
    <!-- Animated Stars -->
    <div class="star" style="top: 8%; left: 12%;">⭐</div>
    <div class="star" style="top: 20%; left: 88%; animation-delay: 0.7s;">✨</div>
    <div class="star" style="top: 65%; left: 8%; animation-delay: 1.2s;">🌟</div>
    <div class="star" style="top: 85%; left: 90%; animation-delay: 2s;">⭐</div>
    <div class="star" style="top: 40%; left: 95%; animation-delay: 0.3s;">✨</div>
    
    <!-- Floating Business Emojis -->
    <div class="floating-emoji" style="top: 15%; right: 8%; animation-delay: 1s;">🏪</div>
    <div class="floating-emoji" style="bottom: 20%; left: 5%; animation-delay: 3s;">💼</div>
    <div class="floating-emoji" style="top: 70%; right: 12%; animation-delay: 5s;">🎯</div>

    <div class="container mx-auto px-6 py-8 relative z-10">
        <div class="max-w-7xl mx-auto">
            <!-- Funky Breadcrumb -->
            <div class="mb-8">
                <nav class="funky-breadcrumb inline-flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4 text-white">
                        <li>
                            <a href="{{ route('welcome') }}" class="retro-link">
                                🏠 Home
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <span class="mx-2 text-yellow-300">⚡</span>
                                <a href="{{ route('businesses.index') }}" class="retro-link">
                                    🏢 Businesses
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <span class="mx-2 text-yellow-300">⚡</span>
                                <span class="font-semibold text-yellow-300">{{ $business->business_name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Hero Section -->
            <div class="hero-business-card p-8 mb-12">
                <div class="relative z-20">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex-1">
                            <h1 class="text-4xl lg:text-5xl font-bold retro-business-title mb-4">
                                🌟 {{ $business->business_name }} 🌟
                            </h1>
                            @if($business->tagline)
                                <p class="text-xl business-text text-yellow-200 mb-6">
                                    "{{ $business->tagline }}"
                                </p>
                            @endif
                            <div class="flex flex-wrap gap-3 mb-6">
                                @if($business->is_featured)
                                    <span class="retro-badge">
                                        ⭐ FEATURED ⭐
                                    </span>
                                @endif
                                @if($business->is_verified)
                                    <span class="retro-badge">
                                        ✅ VERIFIED ✅
                                    </span>
                                @endif
                                <span class="retro-badge">
                                    🏷️ {{ strtoupper($business->industry) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Business Overview -->
                    <div class="retro-business-card p-8">
                        <h3 class="section-title mb-4">📖 About This Awesome Business</h3>
                        <p class="business-text text-white text-lg leading-relaxed mb-6">
                            {{ $business->description }}
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-rgba(255,255,255,0.1) p-4 rounded-lg border border-white/20">
                                <dt class="business-label mb-1">🏭 Industry</dt>
                                <dd class="business-value">{{ ucfirst($business->industry) }}</dd>
                            </div>
                            <div class="bg-rgba(255,255,255,0.1) p-4 rounded-lg border border-white/20">
                                <dt class="business-label mb-1">🏢 Business Type</dt>
                                <dd class="business-value">{{ ucfirst($business->business_type) }}</dd>
                            </div>
                            @if($business->founded_date)
                            <div class="bg-rgba(255,255,255,0.1) p-4 rounded-lg border border-white/20">
                                <dt class="business-label mb-1">📅 Founded</dt>
                                <dd class="business-value">{{ \Carbon\Carbon::parse($business->founded_date)->format('F Y') }}</dd>
                            </div>
                            @endif
                            @if($business->employee_count)
                            <div class="bg-rgba(255,255,255,0.1) p-4 rounded-lg border border-white/20">
                                <dt class="business-label mb-1">👥 Team Size</dt>
                                <dd class="business-value">{{ $business->employee_count }} People</dd>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Services -->
                    @if($business->services_offered)
                    <div class="retro-business-card p-8">
                        <h3 class="section-title mb-6">⚡ What We Rock At!</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach(json_decode($business->services_offered, true) as $service)
                                <div class="flex items-center bg-white/10 p-3 rounded-lg border border-white/20">
                                    <span class="text-2xl mr-3">🎯</span>
                                    <span class="business-text text-white">{{ $service }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Business Hours -->
                    @if($business->business_hours)
                    <div class="retro-business-card p-8">
                        <h3 class="section-title mb-6">🕐 When We're Rockin'</h3>
                        <div class="space-y-3">
                            @foreach(json_decode($business->business_hours, true) as $day => $hours)
                                <div class="flex justify-between items-center bg-white/10 p-3 rounded-lg border border-white/20">
                                    <dt class="business-label">{{ ucfirst($day) }}</dt>
                                    <dd class="business-value">
                                        @if(isset($hours['closed']) && $hours['closed'])
                                            😴 Closed
                                        @elseif(isset($hours['open']) && isset($hours['close']))
                                            ⏰ {{ $hours['open'] }} - {{ $hours['close'] }}
                                        @else
                                            📞 {{ is_array($hours) ? 'Contact for hours' : $hours }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Contact Information -->
                    <div class="retro-business-card p-6">
                        <h3 class="section-title mb-6">📞 Get In Touch!</h3>
                        <div class="space-y-4">
                            <div class="bg-white/10 p-4 rounded-lg border border-white/20">
                                <dt class="business-label mb-2">📧 Email</dt>
                                <dd>
                                    <a href="mailto:{{ $business->primary_email }}" class="contact-button inline-block px-4 py-2 text-sm">
                                        {{ $business->primary_email }}
                                    </a>
                                </dd>
                            </div>
                            @if($business->phone_number)
                            <div class="bg-white/10 p-4 rounded-lg border border-white/20">
                                <dt class="business-label mb-2">📱 Phone</dt>
                                <dd>
                                    <a href="tel:{{ $business->phone_number }}" class="contact-button inline-block px-4 py-2 text-sm">
                                        {{ $business->phone_number }}
                                    </a>
                                </dd>
                            </div>
                            @endif
                            @if($business->website_url)
                            <div class="bg-white/10 p-4 rounded-lg border border-white/20">
                                <dt class="business-label mb-2">🌐 Website</dt>
                                <dd>
                                    <a href="{{ $business->website_url }}" target="_blank" class="contact-button inline-flex items-center px-4 py-2 text-sm">
                                        Visit Website
                                        <span class="ml-2">🚀</span>
                                    </a>
                                </dd>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="retro-business-card p-6">
                        <h3 class="section-title mb-6">📍 Find Us Here!</h3>
                        <div class="bg-white/10 p-4 rounded-lg border border-white/20 business-text text-white">
                            <div class="text-center">
                                <div class="text-3xl mb-3">🗺️</div>
                                <div>{{ $business->street_address }}</div>
                                <div>{{ $business->city }}, {{ $business->state_province }} {{ $business->postal_code }}</div>
                                <div>{{ $business->country }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    @if($business->facebook_url || $business->twitter_url || $business->instagram_url || $business->linkedin_url)
                    <div class="retro-business-card p-6">
                        <h3 class="section-title mb-6">🌟 Follow Our Journey!</h3>
                        <div class="grid grid-cols-2 gap-3">
                            @if($business->facebook_url)
                                <a href="{{ $business->facebook_url }}" target="_blank" class="contact-button flex items-center justify-center p-3 text-sm">
                                    📘 Facebook
                                </a>
                            @endif
                            @if($business->twitter_url)
                                <a href="{{ $business->twitter_url }}" target="_blank" class="contact-button flex items-center justify-center p-3 text-sm">
                                    🐦 Twitter
                                </a>
                            @endif
                            @if($business->instagram_url)
                                <a href="{{ $business->instagram_url }}" target="_blank" class="contact-button flex items-center justify-center p-3 text-sm">
                                    📸 Instagram
                                </a>
                            @endif
                            @if($business->linkedin_url)
                                <a href="{{ $business->linkedin_url }}" target="_blank" class="contact-button flex items-center justify-center p-3 text-sm">
                                    💼 LinkedIn
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Back to Directory -->
                    <div class="retro-business-card p-6">
                        <a href="{{ route('businesses.index') }}" class="contact-button w-full flex items-center justify-center px-6 py-4 text-lg font-bold">
                            <span class="mr-3">🔙</span>
                            Back to Directory
                            <span class="ml-3">🏪</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 