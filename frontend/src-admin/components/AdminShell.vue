<script setup lang="ts">
import { ref } from 'vue';
import { AdminUser } from '../types';
import DashboardOverview from './DashboardOverview.vue';
import ManageRoutes from './ManageRoutes.vue';
import ManageStudents from './ManageStudents.vue';
import ManageCampuses from './ManageCampuses.vue';
import ManageStops from './ManageStops.vue';

import {
  LayoutDashboard, Bus, Users, Building2,
  LogOut, Shield, ChevronRight,MapPin
} from 'lucide-vue-next';

const props = defineProps<{ admin: AdminUser }>();
const emit = defineEmits(['logout']);

type AdminTab = 'overview' | 'routes' | 'students' | 'campuses' | 'stops';
const activeTab = ref<AdminTab>('overview');

const navItems = [
  { tab: 'overview'  as AdminTab, label: 'Overview',  icon: LayoutDashboard, roles: ['campus_admin', 'super_admin'] },
  { tab: 'routes'    as AdminTab, label: 'Bus Routes', icon: Bus,             roles: ['campus_admin', 'super_admin'] },
  { tab: 'students'  as AdminTab, label: 'Students',   icon: Users,           roles: ['campus_admin', 'super_admin'] },
  { tab: 'stops' as AdminTab, label: 'Campus Stops', icon: MapPin, roles: ['campus_admin', 'super_admin'] },
  { tab: 'campuses'  as AdminTab, label: 'Campuses',   icon: Building2,       roles: ['super_admin'] },
].filter(item => item.roles.includes(props.admin.role));
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
          {{ admin.role === 'super_admin' ? 'All Campuses' : admin.campusName }}
        </p>
      </div>

      <nav class="flex-1 p-4 space-y-1 mt-2">
        <button
          v-for="item in navItems"
          :key="item.tab"
          @click="activeTab = item.tab"
          class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-semibold transition-all cursor-pointer border-none text-left"
          :class="activeTab === item.tab
            ? 'bg-brand-primary-container/15 text-brand-primary-container'
            : 'text-on-surface-variant hover:bg-[#222] hover:text-on-surface bg-transparent'"
        >
          <span class="flex items-center gap-3">
            <component :is="item.icon" class="w-4 h-4" />
            {{ item.label }}
          </span>
          <ChevronRight v-if="activeTab === item.tab" class="w-3.5 h-3.5 opacity-60" />
        </button>
      </nav>

      <div class="p-4 border-t border-[#2d2d2d] shrink-0">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-full bg-brand-primary-container/20 flex items-center justify-center shrink-0">
            <span class="text-xs font-bold text-brand-primary-container">
              {{ admin.fullName.charAt(0) }}
            </span>
          </div>
          <div class="min-w-0">
            <p class="text-xs font-bold text-on-surface truncate">{{ admin.fullName }}</p>
            <p class="text-[10px] text-on-surface-variant capitalize">{{ admin.role.replace('_', ' ') }}</p>
          </div>
        </div>
        <button
          @click="emit('logout')"
          class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-on-surface-variant hover:text-[#ff5f52] hover:bg-[#222] transition-all cursor-pointer border-none bg-transparent"
        >
          <LogOut class="w-4 h-4" />
          Sign Out
        </button>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
      <header class="h-16 bg-[#1c1b1b] border-b border-[#2d2d2d] flex items-center px-8 shrink-0">
        <h1 class="text-sm font-bold text-on-surface">
          {{ navItems.find(n => n.tab === activeTab)?.label }}
        </h1>
        <span class="ml-auto text-xs text-on-surface-variant">
          {{ new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
        </span>
      </header>

      <main class="flex-1 overflow-hidden">
        <DashboardOverview v-if="activeTab === 'overview'" :admin="admin" />
        <ManageRoutes v-else-if="activeTab === 'routes'" :admin="admin" />
        <ManageStudents    v-else-if="activeTab === 'students'" />
        <ManageCampuses    v-else-if="activeTab === 'campuses'" />
        <ManageStops v-else-if="activeTab === 'stops'" :admin="admin" />
      </main>
    </div>

  </div>
</template>