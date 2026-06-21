<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import { adminApi } from '../api'
import { MapPin, ToggleLeft, ToggleRight, Plus, X, Trash2, Check, Pencil, GripVertical } from 'lucide-vue-next'
import draggable from 'vuedraggable'
import maplibregl from 'maplibre-gl'
import 'maplibre-gl/dist/maplibre-gl.css'

const props = defineProps<{ admin: any }>()

const routes = ref<any[]>([])
const campuses = ref<any[]>([])
const isLoading = ref(false)
const showCreateMap = ref(false)
const editingRoute = ref<any | null>(null)

// form state
const routeName = ref('')
const selectedCampusId = ref<number | null>(null)
const pendingStops = ref<{ name: string; lat: number; lng: number; order: number }[]>([])
const isSaving = ref(false)
const editingStopIndex = ref<number | null>(null)
const editingStopName = ref('')

// map
const mapContainer = ref<HTMLElement | null>(null)
let map: maplibregl.Map | null = null
let markers: maplibregl.Marker[] = []

// group routes by campus for super admin
const routesByCampus = computed(() => {
  if (props.admin.role !== 'super_admin') return null
  const groups: Record<string, { campus: any; routes: any[] }> = {}
  routes.value.forEach(route => {
    const key = String(route.campus_id)
    if (!groups[key]) {
      groups[key] = {
        campus: route.campus || { name: `Campus ${route.campus_id}` },
        routes: []
      }
    }
    groups[key].routes.push(route)
  })
  return Object.values(groups)
})

const fetchRoutes = async () => {
  isLoading.value = true
  try {
    const data = await adminApi.get('/routes')
    routes.value = Array.isArray(data) ? data.filter((r: any) => r.id) : []
  } catch (e) {
    routes.value = []
  } finally {
    isLoading.value = false
  }
}

const fetchCampuses = async () => {
  try {
    const data = await adminApi.get('/campuses')
    campuses.value = Array.isArray(data) ? data : []
  } catch (e) {
    campuses.value = []
  }
}

const toggleRoute = async (id: number) => {
  try {
    const data = await adminApi.patch(`/routes/${id}/toggle`)
    const idx = routes.value.findIndex(r => r.id === id)
    if (idx !== -1) routes.value[idx] = data
  } catch (e) {
    window.alert('Failed to update route')
  }
}

const deleteRoute = async (id: number) => {
  if (!id) {
    // just remove from local array
    routes.value = routes.value.filter(r => r.id !== id)
    return
  }
  if (!confirm('Delete this route? All stops will be removed.')) return
  try {
    await adminApi.delete(`/routes/${id}`)
    routes.value = routes.value.filter(r => r.id !== id)
  } catch (e) {
    window.alert('Failed to delete route')
  }
}


const campusStops = ref<any[]>([])
const campusStopMarkers: Record<number, maplibregl.Marker> = {}

const fetchCampusStops = async () => {
  try {
    const data = await adminApi.get('/campus-stops')
    campusStops.value = Array.isArray(data) ? data : []
  } catch (e) {
    campusStops.value = []
  }
}

const addCampusStopMarker = (stop: any) => {
  if (!map) return
  const el = document.createElement('div')
  el.style.cssText = `
    width: 26px; height: 26px; border-radius: 50%;
    background: #4edea3;
    border: 3px solid #4edea3;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: bold; color: #131313;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4); cursor: pointer;
    transition: transform 0.1s;
  `
  el.title = stop.name
  el.textContent = stop.name.substring(0, 2).toUpperCase()

  const marker = new maplibregl.Marker({ element: el })
    .setLngLat([stop.lng, stop.lat])
    .setPopup(new maplibregl.Popup({ offset: 25 }).setText(stop.name))
    .addTo(map!)

  campusStopMarkers[stop.id] = marker
}

const removeCampusStopMarker = (stopId: number) => {
  if (campusStopMarkers[stopId]) {
    campusStopMarkers[stopId].remove()
    delete campusStopMarkers[stopId]
  }
}

