<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Bus, MapPin, Shield, CheckCircle, Navigation, ArrowRight } from 'lucide-vue-next';

const emit = defineEmits(['get-started']);

const mousePos = ref({ x: 0, y: 0 });

const handleMouseMove = (e: MouseEvent) => {
  const x = (window.innerWidth - e.clientX * 2) / 100;
  const y = (window.innerHeight - e.clientY * 2) / 100;
  mousePos.value = { x, y };
};

onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove);
});

onUnmounted(() => {
  window.removeEventListener('mousemove', handleMouseMove);
});
</script>

<template>
  <div class="relative w-full h-screen overflow-hidden bg-brand-bg text-on-surface flex items-center justify-center font-sans">
    <!-- Background Image Layer with Parallax movement -->
    <div 
      class="absolute inset-0 z-0 transition-transform duration-300 ease-out scale-105"
      :style="{
        transform: `translateX(${mousePos.x}px) translateY(${mousePos.y}px)`,
      }"
    >
      <img 
        alt="Modern Campus at Dusk" 
        class="w-full h-full object-cover filter brightness-45 contrast-115" 
        src="https://lh3.googleusercontent.com/aida-public/AB6AXuD1Efn7cvAro7kwwIZ5HrL2aGv_EcoLod2q1N8DBD4Q7ZDljz9JAeCjgygelGSwDSP-BourEAs0PazYCvpY6q6zaCyeqQe1lHf_yn3F_Lqh020Jra9t8WKVYFMun2pyNttWxre8yj51Zw8a9TsRfQTya4ze-TX9rgVOwc90ZznBSCaK4qKkZmim8hsu4n2qCiU3KjNYVoHZYEfocFlzQ7IXkShpjMUzIdBdZY7sfydjOipnHeo2ctfbqvUykD-GHoref9u4kL8MlFY"
      />
      <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/80 to-[#131313]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_30%,rgba(255,95,82,0.06)_0%,transparent_40%),radial-gradient(circle_at_80%_70%,rgba(221,183,255,0.06)_0%,transparent_40%)]"></div>
    </div>

    <!-- Main Content Container -->
    <main class="relative z-10 flex flex-col items-center justify-center text-center px-6 max-w-4xl max-h-screen pt-8 md:pt-0">
      <!-- Animated Logo Section -->
      <div class="mb-5 md:mb-8 flex items-center justify-center">
        <div class="relative group cursor-pointer">
          <!-- Outer Glow -->
          <div class="absolute -inset-6 bg-brand-primary-container/20 blur-3xl rounded-full opacity-60 group-hover:opacity-100 transition-opacity duration-750"></div>
          <!-- Logo Frame -->
          <div class="relative bg-[#1a1a1a]/85 border border-[#2a2a2a] max-md:p-5 p-8 rounded-full shadow-[0_0_40px_10px_rgba(255,95,82,0.15)] transform transition-transform group-hover:scale-105 duration-500">
            <Bus class="max-md:w-14 max-md:h-14 w-20 h-20 text-brand-primary duration-500" />
            <div class="absolute -top-1 -right-1 animate-bounce duration-1000">
              <MapPin class="w-10 h-10 text-brand-secondary fill-brand-secondary" />
            </div>
          </div>
        </div>
      </div>

      <!-- Typography Section -->
      <div class="space-y-3">
        <h1 class="font-display text-4xl md:text-6xl font-extrabold text-on-surface tracking-tighter drop-shadow-lg">
          CampusGo
        </h1>
        <p class="font-sans text-base md:text-xl text-on-surface-variant max-w-lg mx-auto opacity-95 leading-relaxed font-light">
          Safe, reliable transit across your university.
        </p>
      </div>

      <!-- CTA Action -->
      <div class="mt-7 md:mt-10 group">
        <button 
          id="btn-get-started"
          @click="emit('get-started')"
          class="group relative flex items-center gap-3 bg-[#ff5f52] hover:bg-[#ff786d] text-white px-10 py-5 rounded-lg font-semibold text-base transition-all duration-300 hover:shadow-[0_0_35px_rgba(255,95,82,0.45)] overflow-hidden active:scale-95 cursor-pointer"
        >
          <!-- Shift background shine -->
          <div class="absolute inset-0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
          <span class="relative z-10 font-bold tracking-wide">Get Started</span>
          <ArrowRight class="w-5 h-5 relative z-10 transition-transform group-hover:translate-x-1" />
        </button>
      </div>

      <!-- Secondary Info Pillars -->
      <div class="mt-8 md:mt-16 grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 w-full max-w-3xl">
        <div class="flex items-center gap-4 px-6 py-4 rounded-xl bg-[#2a2a2a]/40 border border-[#222] backdrop-blur-md hover:border-brand-tertiary/20 transition-all duration-300">
          <CheckCircle class="w-6 h-6 text-brand-tertiary shrink-0" />
          <span class="text-sm font-medium text-on-surface-variant text-left">Verified Routes</span>
        </div>
        <div class="flex items-center gap-4 px-6 py-4 rounded-xl bg-[#2a2a2a]/40 border border-[#222] backdrop-blur-md hover:border-brand-secondary/20 transition-all duration-300">
          <Navigation class="w-6 h-6 text-brand-secondary shrink-0" />
          <span class="text-sm font-medium text-on-surface-variant text-left">Real-time Tracking</span>
        </div>
        <div class="flex items-center gap-4 px-6 py-4 rounded-xl bg-[#2a2a2a]/40 border border-[#222] backdrop-blur-md hover:border-brand-primary-container/20 transition-all duration-300">
          <Shield class="w-6 h-6 text-brand-primary shrink-0" />
          <span class="text-sm font-medium text-on-surface-variant text-left">Safety First</span>
        </div>
      </div>
    </main>

    <!-- Footer Decorative Attribution -->
    <footer class="fixed bottom-6 left-10 right-10 flex justify-between items-center z-10 pointer-events-none">
      <div class="text-on-surface-variant/40 text-xs font-semibold uppercase tracking-wider">
        © 2024 CampusGo Infrastructure
      </div>
      <div class="flex items-center gap-3 bg-[#1e1e1e]/60 border border-[#222] px-3 py-1.5 rounded-full backdrop-blur-md">
        <div class="w-2.5 h-2.5 rounded-full bg-brand-tertiary animate-pulse"></div>
        <span class="text-on-surface-variant text-xs font-semibold">Live Status: Operational</span>
      </div>
    </footer>
  </div>
</template>
