<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { api } from '../api';
import { Car, Bike, MapPin, Flag, Clock, CheckCircle, ExternalLink } from 'lucide-vue-next';
import { showAlert as alert } from '../alert';

const rides = ref<any[]>([]);
const isLoading = ref(false);

const fetchHistory = async () => {
  isLoading.value = true;
  try {
    const data = await api.get('/rides/history');
    rides.value = Array.isArray(data) ? data : [];
  } catch (e) {
    alert('Failed to fetch history');
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => fetchHistory());
</script>

<template>
  <div class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">

    <section class="flex-1 max-md:p-4 p-10 overflow-y-auto">
      <header class="mb-8 select-none">
        <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Activity History</h2>
        <p class="text-sm text-on-surface-variant font-light">Your completed campus rides.</p>
      </header>

      <!-- Loading -->
      <div v-if="isLoading" class="flex items-center justify-center py-20">
        <div class="w-6 h-6 rounded-full border-2 border-brand-primary/20 border-t-brand-primary animate-spin"></div>
      </div>

      <!-- Empty -->
      <div v-else-if="rides.length === 0"
        class="bg-[#201f1f] border border-dashed border-[#2d2d2d] rounded-xl p-12 text-center">
        <CheckCircle class="w-10 h-10 text-on-surface-variant/20 mx-auto mb-3" />
        <p class="text-sm text-on-surface font-semibold">No completed rides yet.</p>
        <p class="text-xs text-on-surface-variant mt-1.5">Your ride history will appear here.</p>
      </div>

      <!-- Rides list -->
      <div v-else class="space-y-4">
        <div v-for="ride in rides" :key="ride.id"
          class="bg-[#201f1f]/60 hover:bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-5 transition-all">

          <div class="flex items-start justify-between gap-4">
            <!-- Icon + route info -->
            <div class="flex items-start gap-4 min-w-0">
              <div class="w-10 h-10 rounded-full bg-[#131313] border border-[#2d2d2d] flex items-center justify-center shrink-0 mt-0.5">
                <Car v-if="ride.vehicle_type === 'car'" class="w-5 h-5 text-brand-secondary" />
                <Bike v-else class="w-5 h-5 text-brand-secondary" />
              </div>
              <div class="min-w-0">
                <span class="text-[10px] uppercase font-bold text-on-surface-variant/75 tracking-wider">
                  {{ ride.ride_type }} ride
                </span>
                <h3 class="text-sm font-bold text-on-surface mt-0.5">{{ ride.route?.name || 'Unknown Route' }}</h3>
                <div class="flex items-center gap-1.5 text-xs text-on-surface-variant mt-1.5">
                  <MapPin class="w-3 h-3 shrink-0" />
                  <span>{{ ride.route?.stops?.[0]?.name }}</span>
                  <span>→</span>
                  <Flag class="w-3 h-3 shrink-0 text-brand-primary" />
                  <span>{{ ride.route?.stops?.[ride.route.stops.length - 1]?.name }}</span>
                </div>
              </div>
            </div>

            <!-- Date + status -->
            <div class="text-right shrink-0 select-none">
              <div class="flex items-center gap-1.5 text-xs text-on-surface-variant/70 font-semibold justify-end">
                <Clock class="w-3.5 h-3.5" />
                {{ new Date(ride.updated_at).toLocaleDateString() }}
              </div>
              <div class="mt-2">
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-brand-tertiary/10 text-brand-tertiary border border-brand-tertiary/15 font-bold uppercase flex items-center gap-1 justify-end">
                  <CheckCircle class="w-3 h-3" />
                  Completed
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Export footer -->
      <div class="mt-8 p-4 bg-[#1e1e1e] border border-dashed border-[#2d2d2d] rounded-xl flex items-center justify-between select-none">
        <p class="text-xs text-on-surface-variant leading-relaxed font-light">
          Need a formal university transportation receipt for expense records?
        </p>
        <button type="button"
          @click="alert('Export feature coming soon.')"
          class="text-xs bg-transparent border-none text-brand-primary hover:underline font-bold flex items-center gap-1 cursor-pointer shrink-0 ml-4">
          <span>Export Logs</span>
          <ExternalLink class="w-3.5 h-3.5" />
        </button>
      </div>

    </section>
  </div>
</template>