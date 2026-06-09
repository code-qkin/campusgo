<script setup lang="ts">
import { activeAlerts } from '../alert';
import { Info, X } from 'lucide-vue-next';

const removeAlert = (id: number) => {
  activeAlerts.value = activeAlerts.value.filter(a => a.id !== id);
};
</script>

<template>
  <div class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-3 w-full max-w-md px-4 pointer-events-none">
    <TransitionGroup name="alert-list">
      <div 
        v-for="alert in activeAlerts" 
        :key="alert.id"
        class="bg-[#201f1f] border-l-4 border-brand-primary text-on-surface p-4 rounded shadow-2xl flex items-start gap-3 pointer-events-auto border-y border-r border-y-[#2d2d2d] border-r-[#2d2d2d]"
      >
        <Info class="w-5 h-5 text-brand-primary shrink-0 mt-0.5" />
        <p class="text-sm font-medium leading-relaxed flex-1 whitespace-pre-wrap">{{ alert.message }}</p>
        <button @click="removeAlert(alert.id)" class="text-on-surface-variant hover:text-white shrink-0 cursor-pointer">
          <X class="w-4 h-4" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.alert-list-enter-active,
.alert-list-leave-active {
  transition: all 0.3s ease;
}
.alert-list-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}
.alert-list-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
