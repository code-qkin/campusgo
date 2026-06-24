<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { Mail, Lock, Shield, ArrowRight } from 'lucide-vue-next';

const router = useRouter();
const API = import.meta.env.VITE_API_BASE || 'http://127.0.0.1:8000/api';

const email = ref('');
const password = ref('');
const isSubmitting = ref(false);
const errorMsg = ref('');

const handleSubmit = async () => {
  if (!email.value || !password.value) {
    errorMsg.value = 'Please enter your email and password.';
    return;
  }

  isSubmitting.value = true;
  errorMsg.value = '';

  try {
    const res = await fetch(`${API}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value }),
    })

    const data = await res.json()

    if (!res.ok) { errorMsg.value = data.message || 'Invalid credentials.'; return }

    if (!['campus_admin', 'super_admin'].includes(data.user.role)) {
      errorMsg.value = 'This account does not have admin access.'; return
    }

    localStorage.setItem('admin_token', data.token)
    localStorage.setItem('admin_user', JSON.stringify(data.user))
    router.push('/dashboard')
  } catch (e) {
    errorMsg.value = 'Could not reach server.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen w-full bg-[#131313] flex items-center justify-center p-8 font-sans">
    <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(at_30%_20%,rgba(255,95,82,0.05)_0px,transparent_60%)]"></div>

    <main class="w-full max-w-[420px] z-10 animate-in fade-in slide-in-from-bottom-4 duration-300">
      <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-xl bg-brand-primary-container flex items-center justify-center mx-auto mb-4 shadow-lg">
          <Shield class="w-6 h-6 text-white" />
        </div>
        <h1 class="text-2xl font-bold text-on-surface tracking-tight">CampusGo Admin</h1>
        <p class="text-sm text-on-surface-variant mt-1">Management & Operations Portal</p>
      </div>

      <div class="bg-[#1e1e1e] border border-[#2a2a2a] rounded-xl p-8 shadow-2xl">
        <h2 class="text-lg font-bold text-on-surface mb-6">Sign in to your account</h2>

        <div v-if="errorMsg"
          class="mb-4 p-3 bg-brand-error-container/20 border border-brand-error-container/40 rounded-lg text-xs text-[#ffb4ab] font-medium">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Email</label>
            <div class="relative">
              <Mail class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="email" v-model="email"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-10 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all"
                placeholder="you@university.edu" required />
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Password</label>
            <div class="relative">
              <Lock class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="password" v-model="password"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-10 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all"
                placeholder="••••••••" required />
            </div>
          </div>

          <button type="submit" :disabled="isSubmitting"
            class="w-full h-11 bg-brand-primary-container text-white font-bold text-sm rounded-lg hover:brightness-110 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed mt-2">
            <template v-if="isSubmitting">
              <div class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></div>
              <span>Signing in...</span>
            </template>
            <template v-else>
              <span>Sign In</span>
              <ArrowRight class="w-4 h-4" />
            </template>
          </button>
        </form>
      </div>

      <p class="text-center text-xs text-on-surface-variant/50 mt-6">
        CampusGo Admin Portal · Restricted Access
      </p>
    </main>
  </div>
</template>