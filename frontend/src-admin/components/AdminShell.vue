<script setup lang="ts">
import { computed } from 'vue'
import { useRouter, useRoute, RouterView } from 'vue-router'
import { AdminUser } from '../types'
import {
  LayoutDashboard, Bus, Users, Building2,
  LogOut, Shield, ChevronRight, MapPin, Car, Lock,
} from 'lucide-vue-next'

const router = useRouter()
const route  = useRoute()

const admin = computed<AdminUser>(() => {
  const raw = JSON.parse(localStorage.getItem('admin_user') || '{}')
  return {
    id:         String(raw.id || ''),
    fullName:   raw.full_name || raw.fullName || '',
    email:      raw.email || '',
    role:       raw.role || 'campus_admin',
    campusId:   raw.campus_id ? String(raw.campus_id) : (raw.campusId || null),
    campusName: raw.campusName || null,
  }
})

const navItems = computed(() => [
  { path: '/dashboard', label: 'Overview',     icon: LayoutDashboard, roles: ['campus_admin', 'super_admin'] },
  { path: '/routes',    label: 'Bus Routes',   icon: Bus,             roles: ['campus_admin', 'super_admin'] },
  { path: '/students',  label: 'Students',     icon: Users,           roles: ['campus_admin', 'super_admin'] },
  { path: '/stops',     label: 'Campus Stops', icon: MapPin,          roles: ['campus_admin', 'super_admin'] },
  { path: '/drivers',   label: 'Drivers',      icon: Car,             roles: ['campus_admin', 'super_admin'] },
  { path: '/campuses',  label: 'Campuses',     icon: Building2,       roles: ['super_admin'] },
  { path: '/password',  label: 'Password',     icon: Lock,            roles: ['campus_admin', 'super_admin'] },
].filter(item => (item.roles as string[]).includes(admin.value.role)))

const currentLabel = computed(
  () => navItems.value.find(n => route.path.startsWith(n.path))?.label || 'Admin'
)

const handleLogout = () => {
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_user')
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-[#131313] text-on-surface flex font-sans">

    <aside class="w-64 shrink-0 bg-[#1c1b1b] border-r border-[#2d2d2d] flex flex-col h-screen sticky top-0">
      <div class="h-16 flex items-center gap-3 px-5 border-b border-[#2d2d2d] shrink-0">
        <div class="w-7 h-7 rounded-lg bg-brand-primary-container flex items-center justify-center">
          <Shield class="w-4 h-4 text-white" />
        </div>
        <div>
          <span class="font-bold text-sm text-on-surface">CampusGo</span>
          <span class="block text-[10px] text-on-surface-variant leading-none mt-0.5">Admin Portal</span>
        </div>
      </div>

      <div class="mx-4 mt-4 p-3 rounded-lg bg-[#131313] border border-[#2d2d2d]">
        <p class="text-[10px] text-on-surface-variant uppercase tracking-wide font-semibold mb-0.5">
          {{ admin.role === 'super_admin' ? 'Platform Level' : 'Campus' }}
        </p>
        <p class="text-xs font-bold text-on-surface truncate">
          {{ admin.role === 'super_admin' ? 'All Campuses' : (admin.campusName || 'Your Campus') }}
        </p>
      </div>

      <nav class="flex-1 p-4 space-y-1 mt-2">
        <button
          v-for="item in navItems"
          :key="item.path"
          @click="router.push(item.path)"
          class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-semibold transition-all cursor-pointer border-none text-left"
          :class="route.path === item.path
            ? 'bg-brand-primary-container/15 text-brand-primary-container'
            : 'text-on-surface-variant hover:bg-[#222] hover:text-on-surface bg-transparent'"
        >
          <span class="flex items-center gap-3">
            <component :is="item.icon" class="w-4 h-4" />
            {{ item.label }}
          </span>
          <ChevronRight v-if="route.path === item.path" class="w-3.5 h-3.5 opacity-60" />
        </button>
      </nav>

      <div class="p-4 border-t border-[#2d2d2d] shrink-0">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-full bg-brand-primary-container/20 flex items-center justify-center shrink-0">
            <span class="text-xs font-bold text-brand-primary-container">
              {{ admin.fullName.charAt(0) || 'A' }}
            </span>
          </div>
          <div class="min-w-0">
            <p class="text-xs font-bold text-on-surface truncate">{{ admin.fullName }}</p>
            <p class="text-[10px] text-on-surface-variant capitalize">{{ admin.role.replace('_', ' ') }}</p>
          </div>
        </div>
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-on-surface-variant hover:text-[#ff5f52] hover:bg-[#222] transition-all cursor-pointer border-none bg-transparent"
        >
          <LogOut class="w-4 h-4" />
          Sign Out
        </button>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
      <header class="h-16 bg-[#1c1b1b] border-b border-[#2d2d2d] flex items-center px-8 shrink-0">
        <h1 class="text-sm font-bold text-on-surface">{{ currentLabel }}</h1>
        <span class="ml-auto text-xs text-on-surface-variant">
          {{ new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
        </span>
      </header>

      <main class="flex-1 overflow-hidden">
        <RouterView v-slot="{ Component }">
          <component :is="Component" :admin="admin" />
        </RouterView>
      </main>
    </div>

  </div>
</template>