// JS version of haversine for frontend use
const haversineJs = (lat1: number, lng1: number, lat2: number, lng2: number): number => {
  const R = 6371000
  const dLat = (lat2 - lat1) * Math.PI / 180
  const dLng = (lng2 - lng1) * Math.PI / 180
  const a = Math.sin(dLat / 2) ** 2 +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLng / 2) ** 2
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
}

const initMap = (centerLat = 7.3037, centerLng = 5.1388) => {
  if (!mapContainer.value) return
  if (map) { map.remove(); map = null }

  map = new maplibregl.Map({
    container: mapContainer.value,
    style: 'https://tiles.openfreemap.org/styles/liberty',
    center: [centerLng, centerLat],
    zoom: 15,
  })

  map.on('load', () => {
    // show all campus stops as green (available)
    campusStops.value.forEach(stop => {
      const alreadyInRoute = pendingStops.value.some(
        p => Math.abs(p.lat - stop.lat) < 0.0001 && Math.abs(p.lng - stop.lng) < 0.0001
      )
      if (!alreadyInRoute) {
        addCampusStopMarker(stop)
      }
    })

    // show route stops as red with order numbers
    if (pendingStops.value.length > 0) {
      pendingStops.value.forEach((stop, i) => {
        addMarker(stop.lng, stop.lat, i + 1)
      })
      drawLine()

      if (pendingStops.value.length > 1) {
        const bounds = pendingStops.value.reduce(
          (b, s) => b.extend([s.lng, s.lat]),
          new maplibregl.LngLatBounds(
            [pendingStops.value[0].lng, pendingStops.value[0].lat],
            [pendingStops.value[0].lng, pendingStops.value[0].lat]
          )
        )
        map!.fitBounds(bounds, { padding: 60 })
      }
    }
  })

  // clicking a green campus stop adds it to the route
  // clicking empty map space also adds a new stop
  map.on('click', (e) => {
    // check if click is near a campus stop (within 30m)
    const { lng, lat } = e.lngLat
    const nearbyStop = campusStops.value.find(stop => {
      const dist = haversineJs(lat, lng, stop.lat, stop.lng)
      return dist < 30
    })

    if (nearbyStop) {
      // add this campus stop to the route
      const order = pendingStops.value.length + 1
      pendingStops.value.push({
        name: nearbyStop.name,
        lat: nearbyStop.lat,
        lng: nearbyStop.lng,
        order,
      })
      // remove green marker, add red marker
      removeCampusStopMarker(nearbyStop.id)
      addMarker(nearbyStop.lng, nearbyStop.lat, order)
    } else {
      // add a new custom stop at clicked location
      const order = pendingStops.value.length + 1
      pendingStops.value.push({ name: `Stop ${order}`, lat, lng, order })
      addMarker(lng, lat, order)
    }
  })
}

const redrawMarkers = () => {
  // update order property to match current array index
  pendingStops.value = pendingStops.value.map((stop, i) => ({
    ...stop,
    order: i + 1
  }))
  // remove all route markers
  markers.forEach(m => m.remove())
  markers = []
  // re-add with correct order numbers
  pendingStops.value.forEach((stop, i) => {
    addMarker(stop.lng, stop.lat, i + 1)
  })
  drawLine()
}

const removeStop = (index: number) => {
  const removed = pendingStops.value[index]
  pendingStops.value.splice(index, 1)
  if (markers[index]) {
    markers[index].remove()
    markers.splice(index, 1)
  }
  // restore green campus stop marker if it matches a campus stop
  const campusStop = campusStops.value.find(
    s => Math.abs(s.lat - removed.lat) < 0.0001 && Math.abs(s.lng - removed.lng) < 0.0001
  )
  if (campusStop) addCampusStopMarker(campusStop)
  redrawMarkers()
}

const addMarker = (lng: number, lat: number, order: number) => {
  if (!map) return
  const el = document.createElement('div')
  el.style.cssText = `
    width: 30px; height: 30px; border-radius: 50%;
    background: ${order === 1 ? '#ffffff' : '#ff5f52'};
    border: 3px solid ${order === 1 ? '#aaaaaa' : '#ff5f52'};
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: bold; color: #131313;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4); cursor: pointer;
  `
  el.textContent = String(order)
  const marker = new maplibregl.Marker({ element: el })
    .setLngLat([lng, lat])
    .addTo(map!)
  markers.push(marker)
  drawLine()
}

