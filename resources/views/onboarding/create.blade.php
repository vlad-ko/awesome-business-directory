@extends('layouts.app')

@section('content')
<!-- Hero Section with Gradient Background -->
<div class="min-h-screen bg-gradient-to-br from-purple-600 via-pink-500 to-yellow-400">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Fun Header -->
            <div class="text-center mb-12">
                <div class="text-8xl mb-4">🚀</div>
                <h1 class="text-5xl font-bold text-white mb-4 leading-tight">
                    Let's Get Your Business <br>
                    <span class="bg-gradient-to-r from-yellow-300 to-pink-300 bg-clip-text text-transparent">
                        On The Map! 🗺️
                    </span>
                </h1>
                <p class="text-xl text-white/90 mb-8">
                    Join our amazing community of local businesses and start connecting with your neighbors! ✨
                </p>
                

            </div>

            @if(session('success'))
                <div class="bg-gradient-to-r from-green-400 to-emerald-500 text-white px-6 py-4 rounded-2xl relative mb-8 shadow-2xl backdrop-blur-sm border border-white/20" role="alert">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">🎉</span>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <form action="{{ route('business.store') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Basic Information Section -->
                <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <span class="text-4xl mr-4">🏪</span>
                        <h2 class="text-3xl font-bold text-white">Tell Us About Your Amazing Business!</h2>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="business_name">
                                <span class="mr-2">✨</span>
                                What's your business called? *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('business_name') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="business_name" type="text" name="business_name" value="{{ old('business_name') }}" 
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
                                id="industry" type="text" name="industry" value="{{ old('industry') }}" 
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
                                <option value="LLC" class="text-gray-800" {{ old('business_type') == 'LLC' ? 'selected' : '' }}>LLC</option>
                                <option value="Corporation" class="text-gray-800" {{ old('business_type') == 'Corporation' ? 'selected' : '' }}>Corporation</option>
                                <option value="Sole Proprietorship" class="text-gray-800" {{ old('business_type') == 'Sole Proprietorship' ? 'selected' : '' }}>Sole Proprietorship</option>
                                <option value="Partnership" class="text-gray-800" {{ old('business_type') == 'Partnership' ? 'selected' : '' }}>Partnership</option>
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
                                id="tagline" type="text" name="tagline" value="{{ old('tagline') }}"
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
                            placeholder="Share your story, what you offer, and what makes your business unique...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-200 text-sm font-medium flex items-center">
                                <span class="mr-1">⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <span class="text-4xl mr-4">📞</span>
                        <h2 class="text-3xl font-bold text-white">How Can Customers Reach You?</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="primary_email">
                                <span class="mr-2">✉️</span>
                                Business Email *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('primary_email') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="primary_email" type="email" name="primary_email" value="{{ old('primary_email') }}" 
                                placeholder="hello@yourbusiness.com" required>
                            @error('primary_email')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="phone_number">
                                <span class="mr-2">📱</span>
                                Phone Number *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('phone_number') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number') }}" 
                                placeholder="(555) 123-4567" required>
                            @error('phone_number')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="website_url">
                                <span class="mr-2">🌐</span>
                                Website (Show off your online presence!)
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300"
                                id="website_url" type="url" name="website_url" value="{{ old('website_url') }}"
                                placeholder="https://www.yourawesomebusiness.com">
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <span class="text-4xl mr-4">📍</span>
                        <h2 class="text-3xl font-bold text-white">Where Can People Find You?</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2 md:col-span-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="street_address">
                                <span class="mr-2">🏠</span>
                                Street Address *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('street_address') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="street_address" type="text" name="street_address" value="{{ old('street_address') }}" 
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
                                id="city" type="text" name="city" value="{{ old('city') }}" 
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
                                id="state_province" type="text" name="state_province" value="{{ old('state_province') }}" 
                                placeholder="CA" required>
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
                                id="postal_code" type="text" name="postal_code" value="{{ old('postal_code') }}" 
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
                                id="country" type="text" name="country" value="{{ old('country') }}" 
                                placeholder="United States" required>
                            @error('country')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Owner Information Section -->
                <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <span class="text-4xl mr-4">👤</span>
                        <h2 class="text-3xl font-bold text-white">Tell Us About The Boss!</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="owner_name">
                                <span class="mr-2">🎭</span>
                                Owner Name *
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300 @error('owner_name') border-red-300 ring-4 ring-red-300/50 @enderror"
                                id="owner_name" type="text" name="owner_name" value="{{ old('owner_name') }}" 
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
                                id="owner_email" type="email" name="owner_email" value="{{ old('owner_email') }}" 
                                placeholder="john@business.com" required>
                            @error('owner_email')
                                <p class="text-red-200 text-sm font-medium flex items-center">
                                    <span class="mr-1">⚠️</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="flex items-center text-white font-semibold text-lg" for="owner_phone">
                                <span class="mr-2">📲</span>
                                Owner Phone (Optional)
                            </label>
                            <input class="w-full py-4 px-6 bg-white/20 backdrop-blur-sm border border-white/30 rounded-2xl text-gray-800 placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 focus:bg-white/30 transition-all duration-300"
                                id="owner_phone" type="tel" name="owner_phone" value="{{ old('owner_phone') }}"
                                placeholder="(555) 987-6543">
                        </div>
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="text-center py-8">
                    <div class="mb-6">
                        <p class="text-xl text-white/90 mb-4">
                            🎉 Ready to join our amazing business community? 🎉
                        </p>
                        <p class="text-white/80">
                            Click the button below and let's get your business discovered by your neighbors!
                        </p>
                    </div>
                    
                    <button class="group relative inline-flex items-center justify-center px-12 py-6 text-2xl font-bold text-purple-600 bg-gradient-to-r from-yellow-300 via-pink-300 to-yellow-300 rounded-full hover:from-yellow-400 hover:via-pink-400 hover:to-yellow-400 focus:outline-none focus:ring-4 focus:ring-yellow-300/50 transform hover:scale-105 transition-all duration-300 shadow-2xl hover:shadow-yellow-300/50" type="submit">
                        <span class="mr-3 text-3xl group-hover:animate-bounce">🚀</span>
                        Launch My Business!
                        <span class="ml-3 text-3xl group-hover:animate-bounce">✨</span>
                    </button>
                    
                    <p class="text-white/60 text-sm mt-4">
                        Don't worry, you can always update your information later! 😊
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
