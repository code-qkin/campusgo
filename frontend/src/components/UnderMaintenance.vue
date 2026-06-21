<script setup lang="ts">
import { Wrench } from 'lucide-vue-next';

defineProps<{
  title?: string;
  message?: string;
}>();
</script>

<template>
  <div class="flex-1 flex items-center justify-center h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] mt-16 max-md:ml-0 ml-[280px] bg-[#131313] p-10">
    <div class="text-center max-w-md">
      <div class="w-16 h-16 rounded-full bg-brand-secondary/10 border border-brand-secondary/20 flex items-center justify-center mx-auto mb-6">
        <Wrench class="w-7 h-7 text-brand-secondary" />
      </div>
      <h2 class="text-xl font-bold text-on-surface mb-2">
        {{ title || 'Under Maintenance' }}
      </h2>
      <p class="text-sm text-on-surface-variant leading-relaxed">
        {{ message || 'This feature is being rebuilt and will be available soon. Thanks for your patience.' }}
      </p>
    </div>
  </div>
</template>