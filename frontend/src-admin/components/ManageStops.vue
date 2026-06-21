<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { adminApi } from '../api'
import { MapPin, Trash2, Plus, X, Check, Star, RefreshCw } from 'lucide-vue-next'
import maplibregl from 'maplibre-gl'
import 'maplibre-gl/dist/maplibre-gl.css'

const props = defineProps<{ admin: any }>()

const stops = ref<any[]>([])
const isLoading = ref(false)
const isGenerating = ref(false)
const showMap = ref(false)
const mapContainer = ref<HTMLElement | null>(null)
const editingStopIndex = ref<number | null>(null)
const editingStopName = ref('')

let map: maplibregl.Map | null = null
let markers: maplibregl.Marker[] = []

// pending stops before saving
const pendingStops = ref<{ name: string; lat: number; lng: number }[]>([])

const fetchStops = async () => {
    isLoading.value = true
    try {
        const data = await adminApi.get('/campus-stops')
        stops.value = Array.isArray(data) ? data : []
    } catch (e) {
        stops.value = []
    } finally {
        isLoading.value = false
    }
}

const initMap = () => {
    if (!mapContainer.value) return
    if (map) { map.remove(); map = null }

    map = new maplibregl.Map({
        container: mapContainer.value,
        style: 'https://tiles.openfreemap.org/styles/liberty',
        center: [5.1388, 7.3037],
        zoom: 15,
    })

    // load existing stops as markers
    map.on('load', () => {
        stops.value.forEach(stop => {
            addMarker(stop.lng, stop.lat, stop.name, '#4edea3', true)
        })
    })

    // click to add new stop
    map.on('click', (e) => {
        const { lng, lat } = e.lngLat
        const order = pendingStops.value.length + 1
        const name = `Stop ${order}`
        pendingStops.value.push({ name, lat, lng })
        addMarker(lng, lat, name, '#ff5f52', false)
    })
}

const addMarker = (lng: number, lat: number, name: string, color: string, existing: boolean) => {
    if (!map) return

    const el = document.createElement('div')
    el.style.cssText = `
    width: 28px; height: 28px; border-radius: 50%;
    background: ${color};
    border: 3px solid ${color};
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: bold; color: #131313;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4); cursor: pointer;
    ${existing ? 'opacity: 0.6;' : ''}
  `
    el.textContent = name.substring(0, 3).toUpperCase()

    const marker = new maplibregl.Marker({ element: el })
        .setLngLat([lng, lat])
        .setPopup(new maplibregl.Popup({ offset: 25 }).setText(name))
        .addTo(map!)

    if (!existing) markers.push(marker)
}

const removeLastPending = () => {
    if (!pendingStops.value.length) return
    pendingStops.value.pop()
    const last = markers.pop()
    if (last) last.remove()
}

const clearPending = () => {
    pendingStops.value = []
    markers.forEach(m => m.remove())
    markers = []
}

const startEditName = (index: number) => {
    editingStopIndex.value = index
    editingStopName.value = pendingStops.value[index].name
}

const saveEditName = () => {
    if (editingStopIndex.value !== null) {
        pendingStops.value[editingStopIndex.value].name = editingStopName.value
        editingStopIndex.value = null
    }
}

const savePendingStops = async () => {
    if (!pendingStops.value.length) return
    try {
        for (const stop of pendingStops.value) {
            await adminApi.post('/campus-stops', {
                name: stop.name,
                lat: stop.lat,
                lng: stop.lng,
                campus_id: selectedCampusId.value,
            })
        }
        window.alert(`${pendingStops.value.length} stops saved successfully`)
        clearPending()
        await fetchStops()
        // reload map markers
        if (map) { map.remove(); map = null }
        await nextTick()
        initMap()
    } catch (e: any) {
        window.alert(e.message || 'Failed to save stops')
    }
}

const deleteStop = async (id: number) => {
    if (!confirm('Delete this stop?')) return
    try {
        await adminApi.delete(`/campus-stops/${id}`)
        stops.value = stops.value.filter(s => s.id !== id)
    } catch (e) {
        window.alert('Failed to delete stop')
    }
}

const togglePopular = async (id: number) => {
    try {
        const data = await adminApi.patch(`/campus-stops/${id}/popular`)
        const idx = stops.value.findIndex(s => s.id === id)
        if (idx !== -1) stops.value[idx] = data
    } catch (e) {
        window.alert('Failed to update stop')
    }
}

const isMarkingAllPopular = ref(false)

const markAllPopular = async () => {
    if (!confirm('Mark all stops as popular? They will all appear as quick chips for students.')) return
    isMarkingAllPopular.value = true
    try {
        for (const stop of stops.value) {
            if (!stop.is_popular) {
                const data = await adminApi.patch(`/campus-stops/${stop.id}/popular`)
                const idx = stops.value.findIndex(s => s.id === stop.id)
                if (idx !== -1) stops.value[idx] = data
            }
        }
        window.alert('All stops marked as popular')
    } catch (e) {
        window.alert('Failed to update some stops')
    } finally {
        isMarkingAllPopular.value = false
    }
}

