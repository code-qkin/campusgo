<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Driver } from '../types'

const props = defineProps<{ driver: Driver }>()
const rides = ref<any[]>([])
const isLoading = ref(false)
const alert = (msg: string) => window.alert(msg)

const getToken = () => localStorage.getItem('driver_token')

const fetchRides = async () => {
    isLoading.value = true
    try {
        const res = await fetch('http://127.0.0.1:8000/api/driver/available', {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        })
        const data = await res.json()
        rides.value = data
    } catch (e) {
        alert('Failed to fetch rides')
    } finally {
        isLoading.value = false
    }
}

const acceptRide = async (id: number) => {
    try {
        const res = await fetch(`http://127.0.0.1:8000/api/rides/${id}/accept`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        const data = await res.json()
        if (!res.ok) {
            alert(data.message || 'Failed to accept ride')
            return
        }
        alert('Ride accepted! Check Active Ride tab.')
        fetchRides()
    } catch (e) {
        alert('Failed to accept ride')
    }
}

onMounted(() => 
fetchRides())
</script>
<template>
  <div class="p-6 overflow-y-auto h-full space-y-4">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-xl font-bold text-on-surface">Available Rides</h2>
      <button @click="fetchRides"
        class="text-xs font-semibold text-brand-tertiary hover:brightness-110 cursor-pointer border-none bg-transparent">
        Refresh
      </button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-20">
      <div class="w-6 h-6 rounded-full border-2 border-brand-tertiary/20 border-t-brand-tertiary animate-spin"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="rides.length === 0"
      class="bg-[#1e1e1e] border border-dashed border-[#2d2d2d] rounded-xl p-12 text-center">
      <p class="text-2xl mb-3">🟢</p>
      <p class="text-sm text-on-surface font-semibold">No rides waiting for a driver.</p>
      <p class="text-xs text-on-surface-variant mt-1.5">Check back soon or refresh.</p>
    </div>

    <!-- Rides list -->
    <div v-else class="space-y-4">
      <div v-for="ride in rides" :key="ride.id"
        class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5 hover:border-brand-tertiary/40 transition-all">

        <!-- Route name -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-bold text-on-surface">{{ ride.route?.name || 'Unknown Route' }}</h3>
          <span class="text-xs font-bold px-2.5 py-1 rounded-full capitalize bg-brand-secondary/15 text-brand-secondary">
            {{ ride.vehicle_type }}
          </span>
        </div>

        <!-- Stops -->
        <div class="space-y-2 mb-4" v-if="ride.route?.stops?.length">
          <div class="flex items-center gap-2 text-xs">
            <span class="w-2 h-2 rounded-full bg-on-surface shrink-0"></span>
            <span class="text-on-surface-variant">From:</span>
            <span class="text-on-surface font-semibold">{{ ride.route.stops[0]?.name }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <span class="w-2 h-2 rounded-full bg-brand-tertiary shrink-0"></span>
            <span class="text-on-surface-variant">To:</span>
            <span class="text-on-surface font-semibold">{{ ride.route.stops[ride.route.stops.length - 1]?.name }}</span>
          </div>
        </div>

        <!-- Passengers + ride type -->
        <div class="flex items-center justify-between text-xs text-on-surface-variant mb-4">
          <span>{{ ride.passengers?.filter((p: any) => p.status !== 'cancelled').length || 0 }} passengers</span>
          <span class="capitalize">{{ ride.ride_type }} ride</span>
        </div>

        <!-- Accept button -->
        <button @click="acceptRide(ride.id)"
          class="w-full py-3 bg-brand-tertiary text-[#131313] font-bold text-sm rounded-lg hover:brightness-110 active:scale-[0.98] transition-all cursor-pointer border-none">
          Accept Ride
        </button>
      </div>
    </div>
  </div>
</template>