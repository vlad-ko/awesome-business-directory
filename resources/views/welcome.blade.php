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
                        <a href="{{ route('business.onboard.step', 1) }}" class="bg-yellow-400 hover:bg-yellow-300 text-purple-800 px-6 py-2 rounded-full font-bold transition-all duration-200 transform hover:scale-105 neon-glow">Join Directory</a>
                    </div>
                </div>
                </nav>
        </header>

        <!-- Hero Section -->
        <main class="container mx-auto px-6 py-12">
            <!-- Hero Content -->
            <div class="text-center mb-12">
                <h1 class="text-3xl lg:text-4xl xl:text-5xl font-bold text-white mb-6 retro-text leading-tight">
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
                    <a href="{{ route('business.onboard.step', 1) }}" 
                       class="bg-transparent border-2 border-yellow-300 text-yellow-300 hover:bg-yellow-300 hover:text-purple-800 px-8 py-4 rounded-full font-bold text-lg transition-all duration-200">
                        🚀 List Your Business
                    </a>
                </div>
            </div>
        </main>

        <!-- Full Width SVG - Outside Container -->
        <div class="w-full mb-16">
            <svg width="100%" height="300" viewBox="0 0 800 300" xmlns="http://www.w3.org/2000/svg" class="drop-shadow-2xl w-full" preserveAspectRatio="xMidYMid meet">
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
                        <rect width="800" height="180" fill="url(#skyGradient)"/>
                        
                        <!-- Sun -->
                        <circle cx="650" cy="60" r="30" fill="url(#sunGradient)" opacity="0.9"/>
                        <circle cx="650" cy="60" r="35" fill="none" stroke="#FFD700" stroke-width="2" opacity="0.5"/>
                        <circle cx="650" cy="60" r="40" fill="none" stroke="#FFD700" stroke-width="1" opacity="0.3"/>
                        
                        <!-- Clouds -->
                        <ellipse cx="100" cy="40" rx="25" ry="12" fill="white" opacity="0.8"/>
                        <ellipse cx="115" cy="35" rx="20" ry="10" fill="white" opacity="0.8"/>
                        <ellipse cx="250" cy="50" rx="30" ry="15" fill="white" opacity="0.7"/>
                        <ellipse cx="270" cy="45" rx="22" ry="11" fill="white" opacity="0.7"/>
                        <ellipse cx="500" cy="35" rx="28" ry="14" fill="white" opacity="0.6"/>
                        <ellipse cx="520" cy="30" rx="20" ry="10" fill="white" opacity="0.6"/>
                        
                        <!-- Ground -->
                        <rect x="0" y="180" width="800" height="120" fill="#90EE90"/>
                        
                        <!-- Street -->
                        <rect x="0" y="240" width="800" height="30" fill="#696969"/>
                        <rect x="0" y="253" width="800" height="3" fill="#FFFF00"/>
                        
                        <!-- Building 1 - Pizza Shop -->
                        <rect x="80" y="130" width="70" height="50" fill="#FF6B6B"/>
                        <rect x="88" y="138" width="12" height="15" fill="#87CEEB"/>
                        <rect x="105" y="138" width="12" height="15" fill="#87CEEB"/>
                        <rect x="122" y="138" width="12" height="15" fill="#87CEEB"/>
                        <rect x="95" y="158" width="25" height="22" fill="#8B4513"/>
                        <polygon points="80,130 115,110 150,130" fill="#FF1493"/>
                        <text x="115" y="105" text-anchor="middle" fill="#4c1d95" font-size="10" font-weight="bold">🍕 PIZZA</text>
                        
                        <!-- Building 2 - Coffee Shop -->
                        <rect x="170" y="140" width="60" height="40" fill="#4ECDC4"/>
                        <rect x="178" y="148" width="10" height="12" fill="#87CEEB"/>
                        <rect x="192" y="148" width="10" height="12" fill="#87CEEB"/>
                        <rect x="206" y="148" width="10" height="12" fill="#87CEEB"/>
                        <rect x="185" y="165" width="20" height="15" fill="#8B4513"/>
                        <polygon points="170,140 200,125 230,140" fill="#9B59B6"/>
                        <text x="200" y="120" text-anchor="middle" fill="#4c1d95" font-size="9" font-weight="bold">☕ CAFÉ</text>
                        
                        <!-- Building 3 - Bookstore -->
                        <rect x="250" y="125" width="65" height="55" fill="#F39C12"/>
                        <rect x="258" y="133" width="12" height="16" fill="#87CEEB"/>
                        <rect x="275" y="133" width="12" height="16" fill="#87CEEB"/>
                        <rect x="292" y="133" width="12" height="16" fill="#87CEEB"/>
                        <rect x="265" y="155" width="24" height="25" fill="#8B4513"/>
                        <polygon points="250,125 282.5,105 315,125" fill="#E74C3C"/>
                        <text x="282" y="100" text-anchor="middle" fill="#4c1d95" font-size="9" font-weight="bold">📚 BOOKS</text>
                        
                        <!-- Building 4 - Flower Shop -->
                        <rect x="335" y="135" width="55" height="45" fill="#FF69B4"/>
                        <rect x="343" y="143" width="10" height="14" fill="#87CEEB"/>
                        <rect x="358" y="143" width="10" height="14" fill="#87CEEB"/>
                        <rect x="373" y="143" width="10" height="14" fill="#87CEEB"/>
                        <rect x="350" y="162" width="20" height="18" fill="#8B4513"/>
                        <polygon points="335,135 362.5,120 390,135" fill="#32CD32"/>
                        <text x="362" y="115" text-anchor="middle" fill="#4c1d95" font-size="8" font-weight="bold">🌸 FLOWERS</text>
                        
                        <!-- Building 5 - Bakery -->
                        <rect x="410" y="145" width="50" height="35" fill="#DDA0DD"/>
                        <rect x="418" y="153" width="8" height="12" fill="#87CEEB"/>
                        <rect x="430" y="153" width="8" height="12" fill="#87CEEB"/>
                        <rect x="442" y="153" width="8" height="12" fill="#87CEEB"/>
                        <rect x="425" y="168" width="16" height="12" fill="#8B4513"/>
                        <polygon points="410,145 435,130 460,145" fill="#FFD700"/>
                        <text x="435" y="125" text-anchor="middle" fill="#4c1d95" font-size="8" font-weight="bold">🥖 BAKERY</text>
                        
                        <!-- Building 6 - New Shop -->
                        <rect x="480" y="140" width="55" height="40" fill="#9370DB"/>
                        <rect x="488" y="148" width="10" height="12" fill="#87CEEB"/>
                        <rect x="503" y="148" width="10" height="12" fill="#87CEEB"/>
                        <rect x="518" y="148" width="10" height="12" fill="#87CEEB"/>
                        <rect x="495" y="165" width="18" height="15" fill="#8B4513"/>
                        <polygon points="480,140 507.5,125 535,140" fill="#FF6347"/>
                        <text x="507" y="120" text-anchor="middle" fill="#4c1d95" font-size="8" font-weight="bold">🛍️ SHOP</text>
                        
                        <!-- Building 7 - Gym -->
                        <rect x="555" y="135" width="60" height="45" fill="#32CD32"/>
                        <rect x="563" y="143" width="11" height="14" fill="#87CEEB"/>
                        <rect x="579" y="143" width="11" height="14" fill="#87CEEB"/>
                        <rect x="595" y="143" width="11" height="14" fill="#87CEEB"/>
                        <rect x="572" y="162" width="22" height="18" fill="#8B4513"/>
                        <polygon points="555,135 585,120 615,135" fill="#FF4500"/>
                        <text x="585" y="115" text-anchor="middle" fill="#4c1d95" font-size="8" font-weight="bold">💪 GYM</text>
                        
                        <!-- Background Trees -->
                        <circle cx="40" cy="170" r="15" fill="#228B22"/>
                        <rect x="38" y="170" width="4" height="18" fill="#8B4513"/>
                        
                        <circle cx="680" cy="165" r="12" fill="#228B22"/>
                        <rect x="678" y="165" width="4" height="15" fill="#8B4513"/>
                        
                        <circle cx="750" cy="168" r="14" fill="#228B22"/>
                        <rect x="748" y="168" width="4" height="17" fill="#8B4513"/>
                        
                        <!-- Car -->
                        <g transform="translate(300,220)">
                            <!-- Car body -->
                            <rect x="0" y="0" width="45" height="15" rx="3" fill="#FF6347"/>
                            <!-- Car top -->
                            <rect x="8" y="-8" width="25" height="8" rx="2" fill="#FF4500"/>
                            <!-- Windows -->
                            <rect x="10" y="-6" width="8" height="6" fill="#87CEEB" opacity="0.7"/>
                            <rect x="20" y="-6" width="8" height="6" fill="#87CEEB" opacity="0.7"/>
                            <!-- Wheels -->
                            <circle cx="8" cy="18" r="4" fill="#2F4F4F"/>
                            <circle cx="37" cy="18" r="4" fill="#2F4F4F"/>
                            <circle cx="8" cy="18" r="2" fill="#C0C0C0"/>
                            <circle cx="37" cy="18" r="2" fill="#C0C0C0"/>
                        </g>
                        
                        <!-- Fire Hydrant -->
                        <g transform="translate(150,225)">
                            <!-- Base -->
                            <rect x="0" y="10" width="8" height="15" fill="#FF0000"/>
                            <!-- Top -->
                            <rect x="-1" y="8" width="10" height="4" fill="#FF0000"/>
                            <!-- Cap -->
                            <rect x="1" y="6" width="6" height="3" fill="#FFD700"/>
                            <!-- Side outlets -->
                            <rect x="-2" y="12" width="3" height="2" fill="#C0C0C0"/>
                            <rect x="7" y="12" width="3" height="2" fill="#C0C0C0"/>
                        </g>
                        
                        <!-- Dog Walker -->
                        <g transform="translate(450,210)">
                            <!-- Person -->
                            <circle cx="0" cy="-12" r="4" fill="#FDBCB4"/>
                            <ellipse cx="0" cy="-4" rx="5" ry="8" fill="#4169E1"/>
                            <ellipse cx="-8" cy="-6" rx="2" ry="6" fill="#FDBCB4" transform="rotate(-15)"/>
                            <ellipse cx="8" cy="-6" rx="2" ry="6" fill="#FDBCB4" transform="rotate(15)"/>
                            <ellipse cx="-3" cy="8" rx="2" ry="8" fill="#4169E1"/>
                            <ellipse cx="3" cy="8" rx="2" ry="8" fill="#4169E1"/>
                            <!-- Dog -->
                            <ellipse cx="15" cy="8" rx="6" ry="4" fill="#8B4513"/>
                            <circle cx="20" cy="6" r="3" fill="#8B4513"/>
                            <ellipse cx="22" cy="5" rx="1" ry="2" fill="#8B4513"/>
                            <ellipse cx="22" cy="3" rx="1" ry="2" fill="#8B4513"/>
                            <rect x="9" y="10" width="1" height="4" fill="#8B4513"/>
                            <rect x="12" y="10" width="1" height="4" fill="#8B4513"/>
                            <rect x="18" y="10" width="1" height="4" fill="#8B4513"/>
                            <rect x="21" y="10" width="1" height="4" fill="#8B4513"/>
                            <!-- Leash -->
                            <path d="M 6,-2 Q 12,2 15,6" stroke="#000000" stroke-width="1" fill="none"/>
                        </g>
                        
                        <!-- Roller Skater (repositioned) -->
                        <g transform="translate(550,215)">
                            <!-- Body -->
                            <ellipse cx="0" cy="0" rx="6" ry="9" fill="#FF1493"/>
                            <!-- Head -->
                            <circle cx="0" cy="-12" r="4" fill="#FDBCB4"/>
                            <!-- Hair -->
                            <path d="M -4,-16 Q 0,-20 4,-16" fill="#FFD700"/>
                            <!-- Arms -->
                            <ellipse cx="-8" cy="-3" rx="2" ry="6" fill="#FDBCB4" transform="rotate(-20)"/>
                            <ellipse cx="8" cy="-3" rx="2" ry="6" fill="#FDBCB4" transform="rotate(20)"/>
                            <!-- Legs -->
                            <ellipse cx="-4" cy="12" rx="2" ry="8" fill="#4169E1"/>
                            <ellipse cx="4" cy="12" rx="2" ry="8" fill="#4169E1"/>
                            <!-- Roller Skates -->
                            <ellipse cx="-4" cy="22" rx="6" ry="3" fill="#FF6347"/>
                            <ellipse cx="4" cy="22" rx="6" ry="3" fill="#FF6347"/>
                            <!-- Wheels -->
                            <circle cx="-7" cy="24" r="1.5" fill="#FFD700"/>
                            <circle cx="-1" cy="24" r="1.5" fill="#FFD700"/>
                            <circle cx="1" cy="24" r="1.5" fill="#FFD700"/>
                            <circle cx="7" cy="24" r="1.5" fill="#FFD700"/>
                        </g>
                        
                        <!-- Motion lines -->
                        <path d="M 540,220 Q 535,215 530,220" stroke="#FF1493" stroke-width="1" fill="none" opacity="0.6"/>
                        <path d="M 535,225 Q 530,220 525,225" stroke="#FF1493" stroke-width="1" fill="none" opacity="0.4"/>
                        
                        <!-- Street lamp -->
                        <rect x="200" y="185" width="2" height="40" fill="#2F4F4F"/>
                        <circle cx="201" y="182" r="6" fill="#FFD700" opacity="0.8"/>
                        
                        <!-- Foreground Tree (Large) -->
                        <g transform="translate(20,200)">
                            <rect x="0" y="0" width="8" height="30" fill="#8B4513"/>
                            <circle cx="4" cy="-5" r="18" fill="#228B22"/>
                            <circle cx="-8" cy="-8" r="12" fill="#228B22"/>
                            <circle cx="16" cy="-10" r="14" fill="#228B22"/>
                            <circle cx="4" cy="-20" r="15" fill="#228B22"/>
                        </g>
                        
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

        <!-- Features Section -->
        <section class="py-16 bg-gradient-to-br from-slate-800 via-blue-900 to-indigo-900">
            <div class="container mx-auto px-6">
                <h2 class="text-4xl font-bold text-gray-200 text-center mb-12">
                    Why Our Directory Rocks! 🎉
                </h2>
                <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-8 text-center hover:bg-white/20 transition-all duration-300 hover:scale-105">
                        <div class="text-5xl mb-4">🏪</div>
                        <h3 class="text-xl font-bold text-gray-200 mb-3">Local Businesses</h3>
                        <p class="text-gray-300 leading-relaxed">Discover amazing shops, restaurants, and services right in your neighborhood!</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-8 text-center hover:bg-white/20 transition-all duration-300 hover:scale-105">
                        <div class="text-5xl mb-4">⭐</div>
                        <h3 class="text-xl font-bold text-gray-200 mb-3">Verified Quality</h3>
                        <p class="text-gray-300 leading-relaxed">All businesses are carefully reviewed to ensure you get the best experience!</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-8 text-center hover:bg-white/20 transition-all duration-300 hover:scale-105">
                        <div class="text-5xl mb-4">🚀</div>
                        <h3 class="text-xl font-bold text-gray-200 mb-3">Easy to Use</h3>
                        <p class="text-gray-300 leading-relaxed">Simple, fast, and fun way to explore what your community has to offer!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="py-16 bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900">
            <div class="container mx-auto px-6 text-center">
                <div class="bg-white/10 backdrop-blur-md rounded-3xl p-12 max-w-3xl mx-auto border border-white/20">
                    <h2 class="text-4xl font-bold text-gray-200 mb-6">Ready to Join the Fun? 🎊</h2>
                    <p class="text-xl text-gray-300 mb-8 leading-relaxed">Whether you're looking for businesses or want to list your own, we've got you covered!</p>
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
        <footer class="py-12 bg-gradient-to-r from-gray-900 via-slate-800 to-gray-900 border-t border-white/10">
            <div class="container mx-auto px-6 text-center">
                <p class="text-gray-300 text-lg">
                    Made with 💜 for awesome local communities | 
                    <span class="text-yellow-400 font-semibold">{{ config('app.name', 'Awesome Business Directory') }}</span>
                </p>
            </div>
        </footer>
    </div>
    </body>
</html>