const handleGenerateRoutes = async () => {
    if (!confirm(`Generate routes from ${stops.value.length} stops? This may take 30-60 seconds.`)) return
    isGenerating.value = true
    try {
        const data = await adminApi.post('/admin/generate-routes-from-stops', {})
        window.alert(`Generated ${data.routes_created} routes from ${data.stops_used} stops using ${data.roads_found} road segments.`)
    } catch (e: any) {
        window.alert(e.message || 'Failed to generate routes')
    } finally {
        isGenerating.value = false
    }
}

const openMap = async () => {
    showMap.value = true
    await nextTick()
    await nextTick()
    initMap()
}

const closeMap = () => {
    showMap.value = false
    if (map) { map.remove(); map = null }
    clearPending()
}

const campuses = ref<any[]>([])
const selectedCampusId = ref<number | null>(null)

const fetchCampuses = async () => {
    try {
        const data = await adminApi.get('/campuses')
        campuses.value = Array.isArray(data) ? data : []
        // default to first campus
        if (campuses.value.length) selectedCampusId.value = campuses.value[0].id
    } catch (e) { }
}

onUnmounted(() => { if (map) map.remove() })
onMounted(() => {
    fetchStops()
    if (props.admin.role === 'super_admin') fetchCampuses()
})
</script>

