<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Bus, Search, Plus, Minus, Compass } from 'lucide-vue-next';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

import { showAlert as alert } from '../alert';

const liveActive = ref(true);
const searchQuery = ref('');
const selectedRoute = ref<'red' | 'blue' | 'green'>('red');
const zoomLevel = ref(14.5);

const mapContainer = ref<HTMLElement | null>(null);
let map: maplibregl.Map | null = null;

const handleStopClick = (name: string) => {
  alert(`GPS Tracker Stop details:\nStop Name: ${name}\nCurrent Service Status: Normal\nNext arrival scheduled on time.`);
};

onMounted(() => {
  if (!mapContainer.value) return;

  map = new maplibregl.Map({
    container: mapContainer.value,
    style: 'https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json',
    center: [-122.1697, 37.4275], // generic campus
    zoom: zoomLevel.value,
    pitch: 45,
    bearing: -17.6,
    attributionControl: false
  });

  map.on('load', () => {
    // Add glowing route path
    map?.addSource('route', {
      'type': 'geojson',
      'data': {
        'type': 'Feature',
        'properties': {},
        'geometry': {
          'type': 'LineString',
          'coordinates': [
            [-122.174, 37.427],
            [-122.169, 37.429],
            [-122.165, 37.426],
            [-122.160, 37.424]
          ]
        }
      }
    });

    map?.addLayer({
      'id': 'route-glow',
      'type': 'line',
      'source': 'route',
      'layout': { 'line-join': 'round', 'line-cap': 'round' },
      'paint': { 'line-color': '#ffb4aa', 'line-width': 12, 'line-opacity': 0.3, 'line-blur': 10 }
    });

    map?.addLayer({
      'id': 'route-core',
      'type': 'line',
      'source': 'route',
      'layout': { 'line-join': 'round', 'line-cap': 'round' },
      'paint': { 'line-color': '#ff5f52', 'line-width': 4 }
    });

    // Add Bus Marker 1
    const bus1 = document.createElement('div');
    bus1.className = 'w-6 h-6 bg-[#93000a] border-2 border-black rounded-full flex items-center justify-center shadow-lg animate-bounce';
    bus1.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>';
    new maplibregl.Marker({ element: bus1 })
      .setLngLat([-122.169, 37.429])
      .addTo(map);

    // Add Stop Marker
    const stop1 = document.createElement('div');
    stop1.className = 'w-4 h-4 bg-brand-primary border-[3px] border-black rounded-full shadow-[0_0_12px_#ffb4aa] animate-pulse cursor-pointer';
    stop1.onclick = () => handleStopClick('Library West Plaza');
    new maplibregl.Marker({ element: stop1 })
      .setLngLat([-122.165, 37.426])
      .addTo(map);
  });
});

onUnmounted(() => {
  if (map) map.remove();
});

const handleZoomIn = () => {
  if (map) {
    zoomLevel.value = Math.min(zoomLevel.value + 1, 20);
    map.zoomTo(zoomLevel.value);
  }
};

const handleZoomOut = () => {
  if (map) {
    zoomLevel.value = Math.max(zoomLevel.value - 1, 0);
    map.zoomTo(zoomLevel.value);
  }
};

const resetView = () => {
  if (map) {
    zoomLevel.value = 14.5;
    map.flyTo({ center: [-122.1697, 37.4275], zoom: 14.5 });
    alert('Map view centered around Engineering Loop.');
  }
};
</script>

