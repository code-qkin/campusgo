<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router';
import { Home, Users, Search, History, Settings, LifeBuoy, Siren } from 'lucide-vue-next';
import { showAlert as alert } from '../alert';
import FutaLogo from './FutaLogo.vue';

const emit = defineEmits(['trigger-sos', 'logout']);
const router = useRouter();
const route  = useRoute();

const tabs = [
  { path: '/home',       label: 'Home',         icon: Home },
  { path: '/carpool',    label: 'Carpool',       icon: Users },
  { path: '/lost-found', label: 'Lost & Found',  icon: Search },
  { path: '/history',    label: 'History',       icon: History },
  { path: '/settings',   label: 'Settings',      icon: Settings },
];
</script>

<template>
  <nav class="max-md:hidden fixed left-0 top-0 h-full w-[280px] bg-[#1c1b1b] border-r border-[#2d2d2d] flex flex-col p-4 z-40">
    <!-- Brand -->
    <div class="px-4 py-6 mb-4 flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center shrink-0 overflow-hidden border border-[#2d2d2d]">
        <FutaLogo size="w-8 h-8" />
      </div>
      <div>
        <h1 class="text-xl font-bold text-on-surface tracking-tight">CampusGo</h1>
        <p class="text-xs text-on-surface-variant font-medium opacity-65">University Transit</p>
      </div>
    </div>

    <!-- Nav items -->
    <ul class="flex flex-col gap-1.5 flex-1 overflow-y-auto pr-1">
      <li v-for="tab in tabs" :key="tab.path">
        <button @click="router.push(tab.path)"
          class="w-full flex items-center gap-3.5 px-4 py-3 rounded-lg transition-all duration-200 outline-none cursor-pointer text-left border-none"
          :class="route.path === tab.path
            ? 'bg-[#353534] text-white font-bold shadow-sm'
            : 'bg-transparent text-on-surface-variant/80 hover:text-on-surface hover:bg-[#2a2a2a]/40'">
          <component :is="tab.icon" class="w-5 h-5"
            :class="route.path === tab.path ? 'text-brand-primary' : 'text-on-surface-variant/70'" />
          <span class="text-sm font-semibold tracking-wide">{{ tab.label }}</span>
          <div v-if="route.path === tab.path" class="ml-auto w-1.5 h-1.5 bg-brand-primary rounded-full"></div>
        </button>
      </li>
    </ul>

    <!-- Footer -->
    <div class="mt-auto flex flex-col gap-3 pt-4 border-t border-[#2a2a2a]/50">
      <div class="w-full bg-[#2a2a2a] text-on-surface-variant/50 font-bold text-sm py-3.5 rounded-lg border border-[#2d2d2d] flex justify-center items-center gap-2 cursor-not-allowed">
        <Siren class="w-5 h-5 text-on-surface-variant/40" />
        <span>SOS — Coming Soon</span>
      </div>
      <ul class="flex flex-col gap-1 mt-1">
        <li>
          <button @click="alert('Connecting you with University Transport Helpdesk Support (24/7)...')"
            class="w-full flex items-center gap-3.5 px-4 py-2.5 rounded-lg text-on-surface-variant/80 hover:text-on-surface hover:bg-[#2a2a2a]/40 text-left cursor-pointer text-sm font-medium border-none bg-transparent">
            <LifeBuoy class="w-5 h-5 text-on-surface-variant/60" />
            <span>Support</span>
          </button>
        </li>
        <li>
          <button @click="emit('logout')"
            class="w-full flex items-center gap-3.5 px-4 py-2.5 rounded-lg text-[#ffb4ab] hover:bg-[#93000a]/10 text-left cursor-pointer text-sm font-semibold border-none bg-transparent">
            <span>Logout</span>
          </button>
        </li>
      </ul>
    </div>
  </nav>
</template>
