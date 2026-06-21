<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { api } from '../api';
import { MapPin, Flag, Clock, X, ShieldAlert, Search, Plus, ArrowDown, Car, Bike } from 'lucide-vue-next';
import { showAlert as alert } from '../alert';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

const currentUser = JSON.parse(localStorage.getItem('campusgo_user') || '{}');
const currentUserId = currentUser.id;

const allStops = ref<any[]>([]);
const selectedRide = ref<any | null>(null);
const mapContainer = ref<HTMLElement | null>(null);
let map: maplibregl.Map | null = null;

const activeTab = ref<'search' | 'myride'>('search');
const myRides = ref<any[]>([]);

const searchBoardingStop = ref('');
const searchExitStop = ref('');
const searchResults = ref<any[]>([]);
const hasSearched = ref(false);
const isSearching = ref(false);
const boardingQuery = ref('');
const exitQuery = ref('');
const showBoardingSuggestions = ref(false);
const showExitSuggestions = ref(false);

const showCreateForm = ref(false);
const formVehicleType = ref<'car' | 'keke'>('car');
const formRideType = ref<'shared' | 'alone'>('shared');
const isCreating = ref(false);

const showJoinModal = ref(false);
const joinRideId = ref<number | null>(null);
const joinRideIsOngoing = ref(false);
const formBoardingStop = ref('');
const formExitStop = ref('');

const boardingSuggestions = computed(() => {
  if (!boardingQuery.value) return allStops.value.slice(0, 6);
  return allStops.value.filter(s =>
    s.name.toLowerCase().includes(boardingQuery.value.toLowerCase())
  ).slice(0, 6);
});

const exitSuggestions = computed(() => {
  if (!searchBoardingStop.value) return [];
  const boardingStop = allStops.value.find(s => s.id === Number(searchBoardingStop.value));
  if (!boardingStop) return [];
  return allStops.value.filter(s =>
    s.route_id === boardingStop.route_id &&
    s.order > boardingStop.order &&
    (!exitQuery.value || s.name.toLowerCase().includes(exitQuery.value.toLowerCase()))
  ).slice(0, 6);
});

const selectedBoardingName = computed(() =>
  allStops.value.find(s => s.id === Number(searchBoardingStop.value))?.name || ''
);

const selectedExitName = computed(() =>
  allStops.value.find(s => s.id === Number(searchExitStop.value))?.name || ''
);

// get current user's passenger record on selected ride
const myPassengerRecord = computed(() => {
  if (!selectedRide.value?.passengers) return null;
  return selectedRide.value.passengers.find(
    (p: any) => p.student_id === currentUserId
  ) || null;
});

const isOnRide = computed(() => {
  if (!myPassengerRecord.value) return false;
  return ['waiting', 'confirmed', 'onboard', 'pending'].includes(myPassengerRecord.value.status);
});

const userHasActiveRide = computed(() => myRides.value.length > 0);

const activePassengers = computed(() => {
  if (!selectedRide.value?.passengers) return [];
  return selectedRide.value.passengers.filter(
    (p: any) => !['cancelled', 'rejected'].includes(p.status)
  );
});

// get passenger record for a ride in myRides
const getMyPassengerStatus = (ride: any) => {
  return ride.passengers?.find((p: any) => p.student_id === currentUserId)?.status || null;
};

const initMap = () => {
  if (!mapContainer.value || !selectedRide.value?.route?.stops?.length) return;
  if (map) { map.remove(); map = null; }

  const stops = selectedRide.value.route.stops;
  map = new maplibregl.Map({
    container: mapContainer.value,
    style: 'https://tiles.openfreemap.org/styles/liberty',
    center: [stops[0].lng, stops[0].lat],
    zoom: 15,
  });

  map.on('load', () => {
    if (!map) return;
    stops.forEach((stop: any, i: number) => {
      const el = document.createElement('div');
      el.style.cssText = `
        width: 28px; height: 28px; border-radius: 50%;
        background: ${i === 0 ? '#ffffff' : i === stops.length - 1 ? '#ff5f52' : '#4edea3'};
        border: 3px solid ${i === 0 ? '#aaaaaa' : i === stops.length - 1 ? '#ff5f52' : '#4edea3'};
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: bold; color: #131313;
        box-shadow: 0 2px 8px rgba(0,0,0,0.4); cursor: pointer;
      `;
      el.textContent = String(i + 1);
      new maplibregl.Marker({ element: el })
        .setLngLat([stop.lng, stop.lat])
        .setPopup(new maplibregl.Popup({ offset: 25 }).setText(stop.name))
        .addTo(map!);
    });

    map.addSource('route', {
      type: 'geojson',
      data: {
        type: 'Feature', properties: {},
        geometry: { type: 'LineString', coordinates: stops.map((s: any) => [s.lng, s.lat]) }
      }
    });
    map.addLayer({
      id: 'route', type: 'line', source: 'route',
      paint: { 'line-color': '#4edea3', 'line-width': 3, 'line-opacity': 0.8, 'line-dasharray': [2, 1] }
    });

    const bounds = stops.reduce(
      (b: maplibregl.LngLatBounds, s: any) => b.extend([s.lng, s.lat]),
      new maplibregl.LngLatBounds([stops[0].lng, stops[0].lat], [stops[0].lng, stops[0].lat])
    );
    map.fitBounds(bounds, { padding: 40 });
  });
};

