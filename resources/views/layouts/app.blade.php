<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        {{-- ── Global Emergency Alert Banner (WebSocket-driven) ── --}}
        @auth
        @if(Auth::user()->agency_id)
        <div id="global-alert-banner"
             class="hidden fixed top-0 inset-x-0 z-[9999] flex items-start justify-between px-6 py-4 shadow-2xl text-white text-sm font-medium pointer-events-auto">
            <div class="flex items-center gap-3">
                <span id="gab-icon" class="text-2xl animate-bounce"></span>
                <div>
                    <p id="gab-title" class="font-bold text-base"></p>
                    <p id="gab-desc" class="opacity-90 text-sm"></p>
                </div>
            </div>
            <button onclick="document.getElementById('global-alert-banner').classList.add('hidden')"
                    class="ml-8 shrink-0 text-white opacity-70 hover:opacity-100 text-xl font-bold leading-none">✕</button>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.Echo === 'undefined') return;

            const agencyId = {{ Auth::user()->agency_id }};
            const banner   = document.getElementById('global-alert-banner');
            const bgMap    = { critical: 'bg-red-700', high: 'bg-orange-600', medium: 'bg-yellow-500', low: 'bg-blue-600' };
            const iconMap  = { critical: '🚨', high: '🔴', medium: '🟡', low: '🔵' };

            window.Echo.channel(`agency.${agencyId}.alerts`)
                .listen('AlertIssued', function (e) {
                    // Strip old bg class and apply severity colour
                    banner.className = banner.className.replace(/bg-\S+/g, '').trim();
                    banner.classList.add(bgMap[e.severity] ?? 'bg-red-700');

                    document.getElementById('gab-icon').textContent  = iconMap[e.severity] ?? '🚨';
                    document.getElementById('gab-title').textContent = e.title;
                    document.getElementById('gab-desc').textContent  = e.description;

                    banner.classList.remove('hidden');
                    setTimeout(() => banner.classList.add('hidden'), 30000);
                });

            // ── New message badge: reload nav area on incoming message ──
            window.Echo.channel(`agency.${agencyId}.chat`)
                .listen('NewMessageReceived', function () {
                    // Only refresh nav badge if NOT currently on the chat page
                    if (!window.location.pathname.startsWith('/chat')) {
                        // Reload just to update the unread count badge in nav
                        // (lightweight — nav is server-rendered)
                        const badge = document.getElementById('nav-msg-badge');
                        if (badge) {
                            const current = parseInt(badge.textContent) || 0;
                            badge.textContent = current + 1;
                            badge.classList.remove('hidden');
                        }
                    }
                });
        });
        </script>
        @endif
        @endauth

        @stack('scripts')
    </body>
</html>