const drawLine = () => {
  if (!map || pendingStops.value.length < 2) return
  const coords = pendingStops.value.map(s => [s.lng, s.lat])
  if (map.getSource('pending-route')) {
    (map.getSource('pending-route') as maplibregl.GeoJSONSource).setData({
      type: 'Feature', properties: {},
      geometry: { type: 'LineString', coordinates: coords }
    })
  } else {
    map.addSource('pending-route', {
      type: 'geojson',
      data: { type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates: coords } }
    })
    map.addLayer({
      id: 'pending-route', type: 'line', source: 'pending-route',
      paint: { 'line-color': '#ff5f52', 'line-width': 3, 'line-dasharray': [2, 1] }
    })
  }
}

const removeLastStop = () => {
  if (!pendingStops.value.length) return
  pendingStops.value.pop()
  const last = markers.pop()
  if (last) last.remove()
  drawLine()
}

const clearStops = () => {
  pendingStops.value = []
  markers.forEach(m => m.remove())
  markers = []
  if (map && map.getLayer('pending-route')) {
    map.removeLayer('pending-route')
    map.removeSource('pending-route')
  }
}

const startEditStopName = (index: number) => {
  editingStopIndex.value = index
  editingStopName.value = pendingStops.value[index].name
}

const saveStopName = () => {
  if (editingStopIndex.value !== null) {
    pendingStops.value[editingStopIndex.value].name = editingStopName.value
    editingStopIndex.value = null
  }
}

const handleSaveRoute = async () => {
  if (!routeName.value.trim()) {
    window.alert('Please enter a route name')
    return
  }
  if (pendingStops.value.length < 2) {
    window.alert('Please add at least 2 stops on the map')
    return
  }

  isSaving.value = true
  try {
    const payload: any = {
      name: routeName.value,
      stops: pendingStops.value,
    }

    // super admin must pick campus for new routes
    if (props.admin.role === 'super_admin' && !editingRoute.value) {
      if (!selectedCampusId.value) {
        window.alert('Please select a campus')
        isSaving.value = false
        return
      }
      payload.campus_id = selectedCampusId.value
    }



    let data
    if (editingRoute.value) {
      data = await adminApi.patch(`/routes/${editingRoute.value.id}`, payload)
      const idx = routes.value.findIndex(r => r.id === editingRoute.value.id)
      if (idx !== -1) routes.value[idx] = data
      window.alert('Route updated successfully')
    } else {
      data = await adminApi.post('/routes', payload)
      routes.value.unshift(data)
      window.alert('Route created successfully')
    }

    closeMap()
  } catch (e: any) {
    window.alert(e.message || 'Failed to save route')
  } finally {
    isSaving.value = false
  }
}

const openCreateMap = async () => {
  editingRoute.value = null
  routeName.value = ''
  selectedCampusId.value = null
  clearStops()
  showCreateMap.value = true
  await nextTick()
  await nextTick()
  initMap()
}

const openEditMap = async (route: any) => {
  editingRoute.value = route
  routeName.value = route.name
  selectedCampusId.value = route.campus_id
  pendingStops.value = route.stops
    ? [...route.stops].sort((a, b) => a.order - b.order).map((s, i) => ({
      name: s.name,
      lat: s.lat,
      lng: s.lng,
      order: i + 1
    }))
    : []
  showCreateMap.value = true
  await nextTick()
  await nextTick()
  const firstStop = pendingStops.value[0]
  initMap(firstStop?.lat || 7.3037, firstStop?.lng || 5.1388)
}

const closeMap = () => {
  showCreateMap.value = false
  editingRoute.value = null
  if (map) { map.remove(); map = null }
  Object.values(campusStopMarkers).forEach(m => m.remove())
  Object.keys(campusStopMarkers).forEach(k => delete campusStopMarkers[Number(k)])
  clearStops()
  routeName.value = ''
  selectedCampusId.value = null
}