<template>
  <div class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">
    
    <!-- LEFT PANEL OVERLAY: Route Schedules -->
    <aside class="max-md:relative absolute top-0 left-0 bottom-0 max-md:w-full w-[420px] max-md:h-[50vh] bg-[#0e0e0e]/95 backdrop-blur-xl border-r border-[#2d2d2d] z-20 flex flex-col shadow-2xl max-md:order-2">
      <!-- Panel Header -->
      <header class="p-6 border-b border-[#2d2d2d] flex justify-between items-center shrink-0 select-none">
        <h2 class="text-xl font-bold text-on-surface tracking-tight">Route Schedules</h2>
        
        <!-- Live Tracking toggle button -->
        <div class="flex items-center gap-3 bg-[#2a2a2a]/45 px-3 py-1 rounded-full border border-[#222]">
          <span class="text-xs font-bold text-brand-primary uppercase tracking-widest flex items-center gap-1.5 select-none">
            <span v-if="liveActive" class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
            Live
          </span>
          <button 
            @click="liveActive = !liveActive"
            class="w-9 h-5 rounded-full relative flex items-center p-0.5 cursor-pointer transition-colors"
            :class="liveActive ? 'bg-brand-primary-container' : 'bg-[#353534]'"
          >
            <div class="w-4 h-4 bg-white rounded-full transition-transform" :class="liveActive ? 'translate-x-4' : 'translate-x-0'"></div>
          </button>
        </div>
      </header>

      <!-- Searching Section -->
      <div class="p-6 pb-2 shrink-0">
        <div class="relative group">
          <Search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50 group-focus-within:text-brand-primary transition-colors" />
          <input
            type="text"
            v-model="searchQuery"
            class="w-full h-11 bg-[#201f1f] border border-[#2d2d2d] rounded-xl pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all font-light placeholder:text-on-surface-variant/40"
            placeholder="Search route, line, or stop..."
          />
        </div>
      </div>

      <!-- Scrollable Schedules Feed -->
      <div class="flex-1 overflow-y-auto p-6 pt-2 space-y-4 pb-6">
        
        <!-- 1. RED LINE -->
        <div class="border rounded-xl overflow-hidden transition-all duration-350 bg-[#131313]" :class="selectedRoute === 'red' ? 'border-brand-primary shadow-lg shadow-brand-primary-container/5' : 'border-[#2d2d2d] hover:border-[#2a2a2a]'">
          <div 
            @click="selectedRoute = 'red'"
            class="p-4 flex items-center justify-between cursor-pointer bg-[#ffb4aa]/5 hover:bg-[#ffb4aa]/10 transition-colors select-none"
          >
            <div class="flex items-center gap-4">
              <div class="w-11 h-11 rounded-full bg-brand-primary flex items-center justify-center text-black shrink-0 shadow-lg shadow-brand-primary/10">
                <Bus class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-on-surface">Red Line (Campus Loop)</h3>
                <p class="text-xxs text-brand-primary flex items-center gap-1.5 font-bold mt-0.5 uppercase tracking-wide">
                  <template v-if="liveActive">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-primary animate-pulse"></span>
                    2 buses active • 12 min freq
                  </template>
                  <template v-else>Syncing Paused</template>
                </p>
              </div>
            </div>
          </div>

          <div v-if="selectedRoute === 'red'" class="p-5 bg-[#0e0e0e] border-t border-[#2d2d2d]/30 animate-in fade-in duration-200">
            <div class="relative pl-8 space-y-8 before:absolute before:left-[15px] before:top-2 before:bottom-2 before:w-[2px] before:bg-brand-surface-high">
              <!-- Stop 1 (Departed) -->
              <div class="relative group cursor-pointer" @click="handleStopClick('Student Union South')">
                <div class="absolute -left-[37px] top-1 w-4 h-4 rounded-full border-[3px] border-[#0e0e0e] bg-brand-surface-highest z-10"></div>
                <div class="flex justify-between items-start">
                  <div>
                    <p class="text-xs font-semibold text-on-surface-variant line-through decoration-on-surface-variant/40">Student Union South</p>
                    <p class="text-xxs text-on-surface-variant/60 font-medium mt-0.5">Departed 10:05 AM</p>
                  </div>
                </div>
              </div>
              <!-- Stop 2 (Active/Next Stop) -->
              <div class="relative group cursor-pointer" @click="handleStopClick('Library West Plaza')">
                <div class="absolute -left-[37px] top-1 w-4 h-4 rounded-full border-[3px] border-[#0e0e0e] bg-brand-primary z-10 shadow-[0_0_12px_rgba(255,180,170,0.8)] animate-pulse"></div>
                <div class="flex justify-between items-start">
                  <div>
                    <p class="text-xs font-bold text-on-surface">Library West Plaza</p>
                    <p class="text-xxs text-brand-primary font-bold mt-0.5 uppercase tracking-wide">Arriving in 3 min</p>
                  </div>
                  <span class="px-2 py-0.5 bg-brand-primary/10 text-brand-primary border border-brand-primary/25 text-xxs font-bold rounded">10:12 AM</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </aside>

    <!-- MAP CANVAS (Interactive WebGL Map) -->
    <div class="max-md:relative absolute inset-x-0 inset-y-0 z-0 bg-[#0e0e0e] max-md:pl-0 pl-[420px] overflow-hidden select-none max-md:flex-1 max-md:order-1">
      <div ref="mapContainer" class="w-full h-full absolute inset-0"></div>

      <!-- Floating Controls -->
      <div class="absolute max-md:bottom-4 bottom-10 max-md:right-4 right-10 flex flex-col gap-3 z-20">
        <button 
          type="button"
          @click="resetView"
          class="w-12 h-12 bg-[#201f1f]/85 hover:bg-[#353534]/95 backdrop-blur-md border border-[#2d2d2d] rounded-xl flex items-center justify-center text-on-surface hover:text-brand-primary transition-colors cursor-pointer shadow-2xl"
          title="Recenter Compass"
        >
          <Compass class="w-6 h-6" />
        </button>
        
        <div class="flex flex-col bg-[#201f1f]/85 backdrop-blur-md border border-[#2d2d2d] rounded-xl overflow-hidden shadow-2xl">
          <button 
            type="button"
            @click="handleZoomIn"
            class="w-12 h-12 flex items-center justify-center text-on-surface hover:bg-brand-surface-high transition-colors border-b border-[#2d2d2d] cursor-pointer"
          >
            <Plus class="w-5 h-5" />
          </button>
          <button 
            type="button"
            @click="handleZoomOut"
            class="w-12 h-12 flex items-center justify-center text-on-surface hover:bg-brand-surface-high transition-colors cursor-pointer"
          >
            <Minus class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<style>
.maplibregl-canvas {
  outline: none;
}
.maplibregl-control-container {
  display: none;
}
</style>