<template>
    <div class="p-8 space-y-6 overflow-y-auto h-full">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-on-surface tracking-tight">Campus Stops</h2>
                <p class="text-sm text-on-surface-variant mt-1">
                    Pin stops on the map. System generates routes automatically.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="markAllPopular" :disabled="isMarkingAllPopular || !stops.length"
                    class="flex items-center gap-2 bg-brand-tertiary/20 text-brand-tertiary text-xs font-bold px-4 py-2.5 rounded-lg hover:brightness-110 cursor-pointer border border-brand-tertiary/30 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                    <template v-if="isMarkingAllPopular">
                        <div
                            class="w-3.5 h-3.5 rounded-full border-2 border-brand-tertiary/20 border-t-brand-tertiary animate-spin">
                        </div>
                        Updating...
                    </template>
                    <template v-else>
                        <Star class="w-3.5 h-3.5" />
                        Mark All Popular
                    </template>
                </button>  
                <button @click="handleGenerateRoutes" :disabled="isGenerating || stops.length < 2"
                    class="flex items-center gap-2 bg-brand-secondary/20 text-brand-secondary text-xs font-bold px-4 py-2.5 rounded-lg hover:brightness-110 cursor-pointer border border-brand-secondary/30 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                    <template v-if="isGenerating">
                        <div
                            class="w-3.5 h-3.5 rounded-full border-2 border-brand-secondary/20 border-t-brand-secondary animate-spin">
                        </div>
                        Generating...
                    </template>
                    <template v-else>
                        <RefreshCw class="w-3.5 h-3.5" />
                        Generate Routes
                    </template>
                </button>
                <button @click="openMap"
                    class="flex items-center gap-2 bg-brand-primary-container text-white text-xs font-bold px-4 py-2.5 rounded-lg hover:brightness-110 cursor-pointer border-none">
                    <Plus class="w-3.5 h-3.5" />
                    Add Stops on Map
                </button>
            </div>
        </div>

        <!-- Info banner -->
        <div
            class="p-4 bg-brand-secondary/5 border border-brand-secondary/20 rounded-xl text-xs text-on-surface-variant leading-relaxed">
            <span class="text-brand-secondary font-bold">How it works:</span>
            Pin stops near roads on the map → Click Generate Routes → System connects stops that share the same roads
            automatically.
            Green markers = saved stops. Red markers = new stops being added.
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex items-center justify-center py-12">
            <div class="w-6 h-6 rounded-full border-2 border-brand-primary/20 border-t-brand-primary animate-spin">
            </div>
        </div>

        <!-- Stops list -->
        <div v-else>
            <div v-if="stops.length === 0"
                class="bg-[#1e1e1e] border border-dashed border-[#2d2d2d] rounded-xl p-12 text-center">
                <MapPin class="w-10 h-10 text-on-surface-variant/20 mx-auto mb-3" />
                <p class="text-sm text-on-surface font-semibold">No stops yet.</p>
                <p class="text-xs text-on-surface-variant mt-1.5">Click "Add Stops on Map" to pin campus stops.</p>
            </div>

            <div v-else class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl overflow-hidden">
                <div class="px-6 py-3 border-b border-[#2d2d2d] flex items-center justify-between">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        {{ stops.length }} stops
                    </span>
                    <span class="text-xs text-on-surface-variant">
                        {{stops.filter(s => s.is_popular).length}} marked popular
                    </span>
                </div>
                <div class="divide-y divide-[#2d2d2d]">
                    <div v-for="stop in stops" :key="stop.id"
                        class="flex items-center justify-between px-6 py-3 hover:bg-[#222] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0"
                                :class="stop.is_popular ? 'bg-brand-tertiary/20' : 'bg-[#2a2a2a]'">
                                <MapPin class="w-3.5 h-3.5"
                                    :class="stop.is_popular ? 'text-brand-tertiary' : 'text-on-surface-variant'" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-on-surface">{{ stop.name }}</p>
                                <p class="text-[10px] text-on-surface-variant mt-0.5">
                                    {{ Number(stop.lat).toFixed(4) }}, {{ Number(stop.lng).toFixed(4) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="togglePopular(stop.id)"
                                class="flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-lg cursor-pointer border-none transition-all"
                                :class="stop.is_popular
                                    ? 'bg-brand-tertiary/15 text-brand-tertiary hover:brightness-110'
                                    : 'bg-[#2a2a2a] text-on-surface-variant hover:text-on-surface'">
                                <Star class="w-3 h-3" />
                                {{ stop.is_popular ? 'Popular' : 'Set Popular' }}
                            </button>
                            <button @click="deleteStop(stop.id)"
                                class="w-7 h-7 rounded-lg bg-[#2a2a2a] text-[#ff5f52] flex items-center justify-center cursor-pointer border-none hover:brightness-110 transition-all">
                                <Trash2 class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAP MODAL -->
        <div v-if="showMap" class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-black/85 backdrop-blur-xs" @click="closeMap"></div>
            <div
                class="relative bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl w-full max-w-4xl shadow-2xl animate-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between p-6 border-b border-[#2d2d2d] shrink-0">
                    <div>
                        <h2 class="text-lg font-bold text-on-surface">Pin Campus Stops</h2>
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            Click on the map to pin stops. Place them close to roads.
                        </p>
                    </div>
                    <button @click="closeMap"
                        class="w-8 h-8 rounded-full hover:bg-[#2a2a2a] flex items-center justify-center text-on-surface-variant cursor-pointer border-none bg-transparent">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex flex-1 overflow-hidden">
                    <!-- Map -->
                    <div ref="mapContainer" class="flex-1 h-[500px]"></div>

                    <!-- Sidebar -->
                    <div class="w-64 border-l border-[#2d2d2d] flex flex-col bg-[#131313]">
                        <div v-if="admin.role === 'super_admin'" class="p-4 border-b border-[#2d2d2d]">
                            <label
                                class="text-xs font-bold text-on-surface-variant uppercase tracking-wider block mb-1.5">Campus</label>
                            <select v-model="selectedCampusId"
                                class="w-full h-9 bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg px-3 text-xs text-on-surface focus:outline-none">
                                <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>

                        <div class="p-4 border-b border-[#2d2d2d]">
                            <p class="text-xs text-on-surface-variant">
                                <span class="text-brand-tertiary font-bold">Green</span> = existing stops<br>
                                <span class="text-brand-primary font-bold">Red</span> = new stops
                            </p>
                        </div>

                        <div class="flex-1 overflow-y-auto p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                                    New ({{ pendingStops.length }})
                                </h3>
                                <button v-if="pendingStops.length" @click="removeLastPending"
                                    class="text-[10px] text-[#ff5f52] font-bold cursor-pointer border-none bg-transparent">
                                    Undo
                                </button>
                            </div>

                            <div v-if="!pendingStops.length" class="text-xs text-on-surface-variant text-center py-6">
                                Click map to add stops.
                            </div>

                            <div v-else class="space-y-2">
                                <div v-for="(stop, i) in pendingStops" :key="i"
                                    class="flex items-center gap-2 p-2 rounded-lg bg-[#1e1e1e] border border-[#2d2d2d]">
                                    <div
                                        class="w-5 h-5 rounded-full bg-brand-primary-container flex items-center justify-center text-[9px] font-black text-white shrink-0">
                                        {{ i + 1 }}
                                    </div>

                                    <template v-if="editingStopIndex === i">
                                        <input v-model="editingStopName" @keyup.enter="saveEditName"
                                            @blur="saveEditName"
                                            class="flex-1 bg-[#131313] border border-brand-primary-container rounded px-2 py-0.5 text-xs text-on-surface focus:outline-none"
                                            autofocus />
                                    </template>
                                    <span v-else @click="startEditName(i)"
                                        class="flex-1 text-xs text-on-surface cursor-pointer hover:text-brand-primary truncate">
                                        {{ stop.name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 border-t border-[#2d2d2d] space-y-2">
                            <p class="text-[10px] text-on-surface-variant text-center">
                                Click names to rename stops.
                            </p>
                            <button @click="savePendingStops" :disabled="!pendingStops.length"
                                class="w-full py-2.5 bg-brand-primary-container text-white text-xs font-bold rounded-lg cursor-pointer border-none hover:brightness-110 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <Check class="w-3.5 h-3.5" />
                                Save {{ pendingStops.length }} Stop{{ pendingStops.length !== 1 ? 's' : '' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>