const expandedRoute = ref<number | null>(null)

const toggleExpand = (id: number) => {
  expandedRoute.value = expandedRoute.value === id ? null : id
}

const togglePopular = async (stopId: number, routeId: number) => {
  console.log('togglePopular called:', stopId, routeId)
  try {
    const data = await adminApi.patch(`/stops/${stopId}/popular`)
    // update the stop in the routes array
    const route = routes.value.find(r => r.id === routeId)
    if (route) {
      const stop = route.stops.find((s: any) => s.id === stopId)
      if (stop) stop.is_popular = data.is_popular
    }
  } catch (e) {
    window.alert('Failed to update stop')
  }
}



const selectedRoutes = ref<number[]>([])
const isDeleting = ref(false)

const allSelected = computed(() =>
  routes.value.length > 0 && selectedRoutes.value.length === routes.value.length
)

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedRoutes.value = []
  } else {
    selectedRoutes.value = routes.value.map(r => r.id)
  }
}

const toggleSelect = (id: number) => {
  if (selectedRoutes.value.includes(id)) {
    selectedRoutes.value = selectedRoutes.value.filter(i => i !== id)
  } else {
    selectedRoutes.value.push(id)
  }
}

const bulkDelete = async () => {
  if (!selectedRoutes.value.length) return
  if (!confirm(`Delete ${selectedRoutes.value.length} route(s)? This cannot be undone.`)) return
  isDeleting.value = true
  try {
    for (const id of selectedRoutes.value) {
      await adminApi.delete(`/routes/${id}`)
    }
    routes.value = routes.value.filter(r => !selectedRoutes.value.includes(r.id))
    selectedRoutes.value = []
    window.alert('Routes deleted successfully')
  } catch (e) {
    window.alert('Failed to delete some routes')
  } finally {
    isDeleting.value = false
  }
}

onMounted(() => {
  fetchRoutes()
  fetchCampusStops()
  if (props.admin.role === 'super_admin') fetchCampuses()
})
</script>

