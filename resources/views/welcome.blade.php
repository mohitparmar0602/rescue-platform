<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RescueCore | Admin Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#0a0a0b] text-slate-200 antialiased">
    <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-red-500/30">
        <!-- Background Decor -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-red-600/5 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-[10%] -right-[10%] w-[30%] h-[50%] bg-blue-600/5 blur-[120px] rounded-full"></div>
        </div>

        <main class="relative z-10 w-full max-w-2xl px-6 text-center">
            <div class="flex flex-col items-center mb-12">
                <div class="flex items-center justify-center w-16 h-16 bg-red-600 rounded-2xl shadow-2xl shadow-red-600/20 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-4xl md:text-6xl font-bold text-white tracking-tight font-display mb-4">
                    RESCUE<span class="text-red-500">CORE</span>
                </h1>
                <p class="text-slate-400 text-lg md:text-xl max-w-lg mx-auto leading-relaxed">
                    Disaster Response Coordination Platform. <br>
                    <span class="text-slate-500 text-sm font-medium uppercase tracking-widest">Administrative Backend</span>
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="flex items-center justify-center px-8 py-4 bg-white text-black font-bold rounded-xl hover:bg-slate-200 transition-all">
                        Open Command Center
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex items-center justify-center px-8 py-4 bg-white text-black font-bold rounded-xl hover:bg-slate-200 transition-all">
                        Log in
                    </a>
                    <a href="{{ route('agency.register') }}" class="flex items-center justify-center px-8 py-4 bg-[#1a1a1c] text-white border border-white/10 font-bold rounded-xl hover:bg-white/5 transition-all">
                        Register Agency
                    </a>
                @endauth
            </div>

            @auth
                <p class="mt-8 text-sm text-slate-500">
                    Logged in as <span class="text-slate-300 font-semibold">{{ Auth::user()->name }}</span>
                </p>
            @endauth
        </main>

        <footer class="absolute bottom-8 text-center text-xs text-slate-600 tracking-wider uppercase font-medium">
            &copy; 2026 Mission Critical Systems | build v1.0.4
        </footer>
    </div>
</body>
</html>
