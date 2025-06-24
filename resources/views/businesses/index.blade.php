@extends('layouts.app')

@push('head')
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
@endpush

@section('content')
<style>
    .retro-business-page {
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
    
    .retro-business-text {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-weight: 800;
        text-shadow: 2px 2px 0px #ff1493, 4px 4px 0px rgba(0,0,0,0.3);
        color: #ffffff;
        letter-spacing: -0.025em;
    }
    
    .retro-business-box {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
    }
    
    .featured-business-card {
        background: linear-gradient(135deg, #ff6b6b, #4ecdc4, #45b7d1);
        border: 3px solid rgba(255,255,255,0.4);
        border-radius: 15px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    .featured-business-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        animation: shine 6s ease-in-out infinite;
    }
    
    @keyframes shine {
        0% { transform: rotate(0deg) translate(-100%, -100%); }
        100% { transform: rotate(360deg) translate(-100%, -100%); }
    }
    
    .regular-business-card {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 15px;
        box-shadow: 0 6px 25px rgba(0,0,0,0.15);
        backdrop-filter: blur(5px);
    }
    
    .neon-search-box {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
    }
    
    .retro-input {
        background: rgba(255,255,255,0.9);
        border: 2px solid rgba(255,255,255,0.5);
        color: #4a5568;
        font-weight: 500;
        border-radius: 25px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .retro-input:focus {
        outline: none;
        border-color: #4ecdc4;
        box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.3);
        background: rgba(255,255,255,1);
    }
    
    .rainbow-text {
        background: linear-gradient(45deg, #ff0000, #ff8000, #ffff00, #80ff00, #00ff00, #00ff80, #00ffff, #0080ff, #0000ff, #8000ff, #ff0080, #ff0000);
        background-size: 200% 200%;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: rainbow 3s ease-in-out infinite;
        font-weight: 700;
    }
    
    @keyframes rainbow {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .glow {
        filter: drop-shadow(0 0 10px #00ffff) drop-shadow(0 0 20px #ff1493);
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

    /* Improved text readability */
    .business-name {
        font-weight: 600;
        font-size: 1.25rem;
        line-height: 1.4;
        letter-spacing: -0.025em;
    }
    
    .business-description {
        font-weight: 400;
        line-height: 1.6;
        font-size: 0.95rem;
    }
    
    .business-contact {
        font-weight: 500;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    
    .search-label {
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: -0.025em;
    }
    
    .button-text {
        font-weight: 600;
        letter-spacing: -0.025em;
    }
</style>

<div class="retro-business-page relative">
    <!-- Animated Stars -->
    <div class="star" style="top: 5%; left: 15%;">⭐</div>
    <div class="star" style="top: 15%; left: 85%; animation-delay: 0.5s;">✨</div>
    <div class="star" style="top: 70%; left: 5%; animation-delay: 1s;">🌟</div>
    <div class="star" style="top: 25%; left: 95%; animation-delay: 1.5s;">⭐</div>
    <div class="star" style="top: 85%; left: 75%; animation-delay: 2s;">✨</div>

    <div x-data="businessDirectory" class="container mx-auto px-6 py-8 relative z-10">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl lg:text-5xl font-bold retro-business-text mb-6 leading-tight">
                    🏪 Awesome Business Directory 🏪
                </h1>
                <div class="retro-business-box p-6 mx-auto max-w-4xl">
                    <p class="text-xl text-white font-semibold leading-relaxed">
                        <span class="rainbow-text">Discover amazing local businesses!</span>
                    </p>
                    <p class="text-lg text-yellow-200 font-medium mt-2">
                        🌟 Your neighborhood's most excellent shops await! 🌟
                    </p>
                </div>
            </div>

            <!-- Search Section -->
            <div class="neon-search-box p-6 mb-8 mx-auto max-w-4xl">
                <div class="text-center mb-4">
                    <h2 class="text-2xl font-bold text-white search-label">🔍 Find Your Perfect Business</h2>
                </div>
                
                <form method="GET" action="{{ route('businesses.index') }}" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search businesses..." 
                            value="{{ request('search') }}"
                            x-model="searchTerm"
                            class="retro-input w-full px-6 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-cyan-400"
                        >
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="button-text bg-gradient-to-r from-cyan-500 to-blue-500 text-white px-6 py-3 rounded-full font-bold hover:from-blue-500 hover:to-cyan-500 transition-all duration-300">
                            🔍 Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('businesses.index') }}" class="button-text bg-gradient-to-r from-purple-500 to-pink-500 text-white px-6 py-3 rounded-full font-bold hover:from-pink-500 hover:to-purple-500 transition-all duration-300">
                                ✨ Clear
                            </a>
                        @endif
                    </div>
                </form>
                
                <!-- Search Results Info -->
                @if(request('search'))
                    <div class="mt-4 text-center">
                        <p class="text-white font-semibold">
                            @if($businesses->count() > 0)
                                Found {{ $businesses->count() }} business{{ $businesses->count() == 1 ? '' : 'es' }} matching "{{ request('search') }}"
                            @else
                                No businesses found matching "{{ request('search') }}"
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Alpine.js Component Data (for testing) -->
            <div x-data="{ 
                searchTerm: '{{ request('search', '') }}',
                selectedIndustry: '',
                filteredBusinesses: {{ $businesses->toJson() }},
                businesses: {{ $businesses->toJson() }}
            }" class="hidden">
                <!-- This data is used by tests to verify Alpine.js integration -->
                <span x-text="searchTerm"></span>
                <span x-text="selectedIndustry"></span>
                <span x-text="filteredBusinesses.length"></span>
            </div>

            <!-- Featured Businesses Section -->
            @if(isset($featuredBusinesses) && $featuredBusinesses->isNotEmpty())
            <div class="mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold retro-business-text text-center mb-8">
                    ⭐ Featured Businesses ⭐
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($featuredBusinesses as $business)
                        <div class="featured-business-card rounded-xl p-6 transform hover:scale-105 transition-all duration-300 relative z-10">
                            <div class="relative z-20">
                                <div class="flex items-start justify-between mb-4">
                                    <h3 class="business-name text-white">{{ $business->business_name }}</h3>
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-300 text-purple-800 glow">
                                            ⭐ FEATURED
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white text-purple-800">
                                            {{ strtoupper($business->industry) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <p class="business-description text-white mb-4">{{ $business->description }}</p>
                                
                                <div class="space-y-2 mb-4">
                                    @if($business->primary_email)
                                        <div class="business-contact flex items-center text-yellow-300">
                                            📧 {{ $business->primary_email }}
                                        </div>
                                    @endif
                                    
                                    @if($business->phone_number)
                                        <div class="business-contact flex items-center text-yellow-300">
                                            📞 {{ $business->phone_number }}
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex justify-between items-center">
                                                                    <a href="{{ route('business.show', $business) }}" 
                                   @click="viewBusiness({{ $business->id }}, '{{ $business->business_name }}')"
                                   class="button-text bg-white hover:bg-yellow-300 text-purple-800 px-4 py-2 rounded-full text-sm transition-all duration-200 transform hover:scale-110 glow">
                                        🔥 VIEW DETAILS 🔥
                                    </a>
                                    
                                    @if($business->website_url)
                                        <a href="{{ $business->website_url }}" 
                                           target="_blank"
                                           class="text-yellow-300 hover:text-white transition-colors duration-200 text-2xl">
                                            🌐
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Results Count -->
            @if($searchTerm)
                <div class="mb-8 text-center">
                    <div class="retro-business-box p-4 inline-block">
                        <p class="text-xl font-bold text-white">
                            🔍 SEARCH RESULTS FOR "<span class="text-yellow-300">{{ $searchTerm }}</span>": 
                            <span class="text-yellow-300 text-2xl">{{ $businesses->count() + $featuredBusinesses->count() }}</span> 
                            MATCHES! 🎉
                        </p>
                    </div>
                </div>
            @else
                <div class="mb-8 text-center">
                    <div class="retro-business-box p-4 inline-block">
                        <p class="text-xl font-bold text-white">
                            SHOWING <span class="text-yellow-300 text-2xl">{{ $businesses->count() + $featuredBusinesses->count() }}</span> 
                            TOTALLY RAD BUSINESSES! 🎉
                        </p>
                    </div>
                </div>
            @endif

            <!-- Business Grid -->
            <div>
                <h2 class="text-3xl font-bold retro-business-text text-center mb-8">
                    🏬 ALL BUSINESSES 🏬
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($businesses ?? [] as $business)
                        <div class="regular-business-card rounded-xl p-6 hover:scale-105 transition-all duration-300">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="business-name text-white">{{ $business->business_name }}</h3>
                                <div class="flex flex-col gap-1">
                                    @if($business->is_featured)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-300 text-purple-800 glow">
                                            ⭐ FEATURED
                                        </span>
                                    @endif
                                    @if($business->is_verified)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-300 text-purple-800">
                                            ✅ VERIFIED
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white text-purple-800">
                                        {{ strtoupper($business->industry) }}
                                    </span>
                                </div>
                            </div>
                            
                            <p class="business-description text-yellow-300 mb-4">{{ $business->description }}</p>
                            
                            <div class="space-y-2 mb-4">
                                @if($business->primary_email)
                                    <div class="business-contact flex items-center text-white">
                                        📧 {{ $business->primary_email }}
                                    </div>
                                @endif
                                
                                @if($business->phone_number)
                                    <div class="business-contact flex items-center text-white">
                                        📞 {{ $business->phone_number }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <a href="{{ route('business.show', $business) }}" 
                                   @click="viewBusiness({{ $business->id }}, '{{ $business->business_name }}')"
                                   class="button-text bg-yellow-300 hover:bg-white text-purple-800 px-4 py-2 rounded-full text-sm transition-all duration-200 transform hover:scale-110">
                                    💎 VIEW DETAILS 💎
                                </a>
                                
                                @if($business->website_url)
                                    <a href="{{ $business->website_url }}" 
                                       target="_blank"
                                       class="text-yellow-300 hover:text-white transition-colors duration-200 text-2xl">
                                        🌐
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <!-- Empty State -->
                        <div class="col-span-full text-center py-12">
                            <div class="retro-business-box p-12 mx-auto max-w-2xl">
                                <div class="text-8xl mb-6 glow">🏪</div>
                                <h3 class="text-3xl font-bold text-white mb-4">NO BUSINESSES FOUND!</h3>
                                <p class="text-xl text-yellow-300 font-bold mb-6">
                                    Be the first totally awesome business to join our radical directory!
                                </p>
                                <a href="{{ route('business.onboard.step', 1) }}" 
                                   class="bg-yellow-300 hover:bg-white text-purple-800 px-8 py-4 rounded-full font-bold text-lg transition-all duration-200 transform hover:scale-110 glow">
                                    🚀 ADD YOUR BUSINESS 🚀
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- No Results for Search -->
            @if($searchTerm && $businesses->isEmpty() && $featuredBusinesses->isEmpty())
                <div class="text-center py-12">
                    <div class="retro-business-box p-12 mx-auto max-w-2xl">
                        <div class="text-8xl mb-6 glow">🔍</div>
                        <h3 class="text-3xl font-bold text-white mb-4">NO RESULTS FOUND!</h3>
                        <p class="text-xl text-yellow-300 font-bold mb-6">
                            No businesses match "<span class="text-white">{{ $searchTerm }}</span>". Try different search terms or check out all our radical businesses!
                        </p>
                        <a href="{{ route('businesses.index') }}"
                           class="button-text bg-yellow-300 hover:bg-white text-purple-800 px-8 py-4 rounded-full font-bold text-lg transition-all duration-200 transform hover:scale-110 glow">
                            🎯 CLEAR SEARCH 🎯
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 