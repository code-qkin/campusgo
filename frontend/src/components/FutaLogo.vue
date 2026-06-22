<script setup lang="ts">
import { ref } from 'vue';

defineProps<{ size?: string }>();
const failed = ref(false);
</script>

<template>
  <img
    v-if="!failed"
    src="https://upload.wikimedia.org/wikipedia/en/7/7a/Federal_University_of_Technology%2C_Akure_logo.png"
    alt="FUTA"
    :class="size || 'w-10 h-10'"
    class="object-contain"
    @error="failed = true"
  />
  <!-- Fallback: green/gold badge with FUTA initials -->
  <div
    v-else
    :class="size || 'w-10 h-10'"
    class="rounded-lg bg-gradient-to-br from-[#006400] to-[#8B6914] flex items-center justify-center shrink-0"
  >
    <span class="text-white font-black text-[10px] tracking-tight leading-none text-center">FUTA</span>
  </div>
</template>
