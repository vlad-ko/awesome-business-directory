<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <!-- Sentry Distributed Tracing Meta Tag -->
        <meta name="sentry-trace" content="{{ \Sentry\SentrySdk::getCurrentHub()->getTransaction()?->toTraceparent() ?? '' }}">

        <title>Discover Amazing Local Businesses - {{ config('app.name', 'Laravel') }}</title>
        
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Sentry Configuration -->
        <script>
            window.sentryConfig = {
                dsn: '{{ config('sentry.dsn') }}',
                environment: '{{ config('sentry.environment', app()->environment()) }}',
                release: '{{ config('sentry.release') }}',
                tracesSampleRate: 1.0,
                enableTracing: true,
                @auth
                user: {
                    id: '{{ auth()->user()->id }}',
                    email: '{{ auth()->user()->email }}',
                    is_admin: {{ auth()->user()->is_admin ? 'true' : 'false' }}
                },
                @else
                user: null,
                @endauth
            };

            // User context for Sentry
            @auth
            window.userContext = {
                id: {{ auth()->user()->id }},
                email: '{{ auth()->user()->email }}',
                is_admin: {{ auth()->user()->is_admin ? 'true' : 'false' }}
            };
            @else
            window.userContext = null;
            @endauth
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .gradient-bg {
                background: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #f5576c);
                background-size: 400% 400%;
                animation: gradientShift 12s ease-in-out infinite;
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
            
            .neon-glow {
                filter: drop-shadow(0 0 10px #00ffff) drop-shadow(0 0 20px #ff1493);
            }
        </style>
    </head>
<body class="gradient-bg" x-data="welcomePage">
    <!-- Navigation -->
    <nav class="retro-nav bg-gradient-to-tr from-purple-600 to-pink-600 shadow-lg p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="retro-text text-2xl lg:text-4xl neon-glow">
                🏪 Awesome Business Directory 🏪
            </div>
            <div class="space-x-4">
                <a href="{{ route('businesses.index') }}" 
                   class="bg-gradient-to-tr from-blue-500 to-cyan-400 text-white px-6 py-3 rounded-full font-bold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-500"
                   x-track='{"action": "browse_businesses", "source": "nav", "position": "primary"}'>
                    Browse Businesses
                </a>
                <a href="{{ route('business.onboard.step', 1) }}" 
                   class="bg-gradient-to-tr from-green-500 to-emerald-400 text-white px-6 py-3 rounded-full font-bold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-green-500"
                   x-track='{"action": "add_business", "source": "nav", "position": "secondary"}'>
                    Join Directory
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="backdrop-blur-md bg-white/10 py-16">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold retro-text neon-glow mb-6 leading-tight">
                ✨ Discover Your ✨
                <br>
                <span class="text-5xl lg:text-7xl bg-gradient-to-tr from-yellow-400 via-red-500 to-pink-500 bg-clip-text text-transparent">
                    Neighborhood
                </span>
                <br>
                💎 Gems! 💎
            </h1>
            
            <p class="text-xl text-white mb-4 max-w-2xl mx-auto leading-8">
                Find amazing local shops, restaurants, and services that make your community awesome
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                <a href="{{ route('businesses.index') }}" 
                   class="bg-gradient-to-tr from-blue-600 to-purple-600 text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-500"
                   x-track='{"action": "browse_businesses", "source": "hero_cta", "position": "primary"}'>
                    🔍 Explore Businesses
                </a>
                <a href="{{ route('business.onboard.step', 1) }}" 
                   class="bg-gradient-to-tr from-green-600 to-blue-600 text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-green-500"
                   x-track='{"action": "add_business", "source": "hero_cta", "position": "secondary"}'>
                    🚀 List Your Business
                </a>
            </div>
        </div>
    </section>

    <!-- Interactive Demo Section -->
    <section class="backdrop-blur-md bg-white/10 py-12" x-data="{ demoStep: 1 }">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-white mb-8">Interactive Demo</h2>
            
            <div class="max-w-4xl mx-auto bg-white/20 backdrop-blur rounded-lg p-8">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-white mb-2">Demo Progress</h3>
                    <div class="text-white">
                        <span x-text="`${demoStep}`"></span>/<span>3</span>
                        <span class="hidden" x-text="`demoStep/3`"></span>
                    </div>
                </div>
                
                <div x-show="demoStep === 1" class="space-y-4">
                    <h3 class="text-2xl font-bold text-white">Step 1: Search for Businesses</h3>
                    <input type="text" placeholder="Try searching for 'pizza'..." 
                           class="w-full p-3 rounded-lg"
                           x-track='{"action": "demo_search", "step": 1}'>
                    <button @click="demoStep = 2" 
                            class="bg-blue-600 text-white px-6 py-3 rounded-lg font-bold"
                            x-track='{"action": "demo_next", "step": 1}'>
                        Next Step →
                    </button>
                </div>
                
                <div x-show="demoStep === 2" class="space-y-4">
                    <h3 class="text-2xl font-bold text-white">Step 2: Add Your Business</h3>
                    <input type="text" placeholder="Enter your business name..." 
                           class="w-full p-3 rounded-lg"
                           x-track='{"action": "demo_business_name", "step": 2}'>
                    <button @click="demoStep = 3" 
                            class="bg-green-600 text-white px-6 py-3 rounded-lg font-bold"
                            x-track='{"action": "demo_next", "step": 2}'>
                        Next Step →
                    </button>
                </div>
                
                <div x-show="demoStep === 3" class="space-y-4">
                    <h3 class="text-2xl font-bold text-white">Step 3: Contact Information</h3>
                    <div class="text-green-300 text-xl font-bold">Demo Completed!</div>
                    <button @click="demoStep = 1" 
                            class="bg-purple-600 text-white px-6 py-3 rounded-lg font-bold"
                            x-track='{"action": "demo_complete", "step": 3}'>
                        Start Over
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Neighborhood SVG Illustration -->
    <section class="py-12">
        <div class="container mx-auto px-6">
            <svg width="100%" height="300" viewBox="0 0 800 300" class="mx-auto">
                <!-- Buildings -->
                <rect x="50" y="150" width="80" height="120" fill="#4F46E5" rx="5"/>
                <text x="90" y="175" fill="white" text-anchor="middle" class="font-bold">🍕 PIZZA</text>
                
                <rect x="150" y="130" width="80" height="140" fill="#7C3AED" rx="5"/>
                <text x="190" y="155" fill="white" text-anchor="middle" class="font-bold">☕ CAFÉ</text>
                
                <rect x="250" y="140" width="80" height="130" fill="#EC4899" rx="5"/>
                <text x="290" y="165" fill="white" text-anchor="middle" class="font-bold">📚 BOOKS</text>
                
                <rect x="350" y="135" width="80" height="135" fill="#10B981" rx="5"/>
                <text x="390" y="160" fill="white" text-anchor="middle" class="font-bold">🌸 FLOWERS</text>
                
                <rect x="450" y="145" width="80" height="125" fill="#F59E0B" rx="5"/>
                <text x="490" y="170" fill="white" text-anchor="middle" class="font-bold">🥖 BAKERY</text>
                
                <!-- Ground -->
                <rect x="0" y="270" width="800" height="30" fill="#065F46"/>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section class="backdrop-blur-md bg-white/10 py-16">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center retro-text neon-glow mb-12">
                Why Our Directory Rocks!
            </h2>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="backdrop-blur bg-white/20 rounded-lg p-8 text-center shadow-lg">
                    <div class="text-6xl mb-4">🏪</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Local Businesses</h3>
                    <p class="text-white">Discover amazing shops, restaurants, and services right in your neighborhood!</p>
                </div>
                
                <div class="backdrop-blur bg-white/20 rounded-lg p-8 text-center shadow-lg">
                    <div class="text-6xl mb-4">✅</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Verified Quality</h3>
                    <p class="text-white">All businesses are carefully reviewed to ensure you get the best experience!</p>
                </div>
                
                <div class="backdrop-blur bg-white/20 rounded-lg p-8 text-center shadow-lg">
                    <div class="text-6xl mb-4">🚀</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Easy to Use</h3>
                    <p class="text-white">Simple, fast, and fun way to explore what your community has to offer!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Technical Features Section -->
    <section class="backdrop-blur-md bg-white/10 py-16">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-white mb-8">Built with Modern Technology</h2>
            <div class="text-center text-white space-y-4 max-w-4xl mx-auto">
                <p>Built with Laravel for robust backend functionality</p>
                <p>Alpine.js for smooth interactions and seamless user experience</p>
                <p>Features comprehensive error tracking and performance monitoring via Sentry</p>
                <p>Comprehensive analytics and business insights for optimal user experience</p>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="backdrop-blur-md bg-white/10 py-16">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-4xl font-bold retro-text neon-glow mb-6">
                Ready to Join the Fun?
            </h2>
            <p class="text-xl text-white mb-8 max-w-2xl mx-auto">
                Whether you're looking for businesses or want to list your own, we've got you covered!
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('businesses.index') }}" 
                   class="bg-gradient-to-tr from-blue-600 to-purple-600 text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-500"
                   x-track='{"action": "browse_businesses", "source": "bottom_cta", "position": "secondary"}'>
                    Start Exploring 🔍
                </a>
                <a href="{{ route('business.onboard') }}" 
                   class="bg-gradient-to-tr from-green-600 to-blue-600 text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-green-500"
                   x-track='{"action": "add_business", "source": "bottom_cta", "position": "primary"}'>
                    List Your Business 📝
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="backdrop-blur-md bg-black/30 py-8">
        <div class="container mx-auto px-6 text-center">
            <p class="text-white mb-2">Made with 💜 for awesome local communities</p>
            <p class="text-white/70">© {{ date('Y') }} Awesome Business Directory</p>
        </div>
    </footer>

    <!-- Accessibility features -->
    <div aria-hidden="true" class="sr-only">Screen reader accessible content</div>
</body>
</html> 