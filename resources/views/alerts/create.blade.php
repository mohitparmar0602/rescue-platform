<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('alerts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Alerts</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Issue Emergency Alert</h2>
        </div>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
        <style>
            #alert-map { height: 280px; width: 100%; border-radius: 0.5rem; z-index: 0; }
        </style>
    @endpush

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="bg-red-600 px-6 py-4">
                    <p class="text-white font-bold text-lg">🚨 Emergency Alert Dispatch</p>
                    <p class="text-red-100 text-sm">This will immediately notify all selected agencies via WebSocket, Email and SMS.</p>
                </div>

                <form action="{{ route('alerts.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alert Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                               placeholder="e.g. Flash Flood — District 4">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea name="description" rows="3"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                  placeholder="Provide actionable detail for responding agencies...">{{ old('description') }}</textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Severity --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Severity Level *</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(['low' => ['🔵','blue'], 'medium' => ['🟡','yellow'], 'high' => ['🔴','orange'], 'critical' => ['🚨','red']] as $level => [$icon, $color])
                            <label class="cursor-pointer">
                                <input type="radio" name="severity" value="{{ $level }}"
                                       {{ old('severity', 'medium') === $level ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="border-2 rounded-lg p-3 text-center text-sm font-semibold capitalize
                                            border-gray-200 peer-checked:border-{{ $color }}-500 peer-checked:bg-{{ $color }}-50
                                            peer-checked:text-{{ $color }}-700 hover:border-gray-300 transition select-none">
                                    {{ $icon }} {{ $level }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('severity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Incident Location (map click) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Incident Location <span class="text-gray-400 font-normal">(click the map to pin, optional)</span>
                        </label>
                        <div id="alert-map" class="mb-2 border border-gray-200"></div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="text-xs text-gray-500">Latitude</label>
                                <input type="number" name="lat" id="lat-input" step="any"
                                       value="{{ old('lat') }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-red-500 focus:border-red-500"
                                       placeholder="e.g. 20.5937">
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-500">Longitude</label>
                                <input type="number" name="lng" id="lng-input" step="any"
                                       value="{{ old('lng') }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-red-500 focus:border-red-500"
                                       placeholder="e.g. 78.9629">
                            </div>
                        </div>
                        @error('lat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @error('lng') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Agencies --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700">Notify Agencies *</label>
                            <button type="button" id="select-all-btn"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                Select All
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                            @foreach($agencies as $agency)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 rounded p-1">
                                    <input type="checkbox" name="agency_ids[]" value="{{ $agency->id }}"
                                           {{ in_array($agency->id, old('agency_ids', [])) ? 'checked' : '' }}
                                           class="agency-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span class="text-sm text-gray-700">
                                        {{ $agency->name }}
                                        <span class="text-xs text-gray-400 capitalize">({{ $agency->type }})</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('agency_ids') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="submit"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-md shadow transition">
                            🚨 Dispatch Alert Now
                        </button>
                        <a href="{{ route('alerts.index') }}"
                           class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ── Map for picking incident location ────────────────────────
            const map     = L.map('alert-map').setView([20.5937, 78.9629], 5);
            let   pin     = null;
            const latInp  = document.getElementById('lat-input');
            const lngInp  = document.getElementById('lng-input');

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            map.on('click', function (e) {
                const { lat, lng } = e.latlng;
                latInp.value = lat.toFixed(6);
                lngInp.value = lng.toFixed(6);

                if (pin) {
                    pin.setLatLng(e.latlng);
                } else {
                    pin = L.marker(e.latlng, {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41], iconAnchor: [12, 41]
                        })
                    }).addTo(map).bindPopup('Incident location').openPopup();
                }
            });

            // Pre-fill pin if old() values exist
            const oldLat = parseFloat(latInp.value);
            const oldLng = parseFloat(lngInp.value);
            if (!isNaN(oldLat) && !isNaN(oldLng)) {
                map.setView([oldLat, oldLng], 10);
                pin = L.marker([oldLat, oldLng]).addTo(map);
            }

            // ── Select All toggle ────────────────────────────────────────
            let allSelected = false;
            document.getElementById('select-all-btn').addEventListener('click', function () {
                allSelected = !allSelected;
                document.querySelectorAll('.agency-checkbox').forEach(cb => cb.checked = allSelected);
                this.textContent = allSelected ? 'Deselect All' : 'Select All';
            });
        });
        </script>
    @endpush
</x-app-layout>
