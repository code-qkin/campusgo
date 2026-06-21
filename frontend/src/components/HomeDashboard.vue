<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { ActiveTab, User } from '../types';
import { api } from '../api';
import { 
  Navigation, 
  Bus, 
  Users, 
  ShieldCheck, 
  Search,
  ChevronRight,
  Wrench,
  Car
} from 'lucide-vue-next';

const props = defineProps<{
  user: User
}>();

const emit = defineEmits(['change-tab']);

const myRides = ref<any[]>([]);
const isLoading = ref(false);

const fetchMyRides = async () => {
  isLoading.value = true;
  try {
    const data = await api.get('/rides/my');
    myRides.value = Array.isArray(data) ? data : [];
  } catch (e) {
    myRides.value = [];
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => fetchMyRides());

const handleActionClick = (target: ActiveTab) => {
  emit('change-tab', target);
};

const getGreeting = () => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good Morning';
  if (hour < 17) return 'Good Afternoon';
  return 'Good Evening';
};
</script>

<template>
  <div class="flex-1 flex h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] overflow-hidden bg-[#131313] max-md:p-4 p-10 mt-16 max-md:ml-0 ml-[280px]">
    <div class="flex-1 overflow-y-auto max-md:pr-2 pr-4 flex max-md:flex-col gap-6 min-w-0">

      <!-- Left main content -->
      <div class="flex-1 flex flex-col gap-6 min-w-0">

        <!-- Welcome -->
        <div>
          <h2 class="text-2xl md:text-3.5xl font-extrabold text-on-surface select-none leading-none">
            {{ getGreeting() }}, {{ user.fullName }}
          </h2>
          <p class="text-sm text-on-surface-variant font-light mt-1.5 opacity-90">
            Welcome back to CampusGo.
          </p>
        </div>

        <!-- Quick Actions -->
        <div>
          <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-4">Quick Actions</h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            <!-- Carpool -->
            <div @click="handleActionClick(ActiveTab.Carpool)"
              class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-5 hover:border-brand-primary/40 transition-all cursor-pointer group active:scale-98">
              <div class="w-12 h-12 rounded-full bg-brand-tertiary/10 text-brand-tertiary border border-brand-tertiary/20 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                <Users class="w-6 h-6 text-brand-tertiary" />
              </div>
              <h4 class="text-lg font-bold text-on-surface flex items-center gap-1">
                <span>Carpool</span>
                <ChevronRight class="w-4 h-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all text-brand-primary" />
              </h4>
              <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed font-light opacity-80">
                Find or create a shared ride across campus.
              </p>
            </div>

            <!-- Lost & Found -->
            <div @click="handleActionClick(ActiveTab.LostFound)"
              class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-5 hover:border-brand-primary/40 transition-all cursor-pointer group active:scale-98">
              <div class="w-12 h-12 rounded-full bg-[#ffb4aa]/10 text-[#ffb4aa] border border-[#ffb4aa]/20 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                <Search class="w-6 h-6" />
              </div>
              <h4 class="text-lg font-bold text-on-surface flex items-center gap-1">
                <span>Lost & Found</span>
                <ChevronRight class="w-4 h-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all text-brand-primary" />
              </h4>
              <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed font-light opacity-80">
                Report or find lost items on campus.
              </p>
            </div>

            <!-- SafeWalk - under maintenance -->
            <div @click="handleActionClick(ActiveTab.SafeWalk)"
              class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-5 hover:border-brand-primary/40 transition-all cursor-pointer group active:scale-98 relative">
              <span class="absolute top-3 right-3 text-[9px] font-bold px-2 py-0.5 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center gap-1">
                <Wrench class="w-2.5 h-2.5" /> Soon
              </span>
              <div class="w-12 h-12 rounded-full bg-brand-primary/10 text-brand-primary border border-brand-primary/20 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                <ShieldCheck class="w-6 h-6 text-brand-primary" />
              </div>
              <h4 class="text-lg font-bold text-on-surface flex items-center gap-1">
                <span>SafeWalk</span>
                <ChevronRight class="w-4 h-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all text-brand-primary" />
              </h4>
              <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed font-light opacity-80">
                Request a buddy to escort you home safely.
              </p>
            </div>

          </div>
        </div>

        <!-- My Active Ride -->
        <div class="flex-1 flex flex-col min-h-[250px]">
          <div class="flex justify-between items-end mb-4">
            <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest">My Rides</h3>
            <button @click="handleActionClick(ActiveTab.Carpool)"
              class="text-xs text-brand-primary hover:underline font-bold bg-transparent border-none cursor-pointer">
              View All
            </button>
          </div>

          <div v-if="isLoading" class="flex items-center justify-center py-12 bg-[#201f1f] border border-[#2d2d2d] rounded-xl">
            <div class="w-5 h-5 rounded-full border-2 border-brand-primary/20 border-t-brand-primary animate-spin"></div>
          </div>

          <div v-else-if="myRides.length === 0"
            class="bg-[#201f1f] border border-dashed border-[#2d2d2d] rounded-xl p-10 text-center flex-1 flex flex-col items-center justify-center">
            <Car class="w-8 h-8 text-on-surface-variant/30 mb-3" />
            <p class="text-sm text-on-surface font-semibold">No active rides.</p>
            <p class="text-xs text-on-surface-variant mt-1">Find or create a ride to get started.</p>
          </div>

          <div v-else class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl overflow-hidden flex flex-col flex-grow">
            <div v-for="ride in myRides" :key="ride.id"
              class="p-4 border-b border-[#2d2d2d]/60 last:border-b-0 hover:bg-[#2a2a2a]/45 transition-colors flex gap-4 items-center cursor-pointer"
              @click="handleActionClick(ActiveTab.Carpool)">
              <div class="w-10 h-10 rounded-full bg-brand-tertiary/10 text-brand-tertiary border border-brand-tertiary/20 flex items-center justify-center shrink-0">
                <Car class="w-5 h-5" />
              </div>
              <div class="flex-1">
                <h4 class="text-sm font-bold text-on-surface">{{ ride.route?.name || 'Unknown Route' }}</h4>
                <p class="text-xs text-on-surface-variant mt-0.5 capitalize">{{ ride.status }}</p>
              </div>
              <ChevronRight class="w-4 h-4 text-on-surface-variant" />
            </div>
          </div>
        </div>

      </div>

      <!-- Right sidebar -->
      <div class="max-md:w-full w-80 shrink-0 flex flex-col gap-6">

        <!-- Rewards - under maintenance -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 relative">
          <span class="absolute top-4 right-4 text-[9px] font-bold px-2 py-0.5 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center gap-1">
            <Wrench class="w-2.5 h-2.5" /> Coming Soon
          </span>
          <h3 class="text-xs text-on-surface-variant font-bold uppercase tracking-widest mb-3">Rewards</h3>
          <p class="text-xs text-on-surface-variant font-light leading-relaxed">
            Points and reward redemption are being built. Check back soon.
          </p>
        </div>

        <!-- Quick links -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6">
          <h3 class="text-xs text-on-surface-variant font-bold uppercase tracking-widest mb-4">Quick Links</h3>
          <div class="space-y-2">
            <button @click="handleActionClick(ActiveTab.History)"
              class="w-full text-left text-xs font-semibold text-on-surface-variant hover:text-on-surface transition-all bg-transparent border-none p-2.5 rounded-lg hover:bg-[#2a2a2a] cursor-pointer flex items-center justify-between">
              Activity History
              <ChevronRight class="w-3.5 h-3.5" />
            </button>
            <button @click="handleActionClick(ActiveTab.LostFound)"
              class="w-full text-left text-xs font-semibold text-on-surface-variant hover:text-on-surface transition-all bg-transparent border-none p-2.5 rounded-lg hover:bg-[#2a2a2a] cursor-pointer flex items-center justify-between">
              Lost & Found
              <ChevronRight class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>