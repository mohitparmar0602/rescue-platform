<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rescue Coordination Platform | Mission Critical Intelligence</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-[#0a0a0b] text-slate-200 selection:bg-red-500/30 overflow-x-hidden">
    <!-- Background Decor -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-red-600/10 blur-[120px] rounded-full"></div>
        <div class="absolute top-[20%] -right-[10%] w-[30%] h-[50%] bg-blue-600/10 blur-[120px] rounded-full"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-[0.03] mix-blend-overlay"></div>
    </div>

    <!-- Navigation -->
    <nav class="relative z-50 flex items-center justify-between px-6 py-6 mx-auto max-w-7xl lg:px-12">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 bg-red-600 rounded-lg shadow-lg shadow-red-600/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <span class="text-xl font-bold tracking-tight text-white font-display">RESCUE<span class="text-red-500">CORE</span></span>
        </div>

        <div class="items-center hidden gap-8 md:flex">
            @if (Route::has('login'))
                <div class="flex items-center gap-6">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium transition-colors hover:text-white">Command Center</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium transition-colors hover:text-white">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2 text-sm font-semibold text-white transition-all border border-white/10 rounded-full bg-white/5 hover:bg-white/10">Responder Access</a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative z-10 pt-16 pb-24 overflow-hidden lg:pt-24 lg:pb-32">
        <div class="px-6 mx-auto max-w-7xl lg:px-12">
            <div class="grid items-center gap-16 lg:grid-cols-2">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 mb-8 border rounded-full bg-red-500/5 border-red-500/20">
                        <span class="status-pulse">
                            <span class="status-pulse-dot"></span>
                            <span class="status-pulse-inner"></span>
                        </span>
                        <span class="text-xs font-semibold tracking-wider uppercase text-red-400/90">System Status: Operational</span>
                    </div>
                    
                    <h1 class="mb-6 text-5xl font-bold leading-tight text-white lg:text-7xl font-display">
                        Unified Intelligence for <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-amber-500">Rapid Response.</span>
                    </h1>
                    
                    <p class="mb-10 text-lg leading-relaxed text-slate-400">
                        Coordinate agencies, track resources, and manage critical incidents in real-time. The next generation of disaster response management is here.
                    </p>

                    <div class="flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('agency.register') }}" class="btn-emergency group">
                            <span>Register Agency</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#features" class="btn-secondary">
                            <span>Platform Tour</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-3 gap-8 mt-16 border-t border-white/5 pt-12">
                        <div>
                            <div class="text-2xl font-bold text-white">24/7</div>
                            <div class="text-xs text-slate-500 uppercase tracking-widest mt-1">Monitoring</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">Real-time</div>
                            <div class="text-xs text-slate-500 uppercase tracking-widest mt-1">Geofencing</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">E2E</div>
                            <div class="text-xs text-slate-500 uppercase tracking-widest mt-1">Encryption</div>
                        </div>
                    </div>
                </div>

                <div class="relative lg:block">
                    <div class="absolute -inset-4 bg-gradient-to-r from-red-600/20 to-blue-600/20 blur-3xl opacity-30 rounded-full animate-pulse"></div>
                    <div class="relative overflow-hidden shadow-2xl glass-card border-white/10 animate-float">
                        <img src="{{ Vite::asset('resources/images/hero.png') }}" alt="Command Center Dashboard" class="w-full h-auto">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0b] via-transparent to-transparent opacity-60"></div>
                        
                        <!-- Mini Overlay Cards -->
                        <div class="absolute bottom-6 left-6 right-6">
                            <div class="p-4 glass-card border-white/10 bg-white/5">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-white uppercase tracking-wider">Active Alerts</span>
                                    <span class="px-2 py-0.5 bg-red-500/20 text-red-400 text-[10px] font-bold rounded uppercase">High Priority</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="w-full h-1 bg-white/10 rounded-full overflow-hidden">
                                        <div class="w-2/3 h-full bg-red-500"></div>
                                    </div>
                                    <p class="text-[10px] text-slate-400">Incident #294: Structural Fire Response in Sector 7</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white/[0.02] border-y border-white/5 relative">
        <div class="px-6 mx-auto max-w-7xl lg:px-12">
            <div class="max-w-2xl mb-16">
                <h2 class="mb-4 text-3xl font-bold text-white lg:text-4xl font-display">Engineered for the Unpredictable</h2>
                <p class="text-lg text-slate-400">Our platform provides the tools necessary for modern emergency services to collaborate and save lives when every second counts.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <!-- Feature 1 -->
                <div class="p-8 glass-card glass-card-hover group">
                    <div class="w-12 h-12 mb-6 bg-red-500/10 rounded-xl flex items-center justify-center text-red-500 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-white font-display">Precision Mapping</h3>
                    <p class="text-slate-400">Real-time geospatial tracking of all field units and incidents with historical playback capabilities.</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-8 glass-card glass-card-hover group">
                    <div class="w-12 h-12 mb-6 bg-blue-500/10 rounded-xl flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-white font-display">Resource Inventory</h3>
                    <p class="text-slate-400">Manage specialized equipment, personnel availability, and cross-agency resource requests seamlessly.</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-8 glass-card glass-card-hover group">
                    <div class="w-12 h-12 mb-6 bg-amber-500/10 rounded-xl flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-white font-display">Secure Comms</h3>
                    <p class="text-slate-400">Instant encrypted messaging and alert broadcasting for direct communication between admins and agencies.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-24 relative overflow-hidden">
        <div class="px-6 mx-auto max-w-7xl lg:px-12 text-center">
            <div class="max-w-3xl mx-auto p-12 glass-card border-red-500/20 bg-gradient-to-br from-red-600/5 to-transparent relative">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 px-4 py-1 bg-red-600 rounded-full text-[10px] font-bold text-white uppercase tracking-widest">Enrolling Now</div>
                <h2 class="mb-6 text-4xl font-bold text-white font-display">Empower Your First Responders</h2>
                <p class="mb-10 text-lg text-slate-400">Join the growing network of agencies using RescueCore to optimize their emergency response operations.</p>
                <div class="flex flex-col gap-4 sm:flex-row justify-center">
                    <a href="{{ route('agency.register') }}" class="btn-emergency">Register Your Agency</a>
                    <a href="{{ route('login') }}" class="btn-secondary">Portal Login</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 border-t border-white/5 relative z-10">
        <div class="px-6 mx-auto max-w-7xl lg:px-12 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex items-center gap-3">
                <span class="text-lg font-bold tracking-tight text-white font-display">RESCUE<span class="text-red-500">CORE</span></span>
                <span class="text-xs text-slate-500 border-l border-white/10 pl-3">© 2026 Mission Critical Systems</span>
            </div>
            <div class="flex gap-8">
                <a href="#" class="text-xs text-slate-500 hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="text-xs text-slate-500 hover:text-white transition-colors">Terms of Service</a>
                <a href="#" class="text-xs text-slate-500 hover:text-white transition-colors">Documentation</a>
            </div>
        </div>
    </footer>
</body>
</html>
