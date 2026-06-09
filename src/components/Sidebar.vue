<script setup lang="ts">
import { ActiveTab } from '../types';
import { 
  Home, 
  Bus, 
  Users, 
  ShieldCheck, 
  Trophy, 
  History, 
  Settings, 
  Search, 
  LifeBuoy,
  Siren
} from 'lucide-vue-next';

import { showAlert as alert } from '../alert';

defineProps<{
  activeTab: ActiveTab
}>();

const emit = defineEmits(['change-tab', 'trigger-sos', 'logout']);

const tabs = [
  { id: ActiveTab.Home, label: 'Home', icon: Home },
  { id: ActiveTab.Routes, label: 'Routes', icon: Bus },
  { id: ActiveTab.Carpool, label: 'Carpool', icon: Users },
  { id: ActiveTab.SafeWalk, label: 'SafeWalk', icon: ShieldCheck },
  { id: ActiveTab.LostFound, label: 'Lost & Found', icon: Search },
  { id: ActiveTab.Rewards, label: 'Rewards', icon: Trophy },
  { id: ActiveTab.History, label: 'History', icon: History },
  { id: ActiveTab.Settings, label: 'Settings', icon: Settings },
];
</script>

<template>
  <nav class="max-md:hidden fixed left-0 top-0 h-full w-[280px] bg-[#1c1b1b] border-r border-[#2d2d2d] flex flex-col p-4 z-40">
    <!-- Brand Header -->
    <div class="px-4 py-6 mb-4 flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-brand-primary-container flex items-center justify-center shrink-0">
        <Bus class="w-6 h-6 text-white" />
      </div>
      <div>
        <h1 class="text-xl font-bold text-on-surface tracking-tight">CampusGo</h1>
        <p class="text-xs text-on-surface-variant font-medium opacity-65">University Transit</p>
      </div>
    </div>

    <!-- Main Tabs List -->
    <ul class="flex flex-col gap-1.5 flex-1 overflow-y-auto pr-1">
      <li v-for="tab in tabs" :key="tab.id">
        <button
          @click="emit('change-tab', tab.id)"
          class="w-full flex items-center gap-3.5 px-4 py-3 rounded-lg transition-all duration-200 outline-none cursor-pointer text-left"
          :class="activeTab === tab.id 
            ? 'bg-[#353534] text-white font-bold shadow-sm' 
            : 'text-on-surface-variant/80 hover:text-on-surface hover:bg-[#2a2a2a]/40'"
        >
          <component 
            :is="tab.icon" 
            class="w-5 h-5" 
            :class="activeTab === tab.id ? 'text-brand-primary' : 'text-on-surface-variant/70'" 
          />
          <span class="text-sm font-semibold tracking-wide">{{ tab.label }}</span>
          <div v-if="activeTab === tab.id" class="ml-auto w-1.5 h-1.5 bg-brand-primary rounded-full"></div>
        </button>
      </li>
    </ul>

    <!-- Sidebar Footer CTA Action Block -->
    <div class="mt-auto flex flex-col gap-3 pt-4 border-t border-[#2a2a2a]/50">
      <button 
        @click="emit('trigger-sos')"
        class="w-full bg-[#93000a] hover:bg-brand-primary-container text-white font-bold text-sm py-3.5 rounded-lg border border-brand-error/20 hover:border-brand-primary/30 transition-all flex justify-center items-center gap-2 cursor-pointer shadow-md shadow-black/30 group active:scale-95 animate-pulse"
      >
        <Siren class="w-5 h-5 text-[#ffb4ab]" />
        <span>Emergency SOS</span>
      </button>

      <ul class="flex flex-col gap-1 mt-1">
        <li>
          <button
            @click="alert('Connecting you with University Transport Helpdesk Support (24/7)...')"
            class="w-full flex items-center gap-3.5 px-4 py-2.5 rounded-lg text-on-surface-variant/80 hover:text-on-surface hover:bg-[#2a2a2a]/40 text-left cursor-pointer text-sm font-medium"
          >
            <LifeBuoy class="w-5 h-5 text-on-surface-variant/60" />
            <span>Support</span>
          </button>
        </li>
        <li>
          <button
            @click="emit('logout')"
            class="w-full flex items-center gap-3.5 px-4 py-2.5 rounded-lg text-[#ffb4ab] hover:bg-[#93000a]/10 text-left cursor-pointer text-sm font-semibold"
          >
            <span>Logout</span>
          </button>
        </li>
      </ul>
    </div>
  </nav>
</template>
