<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Live Situation Map') }}
            </h2>
            <div id="broadcasting-indicator" class="hidden flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium border border-green-200 shadow-sm transition-all duration-500">
                <span class="flex h-2 w-2 relative mr-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Broadcasting Live Location
            </div>
        </div>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #map { height: calc(100vh - 150px); width: 100%; z-index: 10; border-radius: 0.5rem; }
            .alert-popup { min-width: 200px; }
            .alert-popup h4 { font-weight: 700; margin-bottom: 4px; }
            .severity-critical { color: #dc2626; }
            .severity-high     { color: #ea580c; }
            .severity-medium   { color: #ca8a04; }
            .severity-low      { color: #2563eb; }
        </style>
    @endpush

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div id="map"></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initial Agencies Passed from Controller
                const initialAgencies = @json($agencies);
                const activeAlerts    = @json($activeAlerts ?? []);
                
                // Initialize Map
                const map = L.map('map').setView([20.5937, 78.9629], 5);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Marker Storage for dynamic updates
                const markers = {};

                // Custom Icons
                const getIconColor = (type) => {
                    switch(type) {
                        case 'fire': return 'red';
                        case 'police': return 'blue';
                        case 'medical': return 'green';
                        case 'ngo': return 'orange';
                        default: return 'gray';
                    }
                };

                const createCustomIcon = (color) => {
                    return new L.Icon({
                        iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${color}.png`,
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                };

                // Generate Popup Content
                const createPopupContent = (agency) => {
                    let resourcesHtml = '<p class="text-xs text-gray-500 italic mt-1">No resources listed.</p>';
                    // If resources exist, list them (Assuming resources will be passed or fetched)
                    if (agency.resources && agency.resources.length > 0) {
                        resourcesHtml = '<ul class="mt-1 list-disc pl-4 text-xs text-gray-700">';
                        agency.resources.forEach(r => {
                            resourcesHtml += `<li>${r.quantity}x ${r.name} (${r.status})</li>`;
                        });
                        resourcesHtml += '</ul>';
                    }

                    return `
                        <div class="p-1 min-w-[200px]">
                            <h4 class="font-bold text-gray-900 border-b pb-1 mb-1">${agency.name}</h4>
                            <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 uppercase tracking-wider mb-2">
                                ${agency.type}
                            </span>
                            <div class="mb-3">
                                <strong class="text-xs text-gray-900 block">Available Resources:</strong>
                                ${resourcesHtml}
                            </div>
                            <a href="/chat?with=${agency.id}"
                               class="w-full block text-center bg-indigo-600 text-white text-xs font-bold py-1.5 px-3 rounded hover:bg-indigo-700 transition">
                                💬 Send Message
                            </a>
                        </div>
                    `;
                };

                // Add or Update Marker
                const updateMarker = (agency) => {
                    if (!agency.lat || !agency.lng) return;

                    const latLng = [agency.lat, agency.lng];

                    if (markers[agency.id]) {
                        // Move existing marker
                        markers[agency.id].setLatLng(latLng);
                        markers[agency.id].setPopupContent(createPopupContent(agency));
                    } else {
                        // Create new marker
                        const marker = L.marker(latLng, { icon: createCustomIcon(getIconColor(agency.type)) })
                            .addTo(map)
                            .bindPopup(createPopupContent(agency));
                        markers[agency.id] = marker;
                    }
                };

                // Render initial agencies
                const agenciesData = {};
                initialAgencies.forEach(agency => {
                    agenciesData[agency.id] = agency;
                    updateMarker(agency);
                });

                // ── Alert Pins ───────────────────────────────────────────
                const alertPulseIcon = L.divIcon({
                    className: '',
                    html: `<div style="
                        width:20px;height:20px;
                        background:rgba(220,38,38,.9);
                        border-radius:50%;
                        border:3px solid #fff;
                        box-shadow:0 0 0 4px rgba(220,38,38,.35);
                        animation:pulse 1.5s infinite;
                    "></div>
                    <style>@keyframes pulse{0%,100%{box-shadow:0 0 0 4px rgba(220,38,38,.35)}50%{box-shadow:0 0 0 10px rgba(220,38,38,.1)}}</style>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10],
                });

                const severityColor = { critical: '#dc2626', high: '#ea580c', medium: '#ca8a04', low: '#2563eb' };

                const renderAlertPin = (alert) => {
                    if (!alert.lat || !alert.lng) return;
                    L.marker([alert.lat, alert.lng], { icon: alertPulseIcon })
                        .addTo(map)
                        .bindPopup(`
                            <div class="alert-popup">
                                <h4 style="color:${severityColor[alert.severity]||'#dc2626'}">
                                    🚨 ${alert.title}
                                </h4>
                                <span style="font-size:11px;font-weight:700;text-transform:uppercase;
                                    color:${severityColor[alert.severity]||'#dc2626'}">
                                    ${alert.severity}
                                </span>
                                <p style="font-size:12px;margin-top:4px;color:#374151">${alert.description}</p>
                            </div>
                        `);
                };

                activeAlerts.forEach(renderAlertPin);

                // -----------------------------------------
                // Laravel Echo WebSockets (Listen for updates)
                // -----------------------------------------
                if (typeof window.Echo !== 'undefined') {
                    window.Echo.channel('agency-locations')
                        .listen('AgencyLocationUpdated', (e) => {
                            const currentAgency = agenciesData[e.agency_id] || {};
                            const updatedAgency = {
                                ...currentAgency,
                                id: e.agency_id,
                                lat: e.lat,
                                lng: e.lng,
                                name: e.name,
                                type: e.type,
                                resources: e.resources || currentAgency.resources
                            };
                            agenciesData[e.agency_id] = updatedAgency;
                            updateMarker(updatedAgency);
                        });

                    window.Echo.channel('agency-resources')
                        .listen('ResourceStatusChanged', (e) => {
                            if (agenciesData[e.agency_id]) {
                                agenciesData[e.agency_id].resources = e.resources;
                                updateMarker(agenciesData[e.agency_id]);
                            }
                        });

                    @auth
                    @if(Auth::user()->agency_id)
                    // Listen for new alert pins on this agency's channel
                    window.Echo.channel('agency.{{ Auth::user()->agency_id }}.alerts')
                        .listen('AlertIssued', (e) => renderAlertPin(e));
                    @endif
                    @endauth

                } else {
                    console.error("Laravel Echo is not defined. Ensure Vite successfully built the assets and reverb is running.");
                }

                // -----------------------------------------
                // Geolocation Tracking (Broadcast own location)
                // -----------------------------------------
                let geoWatcher = null;
                const indicator = document.getElementById('broadcasting-indicator');

                if ("geolocation" in navigator) {
                    // Send an initial ping, then watch
                    geoWatcher = navigator.geolocation.watchPosition(
                        (position) => {
                            indicator.classList.remove('hidden'); // Show "Broadcasting" badge

                            // POST to our backend to update and broadcast
                            axios.post('{{ route("location.ping") }}', {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            }).catch(err => console.error('Error broadcasting location:', err));
                        },
                        (error) => {
                            console.warn("Geolocation tracking failed or was denied:", error);
                            indicator.classList.add('hidden');
                        },
                        {
                            enableHighAccuracy: true,
                            maximumAge: 30000,
                            timeout: 27000
                        }
                    );
                }
            });
        </script>
    @endpush
</x-app-layout>
