<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Mail, Lock, Car } from 'lucide-vue-next'

const router = useRouter()
const API = import.meta.env.VITE_API_BASE || 'http://127.0.0.1:8000/api'

const email        = ref('')
const password     = ref('')
const isSubmitting = ref(false)
const errorMsg     = ref('')

const handleSubmit = async () => {
  if (!email.value || !password.value) {
    errorMsg.value = 'Please enter your email and password.'
    return
  }
  isSubmitting.value = true
  errorMsg.value = ''
  try {
    const res  = await fetch(`${API}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Login failed')
    if (data.user.role !== 'driver') throw new Error('This account is not a driver account')

    localStorage.setItem('driver_token', data.token)
    localStorage.setItem('driver_user', JSON.stringify(data.user))
    localStorage.setItem('driver_status', data.driver_status || 'pending')

    router.push(data.driver_status === 'approved' ? '/available' : '/pending')
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : 'Could not reach server'
  } finally {
    isSubmitting.value = false
    if (errorMsg.value) setTimeout(() => { errorMsg.value = '' }, 5000)
  }
}
</script>

<template>
  <div class="min-h-screen w-full bg-[#131313] flex items-center justify-center p-8 font-sans">
    <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(at_30%_20%,rgba(78,222,163,0.05)_0px,transparent_60%)]"></div>

    <main class="w-full max-w-[420px] z-10">
      <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-xl bg-brand-tertiary/20 border border-brand-tertiary/30 flex items-center justify-center mx-auto mb-4">
          <Car class="w-6 h-6 text-brand-tertiary" />
        </div>
        <h1 class="text-2xl font-bold text-on-surface tracking-tight">Driver Login</h1>
        <p class="text-sm text-on-surface-variant mt-1">CampusGo Driver Portal</p>
      </div>

      <div class="bg-[#1e1e1e] border border-[#2a2a2a] rounded-xl p-8 shadow-2xl">
        <div v-if="errorMsg" class="mb-4 p-3 bg-red-500/15 border border-red-500/30 rounded-lg text-xs text-red-400 font-medium">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Email</label>
            <div class="relative">
              <Mail class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="email" v-model="email"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-10 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary transition-all"
                placeholder="driver@email.com" required />
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Password</label>
            <div class="relative">
              <Lock class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="password" v-model="password"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-10 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary transition-all"
                placeholder="••••••••" required />
            </div>
          </div>

          <button type="submit" :disabled="isSubmitting"
            class="w-full h-11 bg-brand-tertiary text-black font-bold text-sm rounded-lg hover:brightness-110 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 mt-2 border-none">
            <div v-if="isSubmitting" class="w-4 h-4 rounded-full border-2 border-black/20 border-t-black animate-spin"></div>
            {{ isSubmitting ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>
      </div>

      <p class="text-center text-xs text-on-surface-variant mt-5">
        New driver?
        <button @click="router.push('/signup')"
          class="text-brand-tertiary font-semibold cursor-pointer border-none bg-transparent hover:underline">
          Register here
        </button>
      </p>
    </main>
  </div>
</template>
