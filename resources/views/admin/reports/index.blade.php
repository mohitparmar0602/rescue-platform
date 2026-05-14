<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ⚙️ Admin Dashboard & Reports
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.csv') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-md shadow transition">
                    ⬇ Export CSV
                </a>
                <a href="{{ route('admin.reports.pdf') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-md shadow transition">
                    📄 Export PDF
                </a>
                <a href="{{ route('admin.agencies.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold rounded-md shadow transition">
                    🏢 Pending Approvals
                    @if($pendingCount > 0)
                        <span class="bg-white text-yellow-700 rounded-full px-1.5 text-xs font-black">{{ $pendingCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </x-slot>

    @push('styles')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── KPI Cards ── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $kpis = [
                        ['label' => 'Total Agencies',   'value' => $totalAgencies,  'bg' => 'bg-indigo-50',  'text' => 'text-indigo-700',  'icon' => '🏢'],
                        ['label' => 'Approved',          'value' => $approvedCount,  'bg' => 'bg-green-50',   'text' => 'text-green-700',   'icon' => '✅'],
                        ['label' => 'Pending Approval',  'value' => $pendingCount,   'bg' => 'bg-yellow-50',  'text' => 'text-yellow-700',  'icon' => '⏳'],
                        ['label' => 'Active Alerts',     'value' => $alertsBySeverity->sum('active'), 'bg' => 'bg-red-50', 'text' => 'text-red-700', 'icon' => '🚨'],
                    ];
                @endphp
                @foreach($kpis as $kpi)
                <div class="rounded-xl border {{ $kpi['bg'] }} border-gray-200 p-5 shadow-sm">
                    <div class="text-2xl mb-1">{{ $kpi['icon'] }}</div>
                    <p class="text-3xl font-extrabold {{ $kpi['text'] }}">{{ $kpi['value'] }}</p>
                    <p class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wide">{{ $kpi['label'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- ── Charts Row ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Agencies by type (Doughnut) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="text-sm font-bold text-gray-700 mb-4">Agencies by Type</h3>
                    <div class="relative h-52">
                        <canvas id="agencyTypeChart"></canvas>
                    </div>
                </div>

                {{-- Alerts timeline (Line) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 lg:col-span-2">
                    <h3 class="text-sm font-bold text-gray-700 mb-4">Alerts — Last 30 Days</h3>
                    <div class="relative h-52">
                        <canvas id="alertTimelineChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ── Resource Utilization ── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-bold text-gray-700 mb-4">Resource Utilization by Agency Type</h3>
                <div class="relative h-56">
                    <canvas id="resourceChart"></canvas>
                </div>
            </div>

            {{-- ── Alert Severity Table ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-sm font-bold text-gray-700">Alert History by Severity</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Severity</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Active</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Resolved</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($alertsBySeverity as $row)
                            @php
                                $badge = match($row->severity) {
                                    'critical' => 'bg-red-100 text-red-800',
                                    'high'     => 'bg-orange-100 text-orange-800',
                                    'medium'   => 'bg-yellow-100 text-yellow-800',
                                    default    => 'bg-blue-100 text-blue-800',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $badge }}">{{ $row->severity }}</span>
                                </td>
                                <td class="px-5 py-3 font-semibold text-gray-900">{{ $row->total }}</td>
                                <td class="px-5 py-3 text-red-600 font-semibold">{{ $row->active }}</td>
                                <td class="px-5 py-3 text-green-600 font-semibold">{{ $row->total - $row->active }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400">No alerts issued yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Recent Alerts --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-sm font-bold text-gray-700">Recent Alerts</h3>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @forelse($recentAlerts as $alert)
                        @php
                            $dot = match($alert->severity) {
                                'critical' => 'bg-red-500',
                                'high'     => 'bg-orange-500',
                                'medium'   => 'bg-yellow-400',
                                default    => 'bg-blue-500',
                            };
                        @endphp
                        <li class="px-5 py-3 flex items-start gap-3">
                            <span class="mt-1.5 h-2 w-2 rounded-full shrink-0 {{ $dot }}"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $alert->title }}</p>
                                <p class="text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }} · {{ $alert->issuer->name ?? 'System' }}</p>
                            </div>
                            @if(!$alert->is_active)
                                <span class="ml-auto shrink-0 text-xs text-gray-400 font-medium">resolved</span>
                            @endif
                        </li>
                        @empty
                        <li class="px-5 py-6 text-center text-gray-400 text-sm">No alerts yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- ── Agency Resource Table ── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-700">Approved Agencies — Resource Utilization</h3>
                    <span class="text-xs text-gray-400">{{ count($agencies) }} agencies</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Agency</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Resources</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Available</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Deployed</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Maintenance</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Utilization</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($agencies as $a)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $a['name'] }}</td>
                                <td class="px-5 py-3 capitalize text-gray-500">{{ $a['type'] }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ $a['resources'] }}</td>
                                <td class="px-5 py-3 text-green-600 font-semibold">{{ $a['available'] }}</td>
                                <td class="px-5 py-3 text-yellow-600 font-semibold">{{ $a['deployed'] }}</td>
                                <td class="px-5 py-3 text-red-500 font-semibold">{{ $a['maintenance'] }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-1.5 w-24">
                                            <div class="h-1.5 rounded-full {{ $a['utilization'] >= 75 ? 'bg-red-500' : ($a['utilization'] >= 40 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                                 style="width: {{ $a['utilization'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-600">{{ $a['utilization'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-5 py-6 text-center text-gray-400">No approved agencies yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const palette = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#8b5cf6'];

        // ── Agencies by type (Doughnut) ──────────────────────────────────
        const agencyTypeData = @json($agenciesByType);
        new Chart(document.getElementById('agencyTypeChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(agencyTypeData).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
                datasets: [{ data: Object.values(agencyTypeData), backgroundColor: palette, borderWidth: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });

        // ── Alerts timeline (Line) ──────────────────────────────────────
        const timeline = @json($alertsTimeline);
        new Chart(document.getElementById('alertTimelineChart'), {
            type: 'line',
            data: {
                labels: Object.keys(timeline),
                datasets: [{
                    label: 'Alerts',
                    data: Object.values(timeline),
                    fill: true,
                    tension: 0.4,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.1)',
                    pointBackgroundColor: '#ef4444',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });

        // ── Resource utilization (Stacked Bar) ──────────────────────────
        const utilData = @json($resourceUtilization);
        const agencyTypes  = Object.keys(utilData);
        const statuses     = ['available', 'deployed', 'maintenance'];
        const statusColors = { available: '#10b981', deployed: '#f59e0b', maintenance: '#ef4444' };

        new Chart(document.getElementById('resourceChart'), {
            type: 'bar',
            data: {
                labels: agencyTypes.map(t => t.charAt(0).toUpperCase() + t.slice(1)),
                datasets: statuses.map(status => ({
                    label: status.charAt(0).toUpperCase() + status.slice(1),
                    data: agencyTypes.map(t => utilData[t]?.[status] ?? 0),
                    backgroundColor: statusColors[status],
                    borderRadius: 4,
                }))
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { position: 'top' } }
            }
        });
    });
    </script>
    @endpush
</x-app-layout>
