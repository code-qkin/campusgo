<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router';
import { User } from '../types';
import { initialUser } from '../data';
import { api } from '../api';
import { showAlert as alert } from '../alert';
import { customConfirm } from '../modal';

import Sidebar from '../components/Sidebar.vue';
import Header from '../components/Header.vue';

import { Siren, Mail, RefreshCw } from 'lucide-vue-next';

const router = useRouter();
const route  = useRoute();

// ── User state ────────────────────────────────────────────────────────────────
const storedRaw  = localStorage.getItem('campusgo_user');
const storedUser = storedRaw ? JSON.parse(storedRaw) : null;

const user = ref<User>(storedUser ? {
  ...initialUser,
  fullName: storedUser.full_name || initialUser.fullName,
  email:    storedUser.email     || initialUser.email,
  points:   storedUser.points    || 0,
} : initialUser);

const updateUser = (updated: Partial<User>) => {
  user.value = { ...user.value, ...updated };
};

// ── Email verification banner ─────────────────────────────────────────────────
const emailVerifiedAt    = ref<string | null>(storedUser?.email_verified_at ?? null);
const showVerifyBanner   = ref(!emailVerifiedAt.value);
const resendVerifyLoading = ref(false);
const verifySuccess      = ref(false);

onMounted(() => {
  // ?verified=1 redirect from backend
  const params = new URLSearchParams(window.location.search);
  if (params.get('verified') === '1') {
    emailVerifiedAt.value  = new Date().toISOString();
    showVerifyBanner.value = false;
    verifySuccess.value    = true;
    const stored = JSON.parse(localStorage.getItem('campusgo_user') || '{}');
    localStorage.setItem('campusgo_user', JSON.stringify({ ...stored, email_verified_at: emailVerifiedAt.value }));
    window.history.replaceState({}, '', window.location.pathname);
    setTimeout(() => { verifySuccess.value = false; }, 4000);
  }
});

const resendVerification = async () => {
  resendVerifyLoading.value = true;
  try {
    await api.post('/email/resend', {});
    alert('Verification email resent. Check your inbox.');
  } catch (e: any) {
    alert(e.message || 'Failed to resend.');
  } finally {
    resendVerifyLoading.value = false;
  }
};

// ── Logout ────────────────────────────────────────────────────────────────────
const handleLogout = async () => {
  const confirmed = await customConfirm('Logout', 'Are you sure you want to log out?');
  if (!confirmed) return;
  try { await api.post('/logout', {}); } catch { /* already invalid */ }
  localStorage.removeItem('campusgo_token');
  localStorage.removeItem('campusgo_user');
  router.push('/welcome');
};

// ── Mobile menu ───────────────────────────────────────────────────────────────
const mobileMenuOpen = ref(false);

// ── SOS ───────────────────────────────────────────────────────────────────────
const sosActive     = ref(false);
const sosCountdown  = ref(5);
const sosDispatched = ref(false);
let timerId: ReturnType<typeof setInterval> | null = null;

const triggerSOS = () => {
  sosActive.value = true; sosCountdown.value = 5; sosDispatched.value = false;
  if (timerId) clearInterval(timerId);
  timerId = setInterval(() => {
    if (sosCountdown.value > 0) { sosCountdown.value--; }
    else { sosDispatched.value = true; clearInterval(timerId!); timerId = null; }
  }, 1000);
};
const cancelSOS = () => {
  sosActive.value = false; sosCountdown.value = 5; sosDispatched.value = false;
  if (timerId) { clearInterval(timerId); timerId = null; }
};

const navigate = (path: string) => {
  mobileMenuOpen.value = false;
  router.push(path);
};
</script>

