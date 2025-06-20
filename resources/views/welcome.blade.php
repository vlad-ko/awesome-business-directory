<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Discover Amazing Local Businesses</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .neon-glow {
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        }
        .retro-text {
            text-shadow: 3px 3px 0px #ff6b6b, 6px 6px 0px #4ecdc4;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen gradient-bg">
        <!-- Header -->
        <header class="relative z-10">
            <nav class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-white font-bold text-2xl">
                        🏪 Awesome Business Directory
                    </div>
                    <div class="space-x-4">
                        <a href="{{ route('businesses.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 backdrop-blur-sm">Browse Businesses</a>
                        <a href="{{ route('business.onboard') }}" class="bg-yellow-400 hover:bg-yellow-300 text-purple-800 px-6 py-2 rounded-full font-bold transition-all duration-200 transform hover:scale-105 neon-glow">Join Directory</a>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <main class="container mx-auto px-6 py-12">
            <!-- Hero Content -->
            <div class="text-center mb-12">
                <h1 class="text-5xl lg:text-7xl font-bold text-white mb-6 retro-text">
                    Discover Your
                    <span class="text-yellow-300">Neighborhood</span>
                    Gems! 💎
                </h1>
                <p class="text-xl text-purple-100 mb-8 leading-relaxed max-w-2xl mx-auto">
                    Find amazing local shops, restaurants, and services that make your 
                    community awesome. 🌟
                </p>
                
                <!-- Call to Action Buttons - Centered -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    <a href="{{ route('businesses.index') }}" 
                       class="bg-pink-500 hover:bg-pink-400 text-white px-8 py-4 rounded-full font-bold text-lg transition-all duration-200 transform hover:scale-105 neon-glow">
                        🔍 Explore Businesses
                    </a>
                    <a href="{{ route('business.onboard') }}" 
                       class="bg-transparent border-2 border-yellow-300 text-yellow-300 hover:bg-yellow-300 hover:text-purple-800 px-8 py-4 rounded-full font-bold text-lg transition-all duration-200">
                        🚀 List Your Business
                    </a>
                </div>
            </div>

            <!-- Full Width SVG -->
            <div class="w-full mb-16">
                <svg width="100%" height="400" viewBox="0 0 500 400" xmlns="http://www.w3.org/2000/svg" class="drop-shadow-2xl w-full" preserveAspectRatio="xMidYMid meet">
                        <!-- Sky with gradient -->
                        <defs>
                            <linearGradient id="skyGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#87CEEB;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#FFB6C1;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="sunGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#FFD700;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#FFA500;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        
                        <!-- Sky background -->
                        <rect width="500" height="250" fill="url(#skyGradient)"/>
                        
                        <!-- Sun -->
                        <circle cx="400" cy="80" r="40" fill="url(#sunGradient)" opacity="0.9"/>
                        <circle cx="400" cy="80" r="45" fill="none" stroke="#FFD700" stroke-width="2" opacity="0.5"/>
                        <circle cx="400" cy="80" r="50" fill="none" stroke="#FFD700" stroke-width="1" opacity="0.3"/>
                        
                        <!-- Clouds -->
                        <ellipse cx="120" cy="60" rx="30" ry="15" fill="white" opacity="0.8"/>
                        <ellipse cx="140" cy="55" rx="25" ry="12" fill="white" opacity="0.8"/>
                        <ellipse cx="300" cy="90" rx="35" ry="18" fill="white" opacity="0.7"/>
                        <ellipse cx="320" cy="85" rx="28" ry="14" fill="white" opacity="0.7"/>
                        
                        <!-- Ground -->
                        <rect x="0" y="250" width="500" height="150" fill="#90EE90"/>
                        
                        <!-- Street -->
                        <rect x="0" y="320" width="500" height="40" fill="#696969"/>
                        <rect x="0" y="338" width="500" height="4" fill="#FFFF00"/>
                        
                        <!-- Building 1 - Pizza Shop -->
                        <rect x="50" y="180" width="80" height="70" fill="#FF6B6B"/>
                        <rect x="60" y="190" width="15" height="20" fill="#87CEEB"/>
                        <rect x="85" y="190" width="15" height="20" fill="#87CEEB"/>
                        <rect x="110" y="190" width="15" height="20" fill="#87CEEB"/>
                        <rect x="70" y="220" width="30" height="30" fill="#8B4513"/>
                        <polygon points="50,180 90,150 130,180" fill="#FF1493"/>
                        <text x="90" y="140" text-anchor="middle" fill="white" font-size="12" font-weight="bold">🍕 PIZZA</text>
                        
                        <!-- Building 2 - Coffee Shop -->
                        <rect x="150" y="190" width="70" height="60" fill="#4ECDC4"/>
                        <rect x="160" y="200" width="12" height="18" fill="#87CEEB"/>
                        <rect x="180" y="200" width="12" height="18" fill="#87CEEB"/>
                        <rect x="200" y="200" width="12" height="18" fill="#87CEEB"/>
                        <rect x="170" y="225" width="25" height="25" fill="#8B4513"/>
                        <polygon points="150,190 185,165 220,190" fill="#9B59B6"/>
                        <text x="185" y="155" text-anchor="middle" fill="white" font-size="10" font-weight="bold">☕ CAFÉ</text>
                        
                        <!-- Building 3 - Bookstore -->
                        <rect x="240" y="175" width="75" height="75" fill="#F39C12"/>
                        <rect x="250" y="185" width="14" height="22" fill="#87CEEB"/>
                        <rect x="270" y="185" width="14" height="22" fill="#87CEEB"/>
                        <rect x="290" y="185" width="14" height="22" fill="#87CEEB"/>
                        <rect x="260" y="215" width="28" height="35" fill="#8B4513"/>
                        <polygon points="240,175 277.5,145 315,175" fill="#E74C3C"/>
                        <text x="277" y="135" text-anchor="middle" fill="white" font-size="10" font-weight="bold">📚 BOOKS</text>
                        
                        <!-- Building 4 - Flower Shop -->
                        <rect x="330" y="185" width="65" height="65" fill="#FF69B4"/>
                        <rect x="340" y="195" width="12" height="20" fill="#87CEEB"/>
                        <rect x="360" y="195" width="12" height="20" fill="#87CEEB"/>
                        <rect x="380" y="195" width="12" height="20" fill="#87CEEB"/>
                        <rect x="350" y="220" width="25" height="30" fill="#8B4513"/>
                        <polygon points="330,185 362.5,160 395,185" fill="#32CD32"/>
                        <text x="362" y="150" text-anchor="middle" fill="white" font-size="9" font-weight="bold">🌸 FLOWERS</text>
                        
                        <!-- Building 5 - Bakery -->
                        <rect x="410" y="195" width="60" height="55" fill="#DDA0DD"/>
                        <rect x="420" y="205" width="10" height="16" fill="#87CEEB"/>
                        <rect x="435" y="205" width="10" height="16" fill="#87CEEB"/>
                        <rect x="450" y="205" width="10" height="16" fill="#87CEEB"/>
                        <rect x="430" y="230" width="20" height="20" fill="#8B4513"/>
                        <polygon points="410,195 440,175 470,195" fill="#FFD700"/>
                        <text x="440" y="165" text-anchor="middle" fill="white" font-size="9" font-weight="bold">🥖 BAKERY</text>
                        
                        <!-- Trees -->
                        <circle cx="30" cy="240" r="20" fill="#228B22"/>
                        <rect x="28" y="240" width="4" height="25" fill="#8B4513"/>
                        
                        <circle cx="480" cy="235" r="18" fill="#228B22"/>
                        <rect x="478" y="235" width="4" height="22" fill="#8B4513"/>
                        
                        <!-- Roller Skater -->
                        <g transform="translate(200,290)">
                            <!-- Body -->
                            <ellipse cx="0" cy="0" rx="8" ry="12" fill="#FF1493"/>
                            <!-- Head -->
                            <circle cx="0" cy="-18" r="6" fill="#FDBCB4"/>
                            <!-- Hair -->
                            <path d="M -6,-24 Q 0,-30 6,-24" fill="#FFD700"/>
                            <!-- Arms -->
                            <ellipse cx="-12" cy="-5" rx="3" ry="8" fill="#FDBCB4" transform="rotate(-20)"/>
                            <ellipse cx="12" cy="-5" rx="3" ry="8" fill="#FDBCB4" transform="rotate(20)"/>
                            <!-- Legs -->
                            <ellipse cx="-6" cy="15" rx="3" ry="10" fill="#4169E1"/>
                            <ellipse cx="6" cy="15" rx="3" ry="10" fill="#4169E1"/>
                            <!-- Roller Skates -->
                            <ellipse cx="-6" cy="28" rx="8" ry="4" fill="#FF6347"/>
                            <ellipse cx="6" cy="28" rx="8" ry="4" fill="#FF6347"/>
                            <!-- Wheels -->
                            <circle cx="-10" cy="30" r="2" fill="#FFD700"/>
                            <circle cx="-2" cy="30" r="2" fill="#FFD700"/>
                            <circle cx="2" cy="30" r="2" fill="#FFD700"/>
                            <circle cx="10" cy="30" r="2" fill="#FFD700"/>
                        </g>
                        
                        <!-- Motion lines -->
                        <path d="M 180,295 Q 175,290 170,295" stroke="#FF1493" stroke-width="2" fill="none" opacity="0.6"/>
                        <path d="M 175,300 Q 170,295 165,300" stroke="#FF1493" stroke-width="2" fill="none" opacity="0.4"/>
                        
                        <!-- Street lamp -->
                        <rect x="120" y="260" width="3" height="60" fill="#2F4F4F"/>
                        <circle cx="121.5" cy="255" r="8" fill="#FFD700" opacity="0.8"/>
                        
                        <!-- Retro style decorative elements -->
                        <circle cx="50" cy="50" r="3" fill="#FF69B4" opacity="0.7"/>
                        <circle cx="450" cy="60" r="4" fill="#00CED1" opacity="0.6"/>
                        <circle cx="100" cy="30" r="2" fill="#FFD700" opacity="0.8"/>
                        <circle cx="350" cy="40" r="3" fill="#FF1493" opacity="0.7"/>
                        
                        <!-- Sparkles -->
                        <g fill="#FFD700" opacity="0.8">
                            <polygon points="80,120 82,125 87,125 83,128 85,133 80,130 75,133 77,128 73,125 78,125" />
                            <polygon points="320,110 322,115 327,115 323,118 325,123 320,120 315,123 317,118 313,115 318,115" />
                            <polygon points="420,130 422,135 427,135 423,138 425,143 420,140 415,143 417,138 413,135 418,135" />
                        </g>
                    </svg>
                </div>
            </div>

        </main>

        <!-- Features Section -->
        <section class="py-16 bg-white/5 backdrop-blur-sm">
            <div class="container mx-auto px-6">
                <h2 class="text-4xl font-bold text-white text-center mb-12">
                    Why Our Directory Rocks! 🎉
                </h2>
                <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-8 text-center hover:bg-white/20 transition-all duration-300 hover:scale-105">
                        <div class="text-5xl mb-4">🏪</div>
                        <h3 class="text-xl font-bold text-white mb-3">Local Businesses</h3>
                        <p class="text-purple-100 leading-relaxed">Discover amazing shops, restaurants, and services right in your neighborhood!</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-8 text-center hover:bg-white/20 transition-all duration-300 hover:scale-105">
                        <div class="text-5xl mb-4">⭐</div>
                        <h3 class="text-xl font-bold text-white mb-3">Verified Quality</h3>
                        <p class="text-purple-100 leading-relaxed">All businesses are carefully reviewed to ensure you get the best experience!</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-8 text-center hover:bg-white/20 transition-all duration-300 hover:scale-105">
                        <div class="text-5xl mb-4">🚀</div>
                        <h3 class="text-xl font-bold text-white mb-3">Easy to Use</h3>
                        <p class="text-purple-100 leading-relaxed">Simple, fast, and fun way to explore what your community has to offer!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="py-16">
            <div class="container mx-auto px-6 text-center">
                <div class="bg-gradient-to-r from-pink-500/20 to-yellow-400/20 backdrop-blur-md rounded-3xl p-12 max-w-3xl mx-auto border border-white/10">
                    <h2 class="text-4xl font-bold text-white mb-6">Ready to Join the Fun? 🎊</h2>
                    <p class="text-xl text-purple-100 mb-8 leading-relaxed">Whether you're looking for businesses or want to list your own, we've got you covered!</p>
                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <a href="{{ route('businesses.index') }}" 
                           class="bg-yellow-400 hover:bg-yellow-300 text-purple-800 px-10 py-4 rounded-full font-bold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            Start Exploring 🔍
                        </a>
                        <a href="{{ route('business.onboard') }}" 
                           class="bg-pink-500 hover:bg-pink-400 text-white px-10 py-4 rounded-full font-bold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            List Your Business 📝
                        </a>
                    </div>
                </div>
            </div>
                 </section>

        <!-- Footer -->
        <footer class="mt-20 py-8 border-t border-white/20">
            <div class="container mx-auto px-6 text-center">
                <p class="text-purple-200">
                    Made with 💜 for awesome local communities | 
                    <span class="text-yellow-300">{{ config('app.name', 'Awesome Business Directory') }}</span>
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