watch(selectedRide, () => {
  if (selectedRide.value) setTimeout(initMap, 150);
});

onUnmounted(() => { if (map) map.remove(); });

const fetchStops = async () => {
  try {
    const data = await api.get('/stops');
    allStops.value = Array.isArray(data) ? data : [];
  } catch (e) {
    alert('Failed to fetch stops');
  }
};

const fetchMyRides = async () => {
  try {
    const data = await api.get('/rides/my');
    myRides.value = Array.isArray(data) ? data : [];
  } catch (e) {
    myRides.value = [];
  }
};

const hideBoardingSuggestions = () => {
  setTimeout(() => showBoardingSuggestions.value = false, 200);
};

const hideExitSuggestions = () => {
  setTimeout(() => showExitSuggestions.value = false, 200);
};

const handleSearch = async () => {
  if (!searchBoardingStop.value || !searchExitStop.value) {
    alert('Please select both boarding and exit stops');
    return;
  }
  isSearching.value = true;
  hasSearched.value = true;
  searchResults.value = [];
  selectedRide.value = null;
  try {
    const data = await api.get(
      `/rides/search?boarding_stop_id=${searchBoardingStop.value}&exit_stop_id=${searchExitStop.value}`
    );
    searchResults.value = Array.isArray(data) ? data : [];
  } catch (e) {
    alert('Search failed');
  } finally {
    isSearching.value = false;
  }
};

const selectBoardingStop = (stop: any) => {
  searchBoardingStop.value = stop.id;
  boardingQuery.value = stop.name;
  searchExitStop.value = '';
  exitQuery.value = '';
  showBoardingSuggestions.value = false;
};

const selectExitStop = (stop: any) => {
  searchExitStop.value = stop.id;
  exitQuery.value = stop.name;
  showExitSuggestions.value = false;
};

const handleCreateRide = async () => {
  if (!searchBoardingStop.value || !searchExitStop.value) {
    alert('Please search for a route first');
    return;
  }
  isCreating.value = true;
  const boardingStop = allStops.value.find(s => s.id === Number(searchBoardingStop.value));
  try {
    await api.post('/rides', {
      route_id: boardingStop?.route_id,
      vehicle_type: formVehicleType.value,
      ride_type: formRideType.value,
    });
    showCreateForm.value = false;
    alert('Ride created successfully');
    await handleSearch();
    await fetchMyRides();
  } catch (e) {
    alert('Failed to create ride');
  } finally {
    isCreating.value = false;
  }
};

const openJoin = (ride: any) => {
  joinRideId.value = ride.id;
  joinRideIsOngoing.value = ride.status === 'ongoing';
  selectedRide.value = ride;
  formBoardingStop.value = searchBoardingStop.value;
  formExitStop.value = searchExitStop.value;
  showJoinModal.value = true;
};

const handleJoin = async () => {
  try {
    await api.post(`/rides/${joinRideId.value}/join`, {
      boarding_stop_id: Number(formBoardingStop.value),
      exit_stop_id: Number(formExitStop.value),
    });
    showJoinModal.value = false;

    if (joinRideIsOngoing.value) {
      alert('Request sent! Waiting for driver approval.');
    } else {
      alert('Successfully joined the ride');
    }

    await fetchMyRides();
    await handleSearch();
    // switch to my ride tab
    activeTab.value = 'myride';
    selectedRide.value = null;
  } catch (e: any) {
    alert('Failed to join ride');
  }
};

const handleLeave = async (id: number) => {
  try {
    await api.post(`/rides/${id}/leave`, {});
    alert('Left ride successfully');
    await fetchMyRides();
    if (hasSearched.value) await handleSearch();
    selectedRide.value = null;
  } catch (e) {
    alert('Failed to leave ride');
  }
};