<template>
  <div class="min-h-screen bg-[#131313] text-on-surface flex font-sans overflow-hidden">

    <Sidebar @trigger-sos="triggerSOS" @logout="handleLogout" />

    <div class="flex-1 flex flex-col min-w-0">
      <Header :user="user" @logout="handleLogout" @toggle-menu="mobileMenuOpen = !mobileMenuOpen" />

      <!-- Email verification banner -->
      <div v-if="showVerifyBanner"
        class="fixed top-16 left-0 md:left-[280px] right-0 z-20 bg-brand-secondary/10 border-b border-brand-secondary/25 px-6 py-2.5 flex items-center gap-3">
        <Mail class="w-4 h-4 text-brand-secondary shrink-0" />
        <p class="text-xs text-on-surface-variant flex-1">
          <span class="font-bold text-brand-secondary">Verify your email</span> — check your inbox for the verification link we sent to
          <span class="font-semibold">{{ user.email }}</span>.
        </p>
        <button @click="resendVerification" :disabled="resendVerifyLoading"
          class="text-xs text-brand-secondary font-bold hover:underline flex items-center gap-1 bg-transparent border-none cursor-pointer disabled:opacity-50 shrink-0">
          <RefreshCw class="w-3 h-3" :class="resendVerifyLoading ? 'animate-spin' : ''" />
          Resend
        </button>
        <button @click="showVerifyBanner = false"
          class="text-on-surface-variant/50 hover:text-on-surface text-xs bg-transparent border-none cursor-pointer shrink-0">✕</button>
      </div>

      <!-- Email verified success flash -->
      <div v-if="verifySuccess"
        class="fixed top-16 left-0 md:left-[280px] right-0 z-20 bg-brand-tertiary/15 border-b border-brand-tertiary/30 px-6 py-2.5">
        <span class="text-xs text-brand-tertiary font-bold">✓ Email verified! Your account is fully active.</span>
      </div>

      <main class="flex-1 min-h-0 max-md:pb-16 pt-16" :class="showVerifyBanner ? 'md:pt-[4.5rem]' : ''">
        <RouterView v-slot="{ Component }">
          <component :is="Component" :user="user" @update-user="updateUser" @change-tab="navigate" />
        </RouterView>
      </main>
    </div>

    <!-- Mobile Bottom Nav -->
    <div class="hidden max-md:flex fixed bottom-0 left-0 right-0 h-16 bg-[#1c1b1b] border-t border-[#2d2d2d] justify-around items-center z-50 shadow-[0_-4px_24px_rgba(0,0,0,0.5)]">
      <button @click="navigate('/home')" class="flex flex-col items-center p-2"
        :class="route.path === '/home' ? 'text-brand-primary' : 'text-on-surface-variant'">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
      </button>
      <button @click="navigate('/carpool')" class="flex flex-col items-center p-2"
        :class="route.path === '/carpool' ? 'text-brand-primary' : 'text-on-surface-variant'">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>
        </svg>
      </button>
      <!-- SOS -->
      <button @click="triggerSOS"
        class="flex flex-col items-center p-3 -mt-6 bg-[#93000a] rounded-full border-4 border-[#131313] text-[#ffb4ab] shadow-lg cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 12.2V6.5a5 5 0 0 1 10 0v5.8"/><path d="M5.7 15a4.3 4.3 0 0 0 .8 8.6h11a4.3 4.3 0 0 0 .8-8.6c0-3.9-3.5-7.4-6.3-7.4S5.7 11.1 5.7 15Z"/><circle cx="12" cy="9" r="1"/>
        </svg>
      </button>
      <button @click="navigate('/lost-found')" class="flex flex-col items-center p-2"
        :class="route.path === '/lost-found' ? 'text-brand-primary' : 'text-on-surface-variant'">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
      </button>
      <button @click="navigate('/settings')" class="flex flex-col items-center p-2"
        :class="route.path === '/settings' ? 'text-brand-primary' : 'text-on-surface-variant'">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>
        </svg>
      </button>
    </div>

    <!-- Mobile Hamburger Menu Overlay -->
    <div v-if="mobileMenuOpen"
      class="hidden max-md:block fixed inset-0 z-[60] bg-[#131313] animate-in slide-in-from-left duration-200">
      <div class="flex flex-col h-full">
        <div class="h-16 flex items-center justify-between px-4 border-b border-[#2d2d2d] shrink-0">
          <h1 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-brand-primary to-brand-secondary tracking-tighter">CampusGo</h1>
          <button @click="mobileMenuOpen = false" class="p-2 text-on-surface-variant hover:text-white border-none bg-transparent cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
          <button v-for="item in [
            { path: '/home', label: 'Home Dashboard' },
            { path: '/carpool', label: 'Carpool Hub' },
            { path: '/lost-found', label: 'Lost & Found' },
            { path: '/history', label: 'Activity Logs' },
            { path: '/settings', label: 'Settings' },
          ]" :key="item.path" @click="navigate(item.path)"
            class="w-full flex items-center gap-4 px-4 py-3 rounded-lg text-sm font-bold transition-all border-none bg-transparent cursor-pointer"
            :class="route.path === item.path ? 'bg-brand-primary-container text-white' : 'text-on-surface hover:bg-[#201f1f]'">
            {{ item.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- SOS Modal -->
    <div v-if="sosActive" class="fixed inset-0 z-[100] flex items-center justify-center max-md:p-4 p-8 select-none">
      <div class="absolute inset-0 bg-black/95 backdrop-blur-md"></div>
      <div class="relative bg-[#201f1f] border border-[#ff5f52]/40 rounded-2xl max-md:p-6 p-10 max-w-[500px] w-full text-center shadow-[0_0_60px_rgba(255,95,82,0.3)] animate-in scale-in-95 duration-200">
        <div class="w-20 h-20 rounded-full bg-[#93000a] flex items-center justify-center mx-auto mb-6 shadow-xl border border-[#ff5f52]/30">
          <Siren class="w-10 h-10 text-[#ffb4ab] animate-pulse" />
        </div>
        <div v-if="!sosDispatched">
          <h2 class="text-2xl font-black text-white uppercase tracking-tight">Emergency SOS Initializing</h2>
          <p class="text-sm text-on-surface-variant font-light mt-3 leading-relaxed max-w-sm mx-auto">
            Broadcasting coordinates to <span class="text-[#ffb4ab] font-bold">University Security & Dispatchers</span> in...
          </p>
          <div class="text-7xl font-extrabold text-[#ffb4aa] tracking-tighter my-8">0{{ sosCountdown }}</div>
          <button @click="cancelSOS"
            class="w-full bg-[#353534] hover:bg-[#2d2d2d] border border-[#2d2d2d] py-3.5 rounded-xl text-sm font-bold text-white transition-all cursor-pointer">
            Cancel SOS Trigger
          </button>
        </div>
        <div v-else class="animate-in fade-in duration-300">
          <h2 class="text-2xl font-black text-[#ffb4aa] uppercase tracking-tight">SOS Dispatched!</h2>
          <div class="bg-[#93000a]/15 border border-[#ffb4ab]/25 p-4 rounded-xl my-6 text-left space-y-2">
            <div class="flex justify-between items-center text-xs font-bold text-[#ffb4ab] uppercase tracking-wide">
              <span>Tracker Status</span>
              <span class="flex items-center gap-1.5 animate-pulse">
                <span class="w-2 h-2 rounded-full bg-brand-primary"></span>Active Live Stream
              </span>
            </div>
            <h4 class="text-xs font-bold text-on-surface">Security Team En Route</h4>
            <p class="text-xs text-on-surface-variant leading-relaxed font-light">
              Estimated response time under <span class="text-[#ffb4ab] font-semibold">2 minutes</span>.
            </p>
          </div>
          <div class="flex gap-4">
            <button @click="alert('Opening VoIP connection to campus security dispatch.')"
              class="flex-grow bg-[#ff5f52] hover:bg-[#ff786d] text-white text-xs font-bold py-3 rounded-lg cursor-pointer border-none">
              Direct Office Call
            </button>
            <button @click="cancelSOS"
              class="flex-grow bg-[#353534] border border-[#2d2d2d] text-on-surface-variant hover:text-white text-xs font-semibold py-3 rounded-lg cursor-pointer">
              Deactivate Alert
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>
