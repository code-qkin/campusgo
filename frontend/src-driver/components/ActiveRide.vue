<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Driver } from '../types'
import { MapPin, Flag, Users, CheckCircle, Play, RefreshCw } from 'lucide-vue-next'

const props = defineProps<{ driver: Driver }>()
const ride = ref<any | null>(null)
const isLoading = ref(false)
const alert = (msg: string) => window.alert(msg)

const getToken = () => localStorage.getItem('driver_token')

const fetchRide = async () => {
    isLoading.value = true
    try {
        const res = await fetch('http://127.0.0.1:8000/api/driver/my-ride', {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        })
        const data = await res.json()
        ride.value = data
    } catch (e) {
        alert('Failed to fetch active ride')
    } finally {
        isLoading.value = false
    }
}

const startRide = async () => {
    if (!ride.value) return
    try {
        const res = await fetch(`http://127.0.0.1:8000/api/rides/${ride.value.id}/start`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        const data = await res.json()
        if (!res.ok) { alert(data.message || 'Failed to start ride'); return }
        alert('Ride started!')
        fetchRide()
    } catch (e) {
        alert('Failed to start ride')
    }
}

const completePassenger = async (passengerId: number) => {
    if (!ride.value) return
    try {
        const res = await fetch(
            `http://127.0.0.1:8000/api/rides/${ride.value.id}/passengers/${passengerId}/complete`,
            {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            }
        )
        const data = await res.json()
        if (!res.ok) { alert(data.message || 'Failed'); return }
        ride.value = data.ride
        if (data.ride.status === 'completed') {
            alert('All passengers dropped off. Ride completed!')
            ride.value = null
        }
    } catch (e) {
        alert('Failed to mark passenger')
    }
}

const acceptPassenger = async (passengerId: number) => {
    if (!ride.value) return
    try {
        const res = await fetch(
            `http://127.0.0.1:8000/api/rides/${ride.value.id}/passengers/${passengerId}/accept`,
            {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            }
        )
        const data = await res.json()
        if (!res.ok) { alert(data.message || 'Failed to accept'); return }
        fetchRide()  // ← refresh instead of setting locally
    } catch (e) {
        alert('Failed to accept passenger')
    }
}

const rejectPassenger = async (passengerId: number) => {
    if (!ride.value) return
    try {
        const res = await fetch(
            `http://127.0.0.1:8000/api/rides/${ride.value.id}/passengers/${passengerId}/reject`,
            {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            }
        )
        const data = await res.json()
        if (!res.ok) { alert(data.message || 'Failed to reject'); return }
        fetchRide()
    } catch (e) {
        alert('Failed to reject passenger')
    }
}

const getStopName = (stopId: number) => {
    const stops = ride.value?.route?.stops || []
    return stops.find((s: any) => s.id === stopId)?.name || `Stop #${stopId}`
}

const activePassengers = (passengers: any[]) =>
    passengers?.filter((p: any) => !['cancelled', 'rejected'].includes(p.status)) || []

onMounted(() => fetchRide())
</script>

<template>
    <div class="p-6 overflow-y-auto h-full space-y-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-xl font-bold text-on-surface">Active Ride</h2>
            <button @click="fetchRide"
                class="flex items-center gap-1.5 text-xs font-semibold text-brand-tertiary hover:brightness-110 cursor-pointer border-none bg-transparent">
                <RefreshCw class="w-3.5 h-3.5" />
                Refresh
            </button>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex items-center justify-center py-20">
            <div class="w-6 h-6 rounded-full border-2 border-brand-tertiary/20 border-t-brand-tertiary animate-spin">
            </div>
        </div>

        <!-- No active ride -->
        <div v-else-if="!ride" class="bg-[#1e1e1e] border border-dashed border-[#2d2d2d] rounded-xl p-12 text-center">
            <CheckCircle class="w-10 h-10 text-on-surface-variant/30 mx-auto mb-3" />
            <p class="text-sm text-on-surface font-semibold">No active ride.</p>
            <p class="text-xs text-on-surface-variant mt-1.5">Accept a ride from Available Rides tab.</p>
        </div>

        <!-- Active ride -->
        <div v-else class="space-y-4">

            <!-- Status banner -->
            <div class="p-4 rounded-xl border text-center font-bold text-sm" :class="ride.status === 'ongoing'
                ? 'bg-brand-tertiary/10 border-brand-tertiary/30 text-brand-tertiary'
                : 'bg-brand-secondary/10 border-brand-secondary/30 text-brand-secondary'">
                {{ ride.status === 'ongoing' ? 'Ride In Progress' : 'Accepted — Ready to Start' }}
            </div>

            <!-- Route -->
            <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5">
                <h3 class="text-sm font-bold text-on-surface mb-4">{{ ride.route?.name }}</h3>
                <div class="space-y-2.5 relative border-l border-[#2d2d2d] pl-4 ml-1">
                    <div v-for="(stop, i) in ride.route?.stops" :key="stop.id" class="relative">
                        <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full border-2" :class="i === 0
                            ? 'border-on-surface bg-[#131313]'
                            : i === ride.route.stops.length - 1
                                ? 'border-brand-tertiary bg-[#131313]'
                                : 'border-[#2d2d2d] bg-[#2d2d2d]'">
                        </div>
                        <p class="text-xs font-semibold text-on-surface">{{ stop.name }}</p>
                    </div>
                </div>
            </div>

            <!-- Ride info -->
            <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5 space-y-3">
                <div class="flex justify-between text-xs">
                    <span class="text-on-surface-variant">Vehicle</span>
                    <span class="text-on-surface font-bold capitalize">{{ ride.vehicle_type }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-on-surface-variant">Ride Type</span>
                    <span class="text-on-surface font-bold capitalize">{{ ride.ride_type }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-on-surface-variant">Seats Available</span>
                    <span class="text-on-surface font-bold">{{ ride.seats_available }} / {{ ride.seats_total }}</span>
                </div>
            </div>

            <!-- Passengers -->
            <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5">
                <h4
                    class="text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-4 flex items-center gap-2">
                    <Users class="w-3.5 h-3.5" />
                    Passengers
                </h4>

                <div v-if="!ride.passengers?.length" class="text-xs text-on-surface-variant text-center py-4">
                    No passengers yet.
                </div>

                <div v-else class="space-y-3">
                    <div v-for="p in activePassengers(ride.passengers)" :key="p.id" class="p-3 rounded-lg border"
                        :class="p.status === 'completed'
                            ? 'bg-brand-tertiary/5 border-brand-tertiary/20'
                            : p.status === 'pending'
                                ? 'bg-brand-secondary/5 border-brand-secondary/30'
                                : 'bg-[#131313] border-[#2d2d2d]'">

                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-bold text-on-surface">
                                        Passenger #{{ p.student_id }}
                                    </span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full capitalize" :class="p.status === 'completed'
                                        ? 'bg-brand-tertiary/20 text-brand-tertiary'
                                        : p.status === 'pending'
                                            ? 'bg-brand-secondary/20 text-brand-secondary'
                                            : 'bg-[#2a2a2a] text-on-surface-variant'">
                                        {{ p.status === 'pending' ? 'Requesting' : p.status }}
                                    </span>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-[10px] text-on-surface-variant">
                                        <MapPin class="w-3 h-3 shrink-0" />
                                        <span>Boards at:</span>
                                        <span class="text-on-surface font-semibold">{{ getStopName(p.boarding_stop_id)
                                            }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[10px] text-on-surface-variant">
                                        <Flag class="w-3 h-3 shrink-0 text-brand-primary" />
                                        <span>Exits at:</span>
                                        <span class="text-on-surface font-semibold">{{ getStopName(p.exit_stop_id)
                                            }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="shrink-0">
                                <!-- Completed -->
                                <div v-if="p.status === 'completed'"
                                    class="w-8 h-8 rounded-full bg-brand-tertiary/20 flex items-center justify-center">
                                    <CheckCircle class="w-4 h-4 text-brand-tertiary" />
                                </div>

                                <!-- Pending — Accept/Reject -->
                                <div v-else-if="p.status === 'pending'" class="flex gap-2">
                                    <button @click="rejectPassenger(p.id)"
                                        class="text-[10px] font-bold px-2.5 py-1.5 rounded-lg bg-[#2a2a2a] text-[#ff5f52] hover:brightness-110 transition-all cursor-pointer border-none">
                                        Reject
                                    </button>
                                    <button @click="acceptPassenger(p.id)"
                                        class="text-[10px] font-bold px-2.5 py-1.5 rounded-lg bg-brand-tertiary/20 text-brand-tertiary hover:brightness-110 transition-all cursor-pointer border-none">
                                        Accept
                                    </button>
                                </div>

                                <!-- Active — Mark Arrived -->
                                <button v-else-if="ride.status === 'ongoing'" @click="completePassenger(p.id)"
                                    class="text-[10px] font-bold px-3 py-1.5 rounded-lg bg-brand-tertiary/15 text-brand-tertiary hover:bg-brand-tertiary/25 transition-all cursor-pointer border-none">
                                    Arrived
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Start button -->
            <button v-if="ride.status === 'accepted'" @click="startRide"
                class="w-full py-4 bg-brand-tertiary text-[#131313] font-bold text-sm rounded-xl hover:brightness-110 active:scale-[0.98] transition-all cursor-pointer border-none flex items-center justify-center gap-2">
                <Play class="w-4 h-4" />
                Start Ride
            </button>

            <!-- Ongoing hint -->
            <div v-if="ride.status === 'ongoing'"
                class="p-4 bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl text-center">
                <p class="text-xs text-on-surface-variant">
                    Mark each passenger as
                    <span class="text-brand-tertiary font-bold">Arrived</span>
                    when they exit at their stop.
                    Ride completes automatically when all passengers are dropped off.
                </p>
            </div>

        </div>
    </div>
</template>