const popularStops = ref<any[]>([])
const fromStop = ref<any | null>(null)
const toStop = ref<any | null>(null)

const fetchPopularStops = async () => {
  try {
    const data = await api.get('/stops/popular')
    popularStops.value = Array.isArray(data) ? data : []
  } catch (e) {
    popularStops.value = []
  }
}

const handleChipTap = (stop: any) => {
  if (!fromStop.value) {
    // set as FROM
    fromStop.value = stop
    searchBoardingStop.value = stop.id
    boardingQuery.value = stop.name
    searchExitStop.value = ''
    exitQuery.value = ''
    toStop.value = null
  } else if (fromStop.value.id === stop.id) {
    // deselect FROM
    fromStop.value = null
    searchBoardingStop.value = ''
    boardingQuery.value = ''
  } else if (!toStop.value) {
    // set as TO
    toStop.value = stop
    searchExitStop.value = stop.id
    exitQuery.value = stop.name
  } else if (toStop.value.id === stop.id) {
    // deselect TO
    toStop.value = null
    searchExitStop.value = ''
    exitQuery.value = ''
  } else {
    // replace TO
    toStop.value = stop
    searchExitStop.value = stop.id
    exitQuery.value = stop.name
  }
}

onMounted(() => {
  fetchStops();
  fetchMyRides();
  fetchPopularStops()
});
</script>