<template>
  <div class="p-8 space-y-6 overflow-y-auto h-full">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-on-surface tracking-tight">Bus Routes</h2>
        <p class="text-sm text-on-surface-variant mt-1">Manage campus transit routes.</p>
      </div>
      <div class="flex items-center gap-3">
        
        <button @click="openCreateMap"
          class="flex items-center gap-2 bg-brand-primary-container text-white text-xs font-bold px-4 py-2.5 rounded-lg hover:brightness-110 cursor-pointer border-none">
          <Plus class="w-3.5 h-3.5" />
          Create Route
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="w-6 h-6 rounded-full border-2 border-brand-primary/20 border-t-brand-primary animate-spin"></div>
    </div>

    <template v-else>
      <!-- Super admin: grouped by campus -->
      <template v-if="admin.role === 'super_admin' && routesByCampus">
        <div v-for="group in routesByCampus" :key="group.campus.id" class="space-y-3">
          <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-2">
            <MapPin class="w-3.5 h-3.5" />
            {{ group.campus.name }}
          </h3>
          <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl overflow-hidden">
            <!-- Bulk action bar -->
            <div v-if="selectedRoutes.length"
              class="flex items-center justify-between px-4 py-3 bg-brand-primary/5 border border-brand-primary/20 rounded-xl mb-3">
              <span class="text-xs font-bold text-on-surface">
                {{ selectedRoutes.length }} route{{ selectedRoutes.length > 1 ? 's' : '' }} selected
              </span>
              <div class="flex items-center gap-3">
                <button @click="selectedRoutes = []"
                  class="text-xs text-on-surface-variant hover:text-on-surface cursor-pointer border-none bg-transparent font-semibold">
                  Clear
                </button>
                <button @click="bulkDelete" :disabled="isDeleting"
                  class="flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg bg-[#ff5f52]/15 text-[#ff5f52] hover:brightness-110 cursor-pointer border-none disabled:opacity-60 transition-all">
                  <Trash2 class="w-3.5 h-3.5" />
                  {{ isDeleting ? 'Deleting...' : `Delete ${selectedRoutes.length}` }}
                </button>
              </div>
            </div>
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-[#2d2d2d] text-xs text-on-surface-variant uppercase tracking-wide">
                  <th class="px-4 py-3 w-10">
                    <input type="checkbox" :checked="allSelected" @change="toggleSelectAll"
                      class="w-3.5 h-3.5 rounded cursor-pointer accent-brand-primary-container" />
                  </th>
                  <th class="text-left px-4 py-3 font-semibold">Route</th>
                  <th class="text-left px-4 py-3 font-semibold">Stops</th>
                  <th class="text-left px-4 py-3 font-semibold">Status</th>
                  <th class="text-right px-4 py-3 font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="routes.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-xs text-on-surface-variant">
                    No routes yet. Create one using the map.
                  </td>
                </tr>

                <template v-for="route in routes" :key="route.id">
                  <!-- Route row -->
                  <tr class="border-b border-[#2d2d2d] hover:bg-[#222] transition-colors cursor-pointer" :class="[
                    expandedRoute === route.id ? 'bg-[#222]' : '',
                    selectedRoutes.includes(route.id) ? 'bg-brand-primary/5' : ''
                  ]" @click="toggleExpand(route.id)">

                    <!-- Checkbox -->
                    <td class="px-4 py-4 w-10" @click.stop>
                      <input type="checkbox" :checked="selectedRoutes.includes(route.id)"
                        @change="toggleSelect(route.id)"
                        class="w-3.5 h-3.5 rounded cursor-pointer accent-brand-primary-container" />
                    </td>

                    <td class="px-4 py-4">
                      <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg bg-brand-primary/10 flex items-center justify-center shrink-0">
                          <MapPin class="w-3.5 h-3.5 text-brand-primary" />
                        </div>
                        <div>
                          <span class="font-semibold text-on-surface text-xs">{{ route.name }}</span>
                          <p class="text-[10px] text-on-surface-variant mt-0.5">Click to manage stops</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-4 text-xs text-on-surface-variant">{{ route.stops?.length || 0 }} stops</td>
                    <td class="px-4 py-4">
                      <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                        :class="route.is_active ? 'bg-brand-tertiary/15 text-brand-tertiary' : 'bg-[#2a2a2a] text-on-surface-variant'">
                        {{ route.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-4 py-4">
                      <div class="flex items-center gap-2 justify-end" @click.stop>
                        <button @click="openEditMap(route)"
                          class="flex items-center gap-1.5 text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-pointer border-none bg-[#2a2a2a] text-on-surface-variant hover:text-white transition-all">
                          <Pencil class="w-3 h-3" /> Edit
                        </button>
                        <button @click="toggleRoute(route.id)"
                          class="flex items-center gap-1.5 text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-pointer border-none transition-all"
                          :class="route.is_active ? 'bg-[#2a2a2a] text-[#ff5f52] hover:brightness-110' : 'bg-brand-tertiary/15 text-brand-tertiary hover:brightness-110'">
                          <ToggleRight v-if="route.is_active" class="w-3.5 h-3.5" />
                          <ToggleLeft v-else class="w-3.5 h-3.5" />
                          {{ route.is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button @click="deleteRoute(route.id)"
                          class="flex items-center gap-1.5 text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-pointer border-none bg-[#2a2a2a] text-[#ff5f52] hover:brightness-110 transition-all">
                          <Trash2 class="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>

                  <!-- Expanded stops row -->
                  <tr v-if="expandedRoute === route.id" class="bg-[#131313]" @click.stop>
                    <td colspan="5" class="px-6 py-4" @click.stop>
                      <div class="space-y-2" @click.stop>
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-3">
                          Stops — mark popular ones to show as quick chips for students
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                          <div v-for="stop in route.stops" :key="stop.id"
                            class="flex items-center justify-between p-3 rounded-lg border"
                            :class="stop.is_popular ? 'bg-brand-tertiary/5 border-brand-tertiary/30' : 'bg-[#1e1e1e] border-[#2d2d2d]'">
                            <div class="flex items-center gap-2">
                              <div
                                class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-black shrink-0"
                                :class="stop.is_popular ? 'bg-brand-tertiary text-[#131313]' : 'bg-[#2a2a2a] text-on-surface-variant'">
                                {{ stop.order }}
                              </div>
                              <span class="text-xs font-semibold text-on-surface">{{ stop.name }}</span>
                            </div>
                            <button @click.stop="togglePopular(stop.id, route.id)"
                              class="text-[10px] font-bold px-2.5 py-1 rounded-lg cursor-pointer border-none transition-all"
                              :class="stop.is_popular
                                ? 'bg-brand-tertiary/20 text-brand-tertiary hover:brightness-110'
                                : 'bg-[#2a2a2a] text-on-surface-variant hover:text-on-surface'">
                              {{ stop.is_popular ? 'Popular' : 'Set Popular' }}
                            </button>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- Campus admin: flat list -->
      <div v-else class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-[#2d2d2d] text-xs text-on-surface-variant uppercase tracking-wide">
              <th class="text-left px-6 py-3 font-semibold">Route</th>
              <th class="text-left px-6 py-3 font-semibold">Stops</th>
              <th class="text-left px-6 py-3 font-semibold">Status</th>
              <th class="text-right px-6 py-3 font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="routes.length === 0">
              <td colspan="4" class="px-6 py-8 text-center text-xs text-on-surface-variant">
                No routes yet. Create one using the map.
              </td>
            </tr>
            <tr v-for="route in routes" :key="route.id"
              class="border-b border-[#2d2d2d] last:border-0 hover:bg-[#222] transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-7 h-7 rounded-lg bg-brand-primary/10 flex items-center justify-center shrink-0">
                    <MapPin class="w-3.5 h-3.5 text-brand-primary" />
                  </div>
                  <span class="font-semibold text-on-surface text-xs">{{ route.name }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-xs text-on-surface-variant">{{ route.stops?.length || 0 }} stops</td>
              <td class="px-6 py-4">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                  :class="route.is_active ? 'bg-brand-tertiary/15 text-brand-tertiary' : 'bg-[#2a2a2a] text-on-surface-variant'">
                  {{ route.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2 justify-end">
                  <button @click="openEditMap(route)"
                    class="flex items-center gap-1.5 text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-pointer border-none bg-[#2a2a2a] text-on-surface-variant hover:text-white transition-all">
                    <Pencil class="w-3 h-3" /> Edit
                  </button>
                  <button @click="toggleRoute(route.id)"
                    class="flex items-center gap-1.5 text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-pointer border-none transition-all"
                    :class="route.is_active ? 'bg-[#2a2a2a] text-[#ff5f52] hover:brightness-110' : 'bg-brand-tertiary/15 text-brand-tertiary hover:brightness-110'">
                    <ToggleRight v-if="route.is_active" class="w-3.5 h-3.5" />
                    <ToggleLeft v-else class="w-3.5 h-3.5" />
                    {{ route.is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                  <button @click="deleteRoute(route.id)"
                    class="flex items-center gap-1.5 text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-pointer border-none bg-[#2a2a2a] text-[#ff5f52] hover:brightness-110 transition-all">
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <!-- CREATE / EDIT ROUTE MODAL -->
    <div v-if="showCreateMap" class="fixed inset-0 z-50 flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-black/85 backdrop-blur-xs" @click="closeMap"></div>
      <div
        class="relative bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl w-full max-w-4xl shadow-2xl animate-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">

        <div class="flex items-center justify-between p-6 border-b border-[#2d2d2d] shrink-0">
          <div>
            <h2 class="text-lg font-bold text-on-surface">
              {{ editingRoute ? 'Edit Route' : 'Create Route' }}
            </h2>
            <p class="text-xs text-on-surface-variant mt-0.5">Click on the map to add stops in order.</p>
          </div>
          <button @click="closeMap"
            class="w-8 h-8 rounded-full hover:bg-[#2a2a2a] flex items-center justify-center text-on-surface-variant cursor-pointer border-none bg-transparent">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="flex flex-1 overflow-hidden">
          <div ref="mapContainer" class="flex-1 h-[500px]"></div>

          <div class="w-72 border-l border-[#2d2d2d] flex flex-col bg-[#131313]">

            <div class="p-4 border-b border-[#2d2d2d] space-y-3">
              <!-- Campus selector for super admin on new routes -->
              <div v-if="admin.role === 'super_admin' && !editingRoute" class="space-y-1.5">
                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider block">Campus</label>
                <select v-model="selectedCampusId"
                  class="w-full h-9 bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg px-3 text-xs text-on-surface focus:outline-none focus:border-brand-primary-container">
                  <option :value="null" disabled>Select campus</option>
                  <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>

              <div class="space-y-1.5">
                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider block">Route
                  Name</label>
                <input v-model="routeName" type="text" placeholder="e.g. Main Gate → Library"
                  class="w-full h-9 bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg px-3 text-xs text-on-surface focus:outline-none focus:border-brand-primary-container" />
              </div>
            </div>

            <!-- Stops list -->
            <div class="flex-1 overflow-y-auto p-4">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                  Stops ({{ pendingStops.length }})
                </h3>
                <button v-if="pendingStops.length" @click="removeLastStop"
                  class="text-[10px] text-[#ff5f52] font-bold cursor-pointer border-none bg-transparent hover:brightness-110">
                  Undo Last
                </button>
              </div>

              <div v-if="pendingStops.length === 0" class="text-xs text-on-surface-variant text-center py-8">
                Click on the map to add stops.
              </div>

              <draggable v-else v-model="pendingStops" item-key="name" handle=".drag-handle" @end="redrawMarkers"
                class="space-y-2">
                <template #item="{ element: stop, index: i }">
                  <div class="flex items-center gap-2 p-2.5 rounded-lg border border-[#2d2d2d] bg-[#1e1e1e] group">

                    <!-- Drag handle -->
                    <div
                      class="drag-handle cursor-grab active:cursor-grabbing text-on-surface-variant hover:text-on-surface shrink-0">
                      <GripVertical class="w-3.5 h-3.5" />
                    </div>

                    <!-- Order number -->
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black shrink-0"
                      :style="{ background: i === 0 ? '#ffffff' : i === pendingStops.length - 1 ? '#ff5f52' : '#4edea3', color: '#131313' }">
                      {{ i + 1 }}
                    </div>

                    <!-- Name -->
                    <template v-if="editingStopIndex === i">
                      <input v-model="editingStopName" @keyup.enter="saveStopName" @blur="saveStopName"
                        class="flex-1 bg-[#131313] border border-brand-primary-container rounded px-2 py-0.5 text-xs text-on-surface focus:outline-none"
                        autofocus />
                    </template>
                    <span v-else @click="startEditStopName(i)"
                      class="flex-1 text-xs text-on-surface cursor-pointer hover:text-brand-primary transition-colors truncate">
                      {{ stop.name }}
                    </span>

                    <!-- Actions -->
                    <div class="flex items-center gap-1 shrink-0">
                      <button @click="startEditStopName(i)"
                        class="text-on-surface-variant hover:text-on-surface cursor-pointer border-none bg-transparent">
                        <Pencil class="w-3 h-3" />
                      </button>
                      <button @click="removeStop(i)"
                        class="text-[#ff5f52] hover:brightness-110 cursor-pointer border-none bg-transparent">
                        <X class="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                </template>
              </draggable>
            </div>

            <div class="p-4 border-t border-[#2d2d2d] space-y-2">
              <p class="text-[10px] text-on-surface-variant text-center">
                Click stop names to rename them.
              </p>
              <button @click="handleSaveRoute" :disabled="isSaving || pendingStops.length < 2 || !routeName"
                class="w-full py-2.5 bg-brand-primary-container text-white text-xs font-bold rounded-lg cursor-pointer border-none hover:brightness-110 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <Check class="w-3.5 h-3.5" />
                {{ isSaving ? 'Saving...' : (editingRoute ? 'Update Route' : 'Save Route') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>