<script setup lang="ts">
import { ref, computed } from 'vue';
import { Bell, HelpCircle, ChevronDown, LogOut, User as UserIcon, Search, Menu } from 'lucide-vue-next';
import { ActiveTab, User } from '../types';

import { showAlert as alert } from '../alert';

const props = defineProps<{
  activeTab: ActiveTab
  user: User
}>();

const emit = defineEmits(['logout', 'search-change', 'toggle-menu']);

const profileOpen = ref(false);
const searchValue = ref('');

const getTabTitle = computed(() => {
  switch (props.activeTab) {
    case ActiveTab.Home:
      return 'Live Network Map';
    case ActiveTab.Routes:
      return 'Live Network Map';
    case ActiveTab.Carpool:
      return 'Carpool Hub';
    case ActiveTab.SafeWalk:
      return 'SafeWalk Hub';
    case ActiveTab.LostFound:
      return 'Lost & Found Hub';
    case ActiveTab.Rewards:
      return 'Rewards Catalog';
    case ActiveTab.History:
      return 'Activity Logs';
    case ActiveTab.Settings:
      return 'User Preferences';
    default:
      return 'CampusGo Transit';
  }
});

const getSearchPlaceholder = computed(() => {
  switch (props.activeTab) {
    case ActiveTab.Home:
      return 'Search routes, buildings...';
    case ActiveTab.Routes:
      return 'Search route, line, or stop...';
    case ActiveTab.Carpool:
      return 'Search rides, locations...';
    case ActiveTab.SafeWalk:
      return 'Search locations or buddies...';
    case ActiveTab.LostFound:
      return 'Search lost items...';
    default:
      return 'Search CampusGo...';
  }
});

const handleSearch = (e: Event) => {
  const target = e.target as HTMLInputElement;
  emit('search-change', target.value);
};
</script>

<template>
  <header class="fixed top-0 right-0 max-md:w-full w-[calc(100%-280px)] h-16 bg-[#131313] border-b border-[#2d2d2d] flex justify-between items-center max-md:px-4 px-10 z-30">
    <div class="flex items-center gap-6">
      <!-- Mobile Menu Toggle -->
      <button 
        @click="emit('toggle-menu')"
        class="hidden max-md:flex w-10 h-10 rounded-full items-center justify-center text-on-surface-variant hover:bg-[#2a2a2a]/40 transition-all cursor-pointer border-none bg-transparent"
      >
        <Menu class="w-6 h-6" />
      </button>

      <h2 class="text-xl font-bold text-on-surface select-none hidden lg:block tracking-wide">
        {{ getTabTitle }}
      </h2>

      <!-- Dynamic Context Search bar -->
      <div class="relative w-64 md:w-80 max-md:hidden">
        <Search class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant/70" />
        <input
          type="text"
          v-model="searchValue"
          @input="handleSearch"
          class="w-full bg-[#201f1f] text-on-surface border border-[#2d2d2d] rounded-full pl-11 pr-4 py-1.5 focus:outline-none focus:border-brand-primary-container transition-colors text-sm placeholder:text-on-surface-variant/40"
          :placeholder="getSearchPlaceholder"
        />
      </div>
    </div>

    <div class="flex items-center gap-4 relative">
      <!-- Alerts Bell Button -->
      <button 
        @click="alert('Notification panel: No new critical transit alerts.')"
        class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-[#2a2a2a]/40 hover:text-brand-primary transition-all relative cursor-pointer"
      >
        <Bell class="w-5 h-5" />
        <span class="absolute top-2 right-2 w-2 h-2 bg-brand-primary rounded-full animate-ping"></span>
        <span class="absolute top-2 right-2 w-2 h-2 bg-brand-primary rounded-full"></span>
      </button>

      <!-- Support Help Center -->
      <button 
        @click="alert('Help Center: Accessing CampusGo Frequently Asked Questions.')"
        class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-[#2a2a2a]/40 hover:text-brand-primary transition-all cursor-pointer"
      >
        <HelpCircle class="w-5 h-5" />
      </button>

      <div class="w-px h-6 bg-[#2d2d2d] mx-1"></div>

      <!-- User Account Avatar Trigger -->
      <button 
        @click="profileOpen = !profileOpen"
        class="flex items-center gap-3 hover:opacity-85 transition-opacity cursor-pointer text-left select-none outline-none"
      >
        <div class="w-8 h-8 rounded-full overflow-hidden border border-[#2d2d2d]">
          <img 
            alt="User Profile" 
            class="w-full h-full object-cover" 
            :src="user.avatar" 
          />
        </div>
        <span class="hidden md:inline text-xs font-semibold text-on-surface select-none">
          {{ user.fullName }}
        </span>
        <ChevronDown class="w-4 h-4 text-on-surface-variant/70" />
      </button>

      <!-- Dropdown overlay -->
      <template v-if="profileOpen">
        <div class="fixed inset-0 z-40 bg-transparent" @click="profileOpen = false"></div>
        <div class="absolute right-0 top-12 w-52 bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg p-2 shadow-2xl z-50 flex flex-col gap-1">
          <div class="px-3.5 py-2.5 border-b border-[#2d2d2d] select-none">
            <p class="text-sm text-white font-bold">{{ user.fullName }}</p>
            <p class="text-xxs text-on-surface-variant/70 font-medium truncate mt-0.5">{{ user.email }}</p>
            <p class="inline-flex items-center gap-1.5 mt-1.5 px-2 py-0.5 bg-brand-secondary/15 border border-brand-secondary/30 rounded text-xxs text-brand-secondary font-bold">
              {{ user.points }} points
            </p>
          </div>

          <button 
            @click="profileOpen = false; alert(`Viewing account as ${user.fullName}`);"
            class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-xs text-on-surface-variant hover:text-white hover:bg-[#2a2a2a]/40 text-left font-medium cursor-pointer"
          >
            <UserIcon class="w-4 h-4" />
            <span>My Profile</span>
          </button>
          
          <button 
            @click="profileOpen = false; emit('logout');"
            class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-xs text-brand-error hover:bg-brand-error-container/10 text-left font-bold cursor-pointer"
          >
            <LogOut class="w-4 h-4" />
            <span>Logout</span>
          </button>
        </div>
      </template>
    </div>
  </header>
</template>
