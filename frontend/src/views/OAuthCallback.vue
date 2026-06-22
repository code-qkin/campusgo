<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import FutaLogo from '../components/FutaLogo.vue';

const router = useRouter();

const API_BASE = import.meta.env.VITE_API_BASE || 'http://127.0.0.1:8000/api';

const showCampusForm  = ref(false);
const pendingName     = ref('');
const pendingEmail    = ref('');
const campuses        = ref<any[]>([]);
const campusId        = ref('');
const completing      = ref(false);
const errorMsg        = ref('');

onMounted(async () => {
  const params = new URLSearchParams(window.location.search);
  window.history.replaceState({}, '', window.location.pathname);

  if (params.get('oauth_token')) {
    const token    = params.get('oauth_token')!;
    const userData = JSON.parse(decodeURIComponent(params.get('oauth_user') || '{}'));
    localStorage.setItem('campusgo_token', token);
    localStorage.setItem('campusgo_user',  JSON.stringify(userData));
    router.replace('/home');
    return;
  }

  if (params.get('oauth_pending') === '1') {
    pendingName.value  = decodeURIComponent(params.get('oauth_name')  || '');
    pendingEmail.value = decodeURIComponent(params.get('oauth_email') || '');
    showCampusForm.value = true;
    try {
      const res  = await fetch(`${API_BASE}/campuses`);
      campuses.value = await res.json();
    } catch { campuses.value = []; }
    return;
  }

  if (params.get('oauth_error')) {
    const err = params.get('oauth_error');
    router.replace(err === 'suspended' ? '/login?error=suspended' : '/login?error=oauth_failed');
    return;
  }

  router.replace('/');
});

const completeSignup = async () => {
  errorMsg.value = '';
  if (!campusId.value) { errorMsg.value = 'Please select your university.'; return; }
  completing.value = true;
  try {
    const res  = await fetch(`${API_BASE}/auth/google/complete`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body:    JSON.stringify({ full_name: pendingName.value, email: pendingEmail.value, campus_id: campusId.value }),
    });
    const data = await res.json();
    if (!res.ok) { errorMsg.value = data.message || 'Sign-up failed.'; completing.value = false; return; }
    localStorage.setItem('campusgo_token', data.token);
    localStorage.setItem('campusgo_user',  JSON.stringify(data.user));
    router.replace('/home');
  } catch {
    errorMsg.value = 'Could not reach server.';
    completing.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-[#131313] flex items-center justify-center p-4">

    <div v-if="!showCampusForm" class="flex flex-col items-center gap-4 text-on-surface-variant">
      <div class="w-10 h-10 rounded-full border-2 border-[#2d2d2d] border-t-brand-primary animate-spin"></div>
      <p class="text-sm font-medium">Signing you in...</p>
    </div>

    <div v-else class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-8 max-w-[420px] w-full shadow-2xl">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shrink-0">
          <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84z" fill="#EA4335"/>
          </svg>
        </div>
        <div>
          <h2 class="text-base font-bold text-on-surface">One last step</h2>
          <p class="text-xs text-on-surface-variant">Select your university to complete sign-up</p>
        </div>
      </div>

      <div class="bg-[#131313] border border-[#2d2d2d] rounded-lg p-4 mb-5 space-y-2">
        <div class="flex justify-between text-xs">
          <span class="text-on-surface-variant">Name</span>
          <span class="text-on-surface font-semibold">{{ pendingName }}</span>
        </div>
        <div class="flex justify-between text-xs">
          <span class="text-on-surface-variant">Email</span>
          <span class="text-on-surface font-semibold">{{ pendingEmail }}</span>
        </div>
        <div class="flex items-center gap-1.5 text-xs text-brand-tertiary font-semibold pt-1">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          Verified by Google
        </div>
      </div>

      <div v-if="errorMsg" class="mb-4 p-3 bg-brand-error/15 border border-brand-error/30 rounded-lg text-xs text-brand-error font-medium">
        {{ errorMsg }}
      </div>

      <div class="space-y-1.5 mb-5">
        <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">Select University</label>
        <select v-model="campusId"
          class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg px-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all">
          <option value="" disabled>-- Select your university --</option>
          <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>

      <button @click="completeSignup" :disabled="completing"
        class="w-full h-11 bg-brand-primary-container text-white font-bold text-sm rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-2 cursor-pointer border-none disabled:opacity-60">
        <div v-if="completing" class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></div>
        <span>{{ completing ? 'Creating account...' : 'Complete Sign-Up' }}</span>
      </button>
    </div>

  </div>
</template>
