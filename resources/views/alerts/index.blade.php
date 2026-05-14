<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Emergency Alerts</h2>
            @role('super_admin')
                <a href="{{ route('alerts.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-md shadow hover:bg-red-700 transition">
                    🚨 Issue New Alert
                </a>
            @endrole
        </div>
    </x-slot>

    {{-- ── Global alert banner injected via WebSocket ── --}}
    <div id="alert-banner"
         class="hidden fixed top-0 inset-x-0 z-50 flex items-start justify-between px-6 py-4 shadow-lg text-white text-sm font-medium">
        <div class="flex items-center gap-3">
            <span id="alert-banner-icon" class="text-2xl"></span>
            <div>
                <p id="alert-banner-title" class="font-bold text-base"></p>
                <p id="alert-banner-desc" class="opacity-90"></p>
            </div>
        </div>
        <button onclick="document.getElementById('alert-banner').classList.add('hidden')"
                class="ml-6 text-white opacity-75 hover:opacity-100 text-lg font-bold">✕</button>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @forelse($alerts as $alert)
                @php
                    $colors = [
                        'critical' => ['bg' => 'bg-red-50',    'border' => 'border-red-400',    'badge' => 'bg-red-600 text-white',    'icon' => '🚨'],
                        'high'     => ['bg' => 'bg-orange-50', 'border' => 'border-orange-400', 'badge' => 'bg-orange-500 text-white', 'icon' => '🔴'],
                        'medium'   => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-400', 'badge' => 'bg-yellow-400 text-gray-900','icon' => '🟡'],
                        'low'      => ['bg' => 'bg-blue-50',   'border' => 'border-blue-300',   'badge' => 'bg-blue-500 text-white',   'icon' => '🔵'],
                    ];
                    $c = $colors[$alert->severity] ?? $colors['low'];
                @endphp
                <div class="rounded-lg border-l-4 {{ $c['bg'] }} {{ $c['border'] }} p-4 shadow-sm flex justify-between items-start">
                    <div class="flex gap-3">
                        <span class="text-2xl mt-0.5">{{ $c['icon'] }}</span>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $c['badge'] }}">
                                    {{ $alert->severity }}
                                </span>
                                @if(!$alert->is_active)
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-200 text-gray-600">Deactivated</span>
                                @endif
                                <span class="text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900">{{ $alert->title }}</h3>
                            <p class="text-sm text-gray-700 mt-1">{{ $alert->description }}</p>
                            @if($alert->lat && $alert->lng)
                                <p class="text-xs text-gray-500 mt-1">
                                    📍 {{ number_format($alert->lat, 4) }}, {{ number_format($alert->lng, 4) }}
                                </p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">Issued by: {{ $alert->issuer->name ?? 'System' }}</p>
                        </div>
                    </div>

                    @role('super_admin')
                        @if($alert->is_active)
                            <form action="{{ route('alerts.deactivate', $alert) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="text-xs text-gray-500 hover:text-red-600 font-medium border border-gray-300 rounded px-2 py-1 hover:border-red-300 transition">
                                    Deactivate
                                </button>
                            </form>
                        @endif
                    @endrole
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500">
                    <p class="text-4xl mb-3">🛡️</p>
                    <p class="font-medium text-gray-700">No alerts issued yet.</p>
                    <p class="text-sm mt-1">All clear — no active emergency alerts.</p>
                </div>
            @endforelse

            @if(method_exists($alerts, 'links'))
                <div class="mt-4">{{ $alerts->links() }}</div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        @auth
        @if(Auth::user()->agency_id)
        const agencyId = {{ Auth::user()->agency_id }};
        const banner   = document.getElementById('alert-banner');

        if (typeof window.Echo !== 'undefined') {
            window.Echo.channel(`agency.${agencyId}.alerts`)
                .listen('AlertIssued', (e) => {
                    showAlertBanner(e);
                });
        }

        function showAlertBanner(alert) {
            const icons = { critical: '🚨', high: '🔴', medium: '🟡', low: '🔵' };
            const bgMap = {
                critical: 'bg-red-700',
                high:     'bg-orange-600',
                medium:   'bg-yellow-500',
                low:      'bg-blue-600',
            };

            // Strip old color classes and apply new
            banner.className = banner.className
                .replace(/bg-\S+/g, '')
                .trim();
            banner.classList.add(bgMap[alert.severity] || 'bg-red-700');

            document.getElementById('alert-banner-icon').textContent  = icons[alert.severity] || '🚨';
            document.getElementById('alert-banner-title').textContent = alert.title;
            document.getElementById('alert-banner-desc').textContent  = alert.description;

            banner.classList.remove('hidden');

            // Auto-dismiss after 30 s
            setTimeout(() => banner.classList.add('hidden'), 30000);
        }
        @endif
        @endauth
    });
    </script>
    @endpush
</x-app-layout>
