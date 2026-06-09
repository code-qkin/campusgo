<script setup lang="ts">
import { ref } from 'vue';
import { ActiveTab, CampusAlert, User } from '../types';
import { 
  Navigation, 
  Bus, 
  Users, 
  ShieldCheck, 
  AlertTriangle, 
  Clock, 
  Shuffle, 
  Leaf, 
  Coffee, 
  ChevronRight
} from 'lucide-vue-next';

import { showAlert as alert } from '../alert';

const props = defineProps<{
  user: User
  alerts: CampusAlert[]
}>();

const emit = defineEmits(['change-tab']);

const destination = ref('');
const activeSearchTip = ref(false);

const handleGoSubmit = () => {
  if (!destination.value) {
    alert('Please specify a campus building, coordinate, or parking lot first.');
    return;
  }
  // Search simulation - routes selection
  alert(`Initiating route navigation from current location to "${destination.value}". Redirecting you to the Transit Routes view...`);
  emit('change-tab', ActiveTab.Routes);
};

const handleActionClick = (target: ActiveTab) => {
  emit('change-tab', target);
};
</script>

<template>
  <div class="flex-1 flex h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] overflow-hidden bg-[#131313] max-md:p-4 p-10 mt-16 max-md:ml-0 ml-[280px]">
    <div class="flex-1 overflow-y-auto max-md:pr-2 pr-4 flex max-md:flex-col gap-6 min-w-0">
      
      <!-- Left main content center block (65%) -->
      <div class="flex-1 flex flex-col gap-6 min-w-0">
        
        <!-- Welcome greeting header card -->
        <div>
          <h2 class="text-2xl md:text-3.5xl font-extrabold text-on-surface select-none leading-none">
            Good Morning, {{ user.fullName }}
          </h2>
          <p class="text-sm text-on-surface-variant font-light mt-1.5 opacity-90">
            Campus is currently operating on standard schedule.
          </p>
        </div>

        <!-- Main search card for destination -->
        <div class="relative w-full bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-5 shadow-2xl">
          <form @submit.prevent="handleGoSubmit" class="relative w-full flex items-center">
            <div class="absolute left-4 flex items-center pointer-events-none">
              <Navigation class="w-6 h-6 text-brand-primary fill-brand-primary" />
            </div>
            <input
              type="text"
              v-model="destination"
              @focus="activeSearchTip = true"
              @blur="setTimeout(() => activeSearchTip = false, 200)"
              class="w-full bg-[#131313] border border-[#222] rounded-xl py-4 pl-14 pr-24 text-base text-on-surface focus:outline-none focus:border-brand-primary transition-all placeholder:text-on-surface-variant/40 font-light"
              placeholder="Where are you heading next?"
            />
            <button 
              type="submit"
              class="absolute right-2.5 bg-brand-primary-container text-white font-bold text-xs px-6 py-2.5 rounded-lg hover:brightness-110 active:scale-95 transition-all cursor-pointer shadow-lg"
            >
              Go
            </button>
          </form>

          <!-- Simulated instant search suggestions overlay -->
          <div v-if="activeSearchTip" class="absolute left-5 right-5 top-20 bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg mt-1 p-2 shadow-2xl z-20 flex flex-col gap-0.5 animate-in fade-in duration-200">
            <p class="text-xxs uppercase tracking-wider text-on-surface-variant/40 px-3.5 py-1.5 font-bold">Suggested Locations</p>
            <button 
              v-for="(loc, i) in [
                'Library West Plaza (Red Line stop)',
                'Student Union South (Next departure Route A)',
                'Engineering Building Complex Center',
                'Whole Foods Market Campus Downtown'
              ]"
              :key="i" 
              type="button"
              @click="destination = loc"
              class="w-full text-left px-3.5 py-2 hover:bg-[#2a2a2a]/40 text-xs text-on-surface-variant hover:text-white rounded transition-colors font-medium cursor-pointer"
            >
              {{ loc }}
            </button>
          </div>
        </div>

        <!-- Quick Actions pillars -->
        <div>
          <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-4">Quick Actions</h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- Card 1: Open live system buses -->
            <div 
              @click="handleActionClick(ActiveTab.Routes)"
              class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-5 hover:border-brand-primary/40 transition-all cursor-pointer group active:scale-98"
            >
              <div class="w-12 h-12 rounded-full bg-brand-secondary/10 text-brand-secondary border border-brand-secondary/20 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                <Bus class="w-6 h-6" />
              </div>
              <h4 class="text-lg font-bold text-on-surface flex items-center gap-1">
                <span>Live Buses</span>
                <ChevronRight class="w-4 h-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all text-brand-primary" />
              </h4>
              <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed font-light opacity-80">
                Track campus shuttles in real-time on live GPS tracking.
              </p>
            </div>

            <!-- Card 2: Open peer carpool -->
            <div 
              @click="handleActionClick(ActiveTab.Carpool)"
              class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-5 hover:border-brand-primary/40 transition-all cursor-pointer group active:scale-98"
            >
              <div class="w-12 h-12 rounded-full bg-brand-tertiary/10 text-brand-tertiary border border-brand-tertiary/20 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                <Users class="w-6 h-6 text-brand-tertiary" />
              </div>
              <h4 class="text-lg font-bold text-on-surface flex items-center gap-1">
                <span>Carpool</span>
                <ChevronRight class="w-4 h-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all text-brand-primary" />
              </h4>
              <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed font-light opacity-80">
                Join or offer quick, secure shared rides with peers.
              </p>
            </div>

            <!-- Card 3: Open SafeWalk Escort request -->
            <div 
              @click="handleActionClick(ActiveTab.SafeWalk)"
              class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-5 hover:border-brand-primary/40 transition-all cursor-pointer group active:scale-98"
            >
              <div class="w-12 h-12 rounded-full bg-brand-primary/10 text-brand-primary border border-brand-primary/20 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                <ShieldCheck class="w-6 h-6 text-brand-primary" />
              </div>
              <h4 class="text-lg font-bold text-on-surface flex items-center gap-1">
                <span>SafeWalk</span>
                <ChevronRight class="w-4 h-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all text-brand-primary" />
              </h4>
              <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed font-light opacity-80">
                Request a certified buddy to escort you home safely.
              </p>
            </div>

          </div>
        </div>

        <!-- Campus Alerts Feed -->
        <div class="flex-1 flex flex-col min-h-[250px]">
          <div class="flex justify-between items-end mb-4">
            <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest">Campus Alerts</h3>
            <button 
              @click="alert('Alert logs: No previous critical warnings this semester.')"
              class="text-xs text-brand-primary hover:underline font-bold bg-transparent border-none cursor-pointer"
            >
              View All
            </button>
          </div>

          <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl overflow-hidden flex flex-col flex-grow">
            <div 
              v-for="alertItem in alerts" 
              :key="alertItem.id" 
              class="p-4 border-b border-[#2d2d2d]/60 last:border-b-0 hover:bg-[#2a2a2a]/45 transition-colors flex gap-4 items-start"
            >
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-1"
                :class="alertItem.type === 'delay' 
                  ? 'bg-brand-error/10 text-brand-error border border-brand-error/20' 
                  : alertItem.type === 'relocation'
                    ? 'bg-brand-tertiary/10 text-brand-tertiary border border-brand-tertiary/20'
                    : 'bg-brand-secondary/10 text-brand-secondary border border-brand-secondary/20'"
              >
                <AlertTriangle v-if="alertItem.type === 'delay'" class="w-5 h-5" />
                <Shuffle v-else-if="alertItem.type === 'relocation'" class="w-5 h-5" />
                <Clock v-else class="w-5 h-5" />
              </div>

              <div class="flex-1">
                <div class="flex justify-between items-start">
                  <h4 class="text-sm font-bold text-on-surface">{{ alertItem.title }}</h4>
                  <span class="text-xs text-on-surface-variant opacity-80">{{ alertItem.timeAgo }} {{ alertItem.timeUnit }}</span>
                </div>
                <p class="text-xs text-on-surface-variant mt-1 font-light leading-relaxed">
                  {{ alertItem.message }}
                </p>
                
                <div v-if="alertItem.delayValue" class="mt-3.5 inline-flex items-center gap-1.5 bg-brand-error/10 text-brand-error border border-brand-error/20 px-2.5 py-1 rounded text-xxs font-bold">
                  <Clock class="w-3.5 h-3.5" />
                  <span>{{ alertItem.delayValue }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right side informational widgets (35%) -->
      <div class="max-md:w-full w-80 shrink-0 flex flex-col gap-6">
        
        <!-- Section: Your Next Ride -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 relative overflow-hidden">
          <div class="absolute -right-6 -top-6 w-32 h-32 bg-brand-primary/5 rounded-full blur-2xl pointer-events-none"></div>
          
          <h3 class="text-xs text-on-surface-variant font-bold uppercase tracking-widest mb-2">Your Next Ride</h3>
          
          <div class="flex items-end gap-2 mt-4 select-none">
            <span class="text-5xl font-extrabold text-[#ffb4aa] leading-none tracking-tight">04</span>
            <span class="text-lg font-bold text-[#ffb4aa]/75 mb-1">min</span>
          </div>

          <p class="text-sm font-bold text-on-surface mt-3.5">Route A • Science Bldg</p>
          <p class="text-xs text-on-surface-variant font-light mt-0.5">Arriving at Stop 42</p>

          <button 
            @click="handleActionClick(ActiveTab.Routes)"
            class="w-full mt-6 bg-transparent border border-brand-primary text-brand-primary font-bold text-xs py-2.5 rounded-lg hover:bg-brand-primary hover:text-[#131313] transition-colors cursor-pointer active:scale-95"
          >
            View Live Map
          </button>
        </div>

        <!-- Section: Eco-Stats widget -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6">
          <h3 class="text-xs text-on-surface-variant font-bold uppercase tracking-widest mb-4">Eco-Stats</h3>
          
          <div class="flex items-center gap-4 select-none">
            <div class="w-14 h-14 rounded-full border-4 border-brand-tertiary-container/30 flex items-center justify-center shrink-0">
              <Leaf class="w-6 h-6 text-brand-tertiary fill-brand-tertiary" />
            </div>
            <div>
              <div class="text-2xl font-extrabold text-on-surface">42.5 kg</div>
              <div class="text-xs text-on-surface-variant font-light">CO₂ Saved This Month</div>
            </div>
          </div>

          <!-- Slider bar progression -->
          <div class="mt-5 w-full bg-[#353534] rounded-full h-2 overflow-hidden shadow-inner">
            <div class="bg-brand-tertiary h-full rounded-full" style="width: 75%"></div>
          </div>
          <p class="text-xxs font-bold text-brand-tertiary mt-2 text-right tracking-wide uppercase">Top 15% of Campus</p>
        </div>

        <!-- Section: Active Rewards -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 flex flex-col">
          <h3 class="text-xs text-on-surface-variant font-bold uppercase tracking-widest mb-4">Active Rewards</h3>
          
          <div class="flex items-center gap-3.5 bg-[#353534]/50 border border-[#2d2d2d] p-3.5 rounded-xl select-none mb-4">
            <div class="w-10 h-10 rounded bg-brand-primary-container/10 border border-brand-primary-container/20 text-brand-primary flex items-center justify-center shrink-0">
              <Coffee class="w-5 h-5 text-brand-primary" />
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-bold text-on-surface truncate">Free Coffee</div>
              <div class="text-xxs text-on-surface-variant truncate mt-0.5">250 pts required</div>
            </div>
            <div class="text-sm font-extrabold text-brand-primary">{{ user.points }}</div>
          </div>

          <button 
            @click="handleActionClick(ActiveTab.Rewards)"
            class="w-full text-center text-xs font-bold text-on-surface-variant hover:text-brand-primary transition-all underline bg-transparent border-none p-2 cursor-pointer outline-none"
          >
            Browse Reward Catalog
          </button>
        </div>

      </div>

    </div>
  </div>
</template>
