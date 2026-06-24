<script setup lang="ts">
import { computed } from 'vue'
import { useRouter, useRoute, RouterView } from 'vue-router'
import { Driver } from '../types.ts'

const router = useRouter()
const route  = useRoute()

const driver = computed<Driver>(() => {
  try {
    const raw = JSON.parse(localStorage.getItem('driver_user') || '{}')
    return {
      id:         raw.id || 0,
      fullName:   raw.full_name || raw.fullName || '',
      email:      raw.email || '',
      role:       raw.role || 'driver',
      campusId:   raw.campus_id ? String(raw.campus_id) : null,
      campusName: null,
    }
  } catch {
    return { id: 0, fullName: '', email: '', role: 'driver', campusId: null, campusName: null }
  }
})

const handleLogout = () => {
  localStorage.removeItem('driver_token')
  localStorage.removeItem('driver_user')
  localStorage.removeItem('driver_status')
  router.push('/login')
}

const tabs = [
  { path: '/available', label: '🟢 Available' },
  { path: '/active',    label: '🚦 Active Ride' },
  { path: '/history',   label: '📋 History' },
  { path: '/password',  label: '🔑 Password' },
]
</script>

<template>
  <div class="min-h-screen bg-[#131313] text-on-surface flex flex-col font-sans">
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

    <nav class="bg-[#1c1b1b] border-b border-[#2d2d2d] flex shrink-0 overflow-x-auto">
      <button v-for="tab in tabs" :key="tab.path"
        @click="router.push(tab.path)"
        class="flex-1 min-w-max py-3.5 px-4 text-xs font-bold transition-all cursor-pointer border-none border-b-2"
        :class="route.path === tab.path
          ? 'text-brand-tertiary border-brand-tertiary bg-brand-tertiary/5'
          : 'text-on-surface-variant border-transparent hover:text-on-surface bg-transparent'">
        {{ tab.label }}
      </button>
    </nav>

    <main class="flex-1 overflow-hidden">
      <RouterView v-slot="{ Component }">
        <component :is="Component" :driver="driver" />
      </RouterView>
    </main>
  </div>
</template>
