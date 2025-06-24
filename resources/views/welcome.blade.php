<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }} - TOTALLY RADICAL Business Directory!</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #f5576c);
                background-size: 400% 400%;
                animation: gradientShift 12s ease-in-out infinite;
                overflow-x: hidden;
            }
            
            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            
            .retro-text {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-weight: 800;
                text-shadow: 2px 2px 0px #ff1493, 4px 4px 0px rgba(0,0,0,0.3);
                color: #ffffff;
                letter-spacing: -0.025em;
            }
            
            .neon-button {
                background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
                border: 2px solid #ffffff;
                color: #ffffff;
                font-weight: bold;
                text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            }
            
            .neon-button:hover {
                background: linear-gradient(45deg, #4ecdc4, #ff6b6b);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            }
            
            /* Readable content text */
            .content-text {
                font-weight: 500;
                line-height: 1.6;
                letter-spacing: -0.025em;
            }
            
            .button-text {
                font-weight: 600;
                letter-spacing: -0.025em;
            }
            
            .rainbow-text {
                background: linear-gradient(45deg, #ff0000, #ff8000, #ffff00, #80ff00, #00ff00, #00ff80, #00ffff, #0080ff, #0000ff, #8000ff, #ff0080, #ff0000);
                background-size: 200% 200%;
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
                animation: rainbow 3s ease-in-out infinite;
            }
            
            @keyframes rainbow {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            
            .retro-box {
                background: linear-gradient(45deg, #667eea, #764ba2);
                border: 3px solid rgba(255,255,255,0.3);
                border-radius: 15px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.1);
                backdrop-filter: blur(10px);
            }
            
            .marquee {
                overflow: hidden;
                white-space: nowrap;
                background: #000;
                color: #00ff00;
                padding: 10px;
                font-family: monospace;
                font-size: 18px;
                font-weight: bold;
            }
            
            .marquee span {
                display: inline-block;
                animation: marquee 15s linear infinite;
            }
            
            @keyframes marquee {
                0% { transform: translateX(100%); }
                100% { transform: translateX(-100%); }
            }
            
            .retro-nav {
                background: linear-gradient(90deg, #ff1493, #00ffff, #ffff00);
                border-bottom: 5px solid #ffffff;
                padding: 15px 0;
            }
            
            .cyber-grid {
                background-image: 
                    linear-gradient(rgba(0,255,255,0.3) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(0,255,255,0.3) 1px, transparent 1px);
                background-size: 20px 20px;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                animation: gridMove 10s linear infinite;
            }
            
            @keyframes gridMove {
                0% { transform: translate(0, 0); }
                100% { transform: translate(20px, 20px); }
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
        </style>
    </head>
<body>
    <!-- Cyber Grid Background -->
    <div class="cyber-grid"></div>
    
    <!-- Animated Stars -->
    <div class="star" style="top: 10%; left: 20%;">⭐</div>
    <div class="star" style="top: 20%; left: 80%; animation-delay: 0.5s;">✨</div>
    <div class="star" style="top: 60%; left: 10%; animation-delay: 1s;">🌟</div>
    <div class="star" style="top: 80%; left: 70%; animation-delay: 1.5s;">⭐</div>
    <div class="star" style="top: 40%; left: 90%; animation-delay: 2s;">✨</div>

    <!-- Marquee Text -->
    <div class="marquee">
        <span>🎉 WELCOME TO THE MOST AWESOME BUSINESS DIRECTORY ON THE INFORMATION SUPERHIGHWAY! 🎉 💫 TOTALLY RADICAL BUSINESSES AWAIT YOU! 💫 🚀 SURF THE WEB LIKE IT'S 1999! 🚀</span>
    </div>

    <!-- Header -->
    <header class="retro-nav relative z-10">
        <nav class="container mx-auto px-6">
            <div class="flex items-center justify-between">
                <div class="retro-text text-3xl font-bold glow">
                    🏪 AWESOME BIZZZ DIRECTORY 🏪
                </div>
                <div class="space-x-4">
                    <a href="{{ route('businesses.index') }}" class="button-text neon-button px-6 py-3 rounded-full transition-all duration-300">
                        🔍 BROWSE SHOPS
                    </a>
                    <a href="{{ route('business.onboard.step', 1) }}" class="button-text neon-button px-6 py-3 rounded-full transition-all duration-300">
                        🚀 JOIN NOW!
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8 relative z-10">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 class="text-5xl lg:text-6xl font-bold retro-text mb-6 leading-tight">
                ✨ Discover Your ✨
                <br>
                <span class="rainbow-text text-6xl lg:text-7xl">Neighborhood</span>
                <br>
                💎 Gems! 💎
            </h1>
            
            <div class="retro-box p-8 mb-8 mx-auto max-w-4xl">
                <p class="content-text text-xl text-white mb-4">
                    🌈 Find the most excellent local shops! 🌈
                </p>
                <p class="content-text text-lg text-yellow-200">
                    Restaurants • Stores • Services • And more cool stuff! 
                </p>
                <div class="mt-4">
                    <span class="text-2xl">🎊</span>
                    <span class="content-text text-white text-lg">Totally free to use!</span>
                    <span class="text-2xl">🎊</span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-12">
                <a href="{{ route('businesses.index') }}" 
                   class="button-text neon-button px-8 py-4 rounded-full text-lg transition-all duration-300">
                    🔥 Explore Now! 🔥
                </a>
                <a href="{{ route('business.onboard.step', 1) }}" 
                   class="neon-button px-8 py-4 rounded-full font-bold text-lg transition-all duration-300">
                    💫 List Your Business! 💫
                </a>
            </div>
        </div>

        <!-- Features Section -->
        <div class="grid md:grid-cols-3 gap-8 mb-12">
            <div class="retro-box p-6 text-center transform hover:scale-105 transition-all duration-300">
                <div class="text-6xl mb-4 glow">🍕</div>
                <h3 class="text-2xl font-bold text-white mb-2">RESTAURANTS</h3>
                <p class="text-yellow-300 font-bold">Find the most tubular eats in town!</p>
            </div>
            
            <div class="retro-box p-6 text-center transform hover:scale-105 transition-all duration-300">
                <div class="text-6xl mb-4 glow">🛍️</div>
                <h3 class="text-2xl font-bold text-white mb-2">SHOPPING</h3>
                <p class="text-yellow-300 font-bold">Discover radical retail therapy!</p>
            </div>
            
            <div class="retro-box p-6 text-center transform hover:scale-105 transition-all duration-300">
                <div class="text-6xl mb-4 glow">🔧</div>
                <h3 class="text-2xl font-bold text-white mb-2">SERVICES</h3>
                <p class="text-yellow-300 font-bold">Get totally awesome help!</p>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="retro-box p-8 mb-12 text-center">
            <h2 class="text-4xl font-bold retro-text mb-6">📊 TOTALLY AWESOME STATS! 📊</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <div class="text-5xl font-bold rainbow-text">{{ \App\Models\Business::count() }}+</div>
                    <div class="text-xl text-white font-bold">RADICAL BUSINESSES</div>
                </div>
                <div>
                    <div class="text-5xl font-bold rainbow-text">∞</div>
                    <div class="text-xl text-white font-bold">HAPPY CUSTOMERS</div>
                </div>
                <div>
                    <div class="text-5xl font-bold rainbow-text">24/7</div>
                    <div class="text-xl text-white font-bold">TOTALLY ONLINE</div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center retro-box p-8">
            <h2 class="text-4xl font-bold retro-text mb-4">
                🎯 READY TO GET STARTED? 🎯
            </h2>
            <p class="text-xl text-white font-bold mb-6">
                Join the most excellent business directory on the World Wide Web!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('businesses.index') }}" 
                   class="neon-button px-8 py-4 rounded-full font-bold text-xl transition-all duration-300">
                    🌟 START BROWSING 🌟
                </a>
                <a href="{{ route('business.onboard.step', 1) }}" 
                   class="neon-button px-8 py-4 rounded-full font-bold text-xl transition-all duration-300">
                    🚀 BECOME A MEMBER 🚀
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="retro-nav mt-12 py-8">
        <div class="container mx-auto px-6 text-center">
            <div class="retro-text text-2xl mb-4">
                🌈 AWESOME BUSINESS DIRECTORY 🌈
            </div>
            <p class="text-white font-bold">
                © {{ date('Y') }} - SURFING THE WEB SINCE THE 90s! 🏄‍♂️
            </p>
            <div class="mt-4">
                <span class="text-yellow-300 font-bold">⚡ POWERED BY PURE AWESOMENESS ⚡</span>
            </div>
        </div>
    </footer>

    <!-- Floating Elements -->
    <div style="position: fixed; top: 10%; right: 5%; z-index: 100;">
        <div class="text-4xl glow">🎪</div>
    </div>
    <div style="position: fixed; bottom: 10%; left: 5%; z-index: 100;">
        <div class="text-4xl glow">🎨</div>
    </div>
</body>
</html> 