<template>
  <div
    class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">

    <!-- LEFT PANEL -->
    <section class="flex-1 max-md:p-4 p-10 overflow-y-auto">

      <div class="mb-6">
        <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Order a Ride</h2>
        <p class="text-sm text-on-surface-variant font-light">Search for available rides on campus.</p>
      </div>

      <!-- Tabs -->
      <div class="flex bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg p-1 gap-1 mb-6">
        <button @click="activeTab = 'search'"
          class="flex-1 py-2 text-xs font-bold rounded-md transition-all cursor-pointer border-none flex items-center justify-center gap-2"
          :class="activeTab === 'search'
            ? 'bg-brand-primary-container text-white'
            : 'text-on-surface-variant bg-transparent hover:text-on-surface'">
          <Search class="w-3.5 h-3.5" />
          Find a Ride
        </button>
        <button @click="activeTab = 'myride'"
          class="flex-1 py-2 text-xs font-bold rounded-md transition-all cursor-pointer border-none flex items-center justify-center gap-2"
          :class="activeTab === 'myride'
            ? 'bg-brand-primary-container text-white'
            : 'text-on-surface-variant bg-transparent hover:text-on-surface'">
          <Car class="w-3.5 h-3.5" />
          My Ride
          <span v-if="myRides.length"
            class="w-4 h-4 rounded-full bg-brand-tertiary text-[#131313] text-[10px] font-black flex items-center justify-center">
            {{ myRides.length }}
          </span>
        </button>
      </div>

      <!-- SEARCH TAB -->
      <template v-if="activeTab === 'search'">

        <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5 space-y-4 mb-6">
          <!-- Popular stop chips -->
          <div v-if="popularStops.length" class="mb-6">
            <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-3">Quick Select</p>
            <div class="flex flex-wrap gap-2">
              <button v-for="stop in popularStops" :key="stop.id" @click="handleChipTap(stop)"
                class="px-3 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer" :class="fromStop?.id === stop.id
                  ? 'bg-brand-primary-container text-white border-brand-primary-container'
                  : toStop?.id === stop.id
                    ? 'bg-brand-tertiary/20 text-brand-tertiary border-brand-tertiary'
                    : 'bg-[#1e1e1e] border-[#2d2d2d] text-on-surface-variant hover:text-on-surface hover:border-[#444]'">
                <span class="flex items-center gap-1.5">
                  <MapPin class="w-3 h-3" />
                  {{ stop.name }}
                </span>
              </button>
            </div>

            <!-- Selection summary -->
            <div v-if="fromStop || toStop"
              class="mt-3 p-3 rounded-lg bg-[#131313] border border-[#2d2d2d] text-xs flex items-center gap-3">
              <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-brand-primary-container"></div>
                <span class="text-on-surface-variant">From:</span>
                <span class="text-on-surface font-bold">{{ fromStop?.name || 'Not set' }}</span>
              </div>
              <ArrowDown class="w-3 h-3 text-on-surface-variant rotate-[-90deg]" />
              <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-brand-tertiary"></div>
                <span class="text-on-surface-variant">To:</span>
                <span class="text-on-surface font-bold">{{ toStop?.name || 'Not set' }}</span>
              </div>
              <button v-if="fromStop && toStop" @click="handleSearch"
                class="ml-auto px-3 py-1.5 bg-brand-primary-container text-white text-xs font-bold rounded-lg cursor-pointer border-none hover:brightness-110">
                Search
              </button>
            </div>
          </div>
          <!-- Boarding stop -->
          <div class="space-y-1.5 relative">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
              <MapPin class="w-3.5 h-3.5" /> Where are you?
            </label>
            <input v-model="boardingQuery" type="text" placeholder="Search pickup stop..."
              @focus="showBoardingSuggestions = true" @blur="hideBoardingSuggestions"
              @input="searchBoardingStop = ''; searchExitStop = ''; exitQuery = ''"
              class="w-full h-10 bg-[#131313] border border-[#2d2d2d] rounded-lg px-3 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all" />
            <div v-if="showBoardingSuggestions && boardingSuggestions.length"
              class="absolute top-full left-0 right-0 bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg mt-1 z-20 overflow-hidden shadow-xl">
              <button v-for="stop in boardingSuggestions" :key="stop.id" @mousedown="selectBoardingStop(stop)"
                class="w-full text-left px-4 py-2.5 text-xs hover:bg-[#2a2a2a] transition-colors border-none bg-transparent cursor-pointer flex items-center justify-between">
                <span class="text-on-surface font-semibold">{{ stop.name }}</span>
                <span class="text-on-surface-variant text-[10px]">{{ stop.route?.name }}</span>
              </button>
            </div>
          </div>

          <!-- Arrow -->
          <div class="flex items-center justify-center">
            <div class="w-8 h-8 rounded-full bg-[#131313] border border-[#2d2d2d] flex items-center justify-center">
              <ArrowDown class="w-4 h-4 text-on-surface-variant" />
            </div>
          </div>

          <!-- Exit stop -->
          <div class="space-y-1.5 relative">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
              <Flag class="w-3.5 h-3.5" /> Where are you going?
            </label>
            <input v-model="exitQuery" type="text" placeholder="Search destination stop..."
              :disabled="!searchBoardingStop" @focus="showExitSuggestions = true" @blur="hideExitSuggestions"
              @input="searchExitStop = ''"
              class="w-full h-10 bg-[#131313] border border-[#2d2d2d] rounded-lg px-3 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all disabled:opacity-40" />
            <div v-if="showExitSuggestions && exitSuggestions.length"
              class="absolute top-full left-0 right-0 bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg mt-1 z-20 overflow-hidden shadow-xl">
              <button v-for="stop in exitSuggestions" :key="stop.id" @mousedown="selectExitStop(stop)"
                class="w-full text-left px-4 py-2.5 text-xs hover:bg-[#2a2a2a] transition-colors border-none bg-transparent cursor-pointer">
                <span class="text-on-surface font-semibold">{{ stop.name }}</span>
              </button>
            </div>
          </div>

          <button @click="handleSearch" :disabled="!searchBoardingStop || !searchExitStop"
            class="w-full h-10 bg-brand-primary-container hover:brightness-110 text-white font-bold text-sm rounded-lg flex items-center justify-center gap-2 cursor-pointer border-none disabled:opacity-50 disabled:cursor-not-allowed transition-all">
            <Search class="w-4 h-4" />
            Search Rides
          </button>
        </div>

        <div v-if="isSearching" class="flex items-center justify-center py-12">
          <div class="w-6 h-6 rounded-full border-2 border-brand-primary/20 border-t-brand-primary animate-spin"></div>
        </div>

        <template v-else-if="hasSearched">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-sm font-bold text-on-surface">
                {{ searchResults.length > 0
                  ? `${searchResults.length} ride${searchResults.length > 1 ? 's' : ''} found`
                  : 'No rides found' }}
              </h3>
              <p class="text-xs text-on-surface-variant mt-0.5">
                {{ selectedBoardingName }} → {{ selectedExitName }}
              </p>
            </div>
            <button v-if="!userHasActiveRide" @click="showCreateForm = true"
              class="flex items-center gap-2 bg-[#2a2a2a] hover:bg-[#333] text-on-surface text-xs font-bold px-4 py-2 rounded-lg cursor-pointer border-none transition-all">
              <Plus class="w-3.5 h-3.5" />
              Create Ride
            </button>
          </div>

          <div v-if="searchResults.length === 0"
            class="bg-[#201f1f] border border-dashed border-[#2d2d2d] rounded-xl p-10 text-center mb-6">
            <ShieldAlert class="w-10 h-10 text-brand-primary/70 mx-auto mb-3" />
            <p class="text-sm text-on-surface font-semibold">No rides on this route right now.</p>
            <p class="text-xs text-on-surface-variant mt-1.5">Be the first — create a ride!</p>
            <button v-if="!userHasActiveRide" @click="showCreateForm = true"
              class="mt-4 px-6 py-2.5 bg-brand-primary-container text-white text-xs font-bold rounded-lg cursor-pointer border-none hover:brightness-110">
              Create a Ride
            </button>
          </div>

          <div v-else class="space-y-4 pb-12">
            <div v-for="ride in searchResults" :key="ride.id" @click="selectedRide = ride"
              class="relative border rounded-xl p-5 hover:bg-[#201f1f] transition-all cursor-pointer" :class="selectedRide?.id === ride.id
                ? 'bg-[#201f1f] border-brand-primary'
                : 'bg-[#1e1e1e]/60 border-[#2d2d2d]'">

              <div class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-[#2a2a2a] flex items-center justify-center">
                <Car v-if="ride.vehicle_type === 'car'" class="w-4 h-4 text-on-surface-variant" />
                <Bike v-else class="w-4 h-4 text-on-surface-variant" />
              </div>

              <div class="flex items-center justify-between mb-3 pr-12">
                <h3 class="text-sm font-bold text-on-surface truncate">{{ ride.route?.name }}</h3>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full capitalize ml-2 shrink-0" :class="{
                  'bg-brand-tertiary/15 text-brand-tertiary': ride.status === 'filling',
                  'bg-brand-secondary/15 text-brand-secondary': ride.status === 'available',
                  'bg-brand-primary/15 text-brand-primary': ride.status === 'accepted' || ride.status === 'ongoing',
                }">
                  {{ ride.status }}
                </span>
              </div>

              <div class="flex items-center justify-between text-xs text-on-surface-variant">
                <span class="flex items-center gap-1.5">
                  <Clock class="w-3.5 h-3.5" />
                  {{ new Date(ride.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                </span>
                <span class="text-on-surface font-bold capitalize">{{ ride.ride_type }}</span>
                <span v-if="ride.ride_type !== 'alone'" class="text-on-surface font-bold">
                  {{ ride.seats_available }}/{{ ride.seats_total }} seats
                </span>
              </div>
            </div>
          </div>
        </template>

        <div v-else class="flex flex-col items-center justify-center py-20 text-center">
          <Search class="w-12 h-12 text-on-surface-variant/20 mb-4" />
          <p class="text-sm text-on-surface-variant">Search for a ride to get started.</p>
        </div>
      </template>

      <!-- MY RIDE TAB -->
      <template v-else-if="activeTab === 'myride'">
        <div v-if="myRides.length === 0"
          class="bg-[#201f1f] border border-dashed border-[#2d2d2d] rounded-xl p-12 text-center">
          <Car class="w-10 h-10 text-on-surface-variant/30 mx-auto mb-3" />
          <p class="text-sm text-on-surface font-semibold">No active rides.</p>
          <p class="text-xs text-on-surface-variant mt-1.5">Search for a ride to join one.</p>
          <button @click="activeTab = 'search'"
            class="mt-4 px-6 py-2.5 bg-[#2a2a2a] text-on-surface text-xs font-bold rounded-lg cursor-pointer border-none hover:bg-[#333]">
            Find a Ride
          </button>
        </div>

        <div v-else class="space-y-4 pb-12">
          <div v-for="ride in myRides" :key="ride.id" @click="selectedRide = ride"
            class="relative border rounded-xl p-5 hover:bg-[#201f1f] transition-all cursor-pointer" :class="selectedRide?.id === ride.id
              ? 'bg-[#201f1f] border-brand-primary'
              : 'bg-[#1e1e1e]/60 border-[#2d2d2d]'">

            <div class="flex items-center justify-between mb-3">
              <h3 class="text-sm font-bold text-on-surface">{{ ride.route?.name }}</h3>
              <span class="text-xs font-bold px-2.5 py-1 rounded-full capitalize" :class="{
                'bg-brand-tertiary/15 text-brand-tertiary': ride.status === 'filling',
                'bg-brand-secondary/15 text-brand-secondary': ride.status === 'available',
                'bg-brand-primary/15 text-brand-primary': ride.status === 'accepted' || ride.status === 'ongoing',
              }">
                {{ ride.status }}
              </span>
            </div>

            <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-3">
              <MapPin class="w-3.5 h-3.5" />
              <span>{{ ride.route?.stops?.[0]?.name }}</span>
              <span>→</span>
              <span>{{ ride.route?.stops?.[ride.route.stops.length - 1]?.name }}</span>
            </div>

            <!-- Pending approval badge -->
            <div v-if="getMyPassengerStatus(ride) === 'pending'"
              class="mb-3 px-2.5 py-1.5 rounded-lg bg-brand-secondary/10 border border-brand-secondary/30 text-brand-secondary text-[10px] font-bold flex items-center gap-1.5">
              <div class="w-1.5 h-1.5 rounded-full bg-brand-secondary animate-pulse"></div>
              Pending Driver Approval
            </div>

            <div class="flex items-center justify-between text-xs">
              <span class="flex items-center gap-1.5 text-on-surface-variant">
                <Car v-if="ride.vehicle_type === 'car'" class="w-3.5 h-3.5" />
                <Bike v-else class="w-3.5 h-3.5" />
                <span class="capitalize">{{ ride.vehicle_type }}</span>
              </span>
              <span class="text-on-surface font-bold">{{ ride.seats_available }}/{{ ride.seats_total }} seats</span>
              <button @click.stop="handleLeave(ride.id)"
                class="text-[#ff5f52] text-xs font-bold hover:brightness-110 cursor-pointer border-none bg-transparent px-2 py-1 rounded hover:bg-[#ff5f52]/10 transition-all">
                {{ getMyPassengerStatus(ride) === 'pending' ? 'Withdraw' : 'Leave Ride' }}
              </button>
            </div>
          </div>
        </div>
      </template>

    </section>

    <!-- RIGHT PANEL -->
    <aside v-if="selectedRide"
      class="max-md:absolute max-md:inset-0 max-md:w-full w-[450px] border-l border-[#2d2d2d] bg-[#1c1b1b] flex flex-col h-full shrink-0 relative z-30 shadow-2xl animate-in slide-in-from-right duration-200">

      <div class="p-6 border-b border-[#2d2d2d] flex justify-between items-center shrink-0">
        <h2 class="text-lg font-bold text-on-surface">Ride Details</h2>
        <button @click="selectedRide = null"
          class="w-8 h-8 rounded-full hover:bg-[#2a2a2a] flex items-center justify-center text-on-surface-variant cursor-pointer border-none bg-transparent">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-6 space-y-5">

        <!-- Map -->
        <div ref="mapContainer" class="w-full h-52 rounded-xl overflow-hidden border border-[#2d2d2d]"></div>

        <!-- Route stops -->
        <div class="bg-[#131313] p-4 rounded-xl border border-[#222]">
          <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-3">Route Stops</h3>
          <div class="space-y-2.5 relative border-l border-[#2d2d2d] pl-4 ml-1">
            <div v-for="(stop, i) in selectedRide.route?.stops" :key="stop.id" class="relative">
              <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full border-2" :class="i === 0 ? 'border-on-surface bg-[#131313]'
                : i === selectedRide.route.stops.length - 1 ? 'border-brand-primary bg-[#131313]'
                  : 'border-[#2d2d2d] bg-[#2d2d2d]'">
              </div>
              <p class="text-xs font-semibold text-on-surface">{{ stop.name }}</p>
            </div>
          </div>
        </div>

        <!-- Ride info -->
        <div class="bg-[#131313] p-4 rounded-xl border border-[#222] space-y-3">
          <div class="flex justify-between text-xs">
            <span class="text-on-surface-variant">Vehicle</span>
            <span class="text-on-surface font-bold capitalize flex items-center gap-1.5">
              <Car v-if="selectedRide.vehicle_type === 'car'" class="w-3.5 h-3.5" />
              <Bike v-else class="w-3.5 h-3.5" />
              {{ selectedRide.vehicle_type }}
            </span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-on-surface-variant">Ride Type</span>
            <span class="text-on-surface font-bold capitalize">{{ selectedRide.ride_type }}</span>
          </div>
          <div v-if="selectedRide.ride_type !== 'alone'" class="flex justify-between text-xs">
            <span class="text-on-surface-variant">Seats Available</span>
            <span class="text-on-surface font-bold">{{ selectedRide.seats_available }} / {{ selectedRide.seats_total
            }}</span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-on-surface-variant">Status</span>
            <span class="font-bold capitalize text-brand-tertiary">{{ selectedRide.status }}</span>
          </div>
        </div>

        <!-- Passengers -->
        <div v-if="activePassengers.length" class="bg-[#131313] p-4 rounded-xl border border-[#222]">
          <h4 class="text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-3">
            Passengers ({{ activePassengers.length }})
          </h4>
          <div class="space-y-2">
            <div v-for="p in activePassengers" :key="p.id"
              class="flex items-center justify-between text-xs py-1.5 border-b border-[#2d2d2d] last:border-0">
              <span class="text-on-surface">
                {{ p.student_id === currentUserId ? 'You' : `Passenger #${p.student_id}` }}
              </span>
              <span class="capitalize text-xs font-bold px-2 py-0.5 rounded-full" :class="p.status === 'pending'
                ? 'bg-brand-secondary/15 text-brand-secondary'
                : p.status === 'completed'
                  ? 'bg-brand-tertiary/15 text-brand-tertiary'
                  : 'text-on-surface-variant'">
                {{ p.status === 'pending' ? 'Requesting' : p.status }}
              </span>
            </div>
          </div>
        </div>

      </div>

      <!-- Action -->
      <div class="p-6 border-t border-[#2d2d2d] bg-[#201f1f] shrink-0 space-y-2">

        <!-- Already on this ride -->
        <template v-if="isOnRide">
          <!-- Pending approval -->
          <div v-if="myPassengerRecord?.status === 'pending'"
            class="w-full py-3 rounded-xl text-sm font-bold text-center bg-brand-secondary/10 border border-brand-secondary/30 text-brand-secondary">
            Pending Driver Approval
          </div>
          <button @click="handleLeave(selectedRide.id)"
            class="w-full py-3.5 rounded-xl text-sm font-bold bg-[#2a2a2a] text-[#ff5f52] hover:brightness-110 transition-all cursor-pointer border-none">
            {{ myPassengerRecord?.status === 'pending' ? 'Withdraw Request' : 'Leave Ride' }}
          </button>
        </template>

        <!-- Already on another ride -->
        <div v-else-if="userHasActiveRide"
          class="w-full py-3.5 rounded-xl text-sm font-bold text-center bg-[#2a2a2a] text-on-surface-variant">
          Leave your current ride first
        </div>

        <!-- Filling ride — full -->
        <div v-else-if="selectedRide.seats_available === 0 && selectedRide.status === 'filling'"
          class="w-full py-3.5 rounded-xl text-sm font-bold text-center bg-[#2a2a2a] text-on-surface-variant">
          Ride Full
        </div>

        <!-- Not accepting -->
        <div v-else-if="!['filling', 'ongoing'].includes(selectedRide.status)"
          class="w-full py-3.5 rounded-xl text-sm font-bold text-center bg-[#2a2a2a] text-on-surface-variant">
          Not Accepting Passengers
        </div>

        <!-- Join / Request -->
        <button v-else @click="openJoin(selectedRide)"
          class="w-full py-3.5 rounded-xl text-sm font-bold bg-[#ff5f52] hover:bg-[#ff786d] text-white transition-all cursor-pointer border-none">
          {{ selectedRide.status === 'ongoing' ? 'Request to Join' : 'Join Ride' }}
        </button>

        <p class="text-center text-on-surface-variant/60 text-xs">
          Fare calculated based on your stops.
        </p>
      </div>
    </aside>

    <!-- CREATE RIDE MODAL -->
    <div v-if="showCreateForm" class="fixed inset-0 z-50 flex items-center justify-center max-md:p-4 p-8">
      <div class="absolute inset-0 bg-black/85 backdrop-blur-xs" @click="showCreateForm = false"></div>
      <div
        class="relative bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl max-md:p-5 p-8 max-w-[480px] w-full shadow-2xl animate-in zoom-in-95 duration-200">
        <header class="flex justify-between items-center mb-6">
          <div>
            <h2 class="text-lg font-bold text-on-surface">Create a Ride</h2>
            <p class="text-xs text-on-surface-variant mt-1">{{ selectedBoardingName }} → {{ selectedExitName }}</p>
          </div>
          <button @click="showCreateForm = false"
            class="w-8 h-8 rounded-full hover:bg-[#2a2a2a] flex items-center justify-center text-on-surface-variant cursor-pointer border-none bg-transparent">
            <X class="w-5 h-5" />
          </button>
        </header>

        <form @submit.prevent="handleCreateRide" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Vehicle Type</label>
            <div class="flex gap-3">
              <button type="button" @click="formVehicleType = 'car'"
                class="flex-1 py-3 rounded-lg text-xs font-bold border cursor-pointer transition-all flex items-center justify-center gap-2"
                :class="formVehicleType === 'car'
                  ? 'bg-brand-primary-container text-white border-brand-primary-container'
                  : 'bg-[#131313] border-[#2d2d2d] text-on-surface-variant hover:text-white'">
                <Car class="w-4 h-4" /> Car (4 seats)
              </button>
              <button type="button" @click="formVehicleType = 'keke'"
                class="flex-1 py-3 rounded-lg text-xs font-bold border cursor-pointer transition-all flex items-center justify-center gap-2"
                :class="formVehicleType === 'keke'
                  ? 'bg-brand-primary-container text-white border-brand-primary-container'
                  : 'bg-[#131313] border-[#2d2d2d] text-on-surface-variant hover:text-white'">
                <Bike class="w-4 h-4" /> Keke (3 seats)
              </button>
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Ride Type</label>
            <div class="flex gap-3">
              <button type="button" @click="formRideType = 'shared'"
                class="flex-1 py-2.5 rounded-lg text-xs font-bold border cursor-pointer transition-all" :class="formRideType === 'shared'
                  ? 'bg-brand-primary-container text-white border-brand-primary-container'
                  : 'bg-[#131313] border-[#2d2d2d] text-on-surface-variant hover:text-white'">
                Shared Seat
              </button>
              <button type="button" @click="formRideType = 'alone'"
                class="flex-1 py-2.5 rounded-lg text-xs font-bold border cursor-pointer transition-all" :class="formRideType === 'alone'
                  ? 'bg-brand-primary-container text-white border-brand-primary-container'
                  : 'bg-[#131313] border-[#2d2d2d] text-on-surface-variant hover:text-white'">
                Order Alone
              </button>
            </div>
          </div>

          <button type="submit" :disabled="isCreating"
            class="w-full py-3 bg-brand-primary-container hover:brightness-110 text-white font-bold text-sm rounded-lg cursor-pointer border-none mt-2 disabled:opacity-60">
            {{ isCreating ? 'Creating...' : 'Create Ride' }}
          </button>
        </form>
      </div>
    </div>

    <!-- JOIN / REQUEST MODAL -->
    <div v-if="showJoinModal" class="fixed inset-0 z-50 flex items-center justify-center max-md:p-4 p-8">
      <div class="absolute inset-0 bg-black/85 backdrop-blur-xs" @click="showJoinModal = false"></div>
      <div
        class="relative bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl max-md:p-5 p-8 max-w-[480px] w-full shadow-2xl animate-in zoom-in-95 duration-200">
        <header class="flex justify-between items-center mb-6">
          <h2 class="text-lg font-bold text-on-surface">
            {{ joinRideIsOngoing ? 'Request to Join' : 'Confirm Join' }}
          </h2>
          <button @click="showJoinModal = false"
            class="w-8 h-8 rounded-full hover:bg-[#2a2a2a] flex items-center justify-center text-on-surface-variant cursor-pointer border-none bg-transparent">
            <X class="w-5 h-5" />
          </button>
        </header>

        <!-- Ongoing ride notice -->
        <div v-if="joinRideIsOngoing"
          class="mb-4 p-3 rounded-lg bg-brand-secondary/10 border border-brand-secondary/30 text-brand-secondary text-xs font-medium">
          This ride is ongoing. Your request will be sent to the driver for approval.
        </div>

        <div class="bg-[#131313] p-4 rounded-xl border border-[#222] mb-6 space-y-3">
          <div class="flex items-center gap-2 text-xs">
            <MapPin class="w-3.5 h-3.5 text-on-surface-variant shrink-0" />
            <span class="text-on-surface-variant">Boarding:</span>
            <span class="text-on-surface font-bold">{{ selectedBoardingName }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <Flag class="w-3.5 h-3.5 text-brand-primary shrink-0" />
            <span class="text-on-surface-variant">Exit:</span>
            <span class="text-on-surface font-bold">{{ selectedExitName }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <span class="text-on-surface-variant">Route:</span>
            <span class="text-on-surface font-bold">{{ selectedRide?.route?.name }}</span>
          </div>
        </div>

        <button @click="handleJoin"
          class="w-full py-3 bg-brand-primary-container hover:brightness-110 text-white font-bold text-sm rounded-lg cursor-pointer border-none">
          {{ joinRideIsOngoing ? 'Send Request' : 'Confirm Join' }}
        </button>
      </div>
    </div>

  </div>
</template>