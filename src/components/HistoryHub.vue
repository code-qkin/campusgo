<script setup lang="ts">
import { mockHistoryEvents } from '../data';
import { 
  Bus, 
  Users, 
  ShieldCheck, 
  Search, 
  Trophy, 
  ExternalLink 
} from 'lucide-vue-next';

import { showAlert as alert } from '../alert';
</script>

<template>
  <div class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">
    
    <section class="flex-1 max-md:p-4 p-10 overflow-y-auto">
      <header class="mb-8 select-none">
        <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Activity History</h2>
        <p class="text-sm text-on-surface-variant font-light">Monitor previous campus transits, shared pooling matches, escort logs, and claimed coupons.</p>
      </header>

      <!-- History records stack item list -->
      <div class="space-y-4">
        <div 
          v-for="event in mockHistoryEvents" 
          :key="event.id"
          class="bg-[#201f1f]/60 hover:bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-5 flex items-center transition-all justify-between"
        >
          <div class="flex items-center gap-4 min-w-0">
            <div class="w-10 h-10 rounded-full bg-[#131313] border border-[#2d2d2d] flex items-center justify-center shrink-0">
              <Bus v-if="event.type === 'Transit'" class="w-5 h-5 text-brand-primary" />
              <Users v-else-if="event.type === 'Carpool'" class="w-5 h-5 text-brand-secondary" />
              <ShieldCheck v-else-if="event.type === 'SafeWalk'" class="w-5 h-5 text-brand-tertiary" />
              <Search v-else-if="event.type === 'LostFound'" class="w-5 h-5 text-[#ffb4aa]" />
              <Trophy v-else class="w-5 h-5 text-brand-secondary" />
            </div>
            <div class="min-w-0">
              <span class="text-[10px] uppercase font-bold text-on-surface-variant/75 tracking-wider">{{ event.type }}</span>
              <h3 class="text-sm font-bold text-[#fff] mt-0.5 truncate">{{ event.title }}</h3>
              <p class="text-xxs text-on-surface-variant mt-0.5 truncate font-light">{{ event.detail }}</p>
            </div>
          </div>

          <div class="text-right shrink-0 ml-4 select-none">
            <div class="text-xs text-on-surface-variant/70 font-semibold">{{ event.date }}</div>
            <div class="flex items-center gap-2 mt-1 justify-end">
              <span v-if="event.cost" class="text-xxs bg-[#353534] px-1.5 py-0.5 text-on-surface rounded font-bold">{{ event.cost }}</span>
              <span class="text-xxs px-2 py-0.5 rounded bg-brand-tertiary/10 text-brand-tertiary border border-brand-tertiary/15 font-bold uppercase">
                {{ event.status }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- System telemetry audit disclaimer -->
      <div class="mt-8 p-4 bg-[#1e1e1e] border border-dashed border-[#2d2d2d] rounded-xl flex items-center justify-between select-none">
        <p class="text-xxs text-on-surface-variant leading-relaxed font-light">
          Need a formal university transportation receipt export for expense records or co-op subsidy filing?
        </p>
        <button 
          type="button" 
          @click="alert('Dispatching formal CSV/PDF transport audit logs report to university registered mail inbox...')"
          class="text-xxs bg-transparent border-none text-brand-primary hover:underline font-extrabold flex items-center gap-1 cursor-pointer"
        >
          <span>Export Report Logs</span>
          <ExternalLink class="w-3.5 h-3.5 text-brand-primary" />
        </button>
      </div>

    </section>
  </div>
</template>
