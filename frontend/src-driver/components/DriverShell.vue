<script setup lang="ts">
import { ref } from 'vue'
import { Driver } from '../types.ts'
import AvailableRides from './AvailableRides.vue'
import ActiveRide from './ActiveRide.vue'
import RideHistory from './RideHistory.vue'

const props = defineProps<{ driver: Driver }>()
const emit = defineEmits(['logout'])

type DriverTab = 'available' | 'active' | 'history'
const activeTab = ref<DriverTab>('available')

const handleLogout = () => {
    localStorage.removeItem('driver_token');
    localStorage.removeItem('driver_user');
    emit('logout');
}

</script>
<template>
  <div class="min-h-screen bg-[#131313] text-on-surface flex flex-col font-sans">

    <!-- Header -->
    <header class="h-16 bg-[#1c1b1b] border-b border-[#2d2d2d] flex items-center justify-between px-6 shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-lg bg-brand-tertiary/20 border border-brand-tertiary/30 flex items-center justify-center">
          <span class="text-sm">🚗</span>
        </div>
        <div>
          <span class="font-bold text-sm text-on-surface">CampusGo Driver</span>
          <span class="block text-[10px] text-on-surface-variant">{{ driver.fullName }}</span>
        </div>
      </div>
      <button @click="handleLogout"
        class="text-xs font-semibold text-on-surface-variant hover:text-[#ff5f52] transition-colors cursor-pointer border-none bg-transparent">
        Sign Out
      </button>
    </header>

    <!-- Tab navigation -->
    <nav class="bg-[#1c1b1b] border-b border-[#2d2d2d] flex shrink-0">
      <button v-for="tab in ([
        { id: 'available', label: '🟢 Available Rides' },
        { id: 'active',    label: '🚦 Active Ride' },
        { id: 'history',   label: '📋 History' },
      ] as const)" :key="tab.id"
        @click="activeTab = tab.id"
        class="flex-1 py-3.5 text-xs font-bold transition-all cursor-pointer border-none border-b-2"
        :class="activeTab === tab.id
          ? 'text-brand-tertiary border-brand-tertiary bg-brand-tertiary/5'
          : 'text-on-surface-variant border-transparent hover:text-on-surface bg-transparent'">
        {{ tab.label }}
      </button>
    </nav>

    <!-- Content -->
    <main class="flex-1 overflow-hidden">
      <AvailableRides v-if="activeTab === 'available'" :driver="driver" />
      <ActiveRide    v-else-if="activeTab === 'active'" :driver="driver" />
      <RideHistory   v-else-if="activeTab === 'history'" :driver="driver" />
    </main>

  </div>
</template>