<script setup lang="ts">
import { ref } from 'vue'
import { Driver } from '../types'

const emit = defineEmits<{
    'login-success': [user: Driver]
}>()

const email = ref('driver@unilag.edu.ng')
const password = ref('password123')
const isSubmitting = ref(false)
const errorMsg = ref('')

const handleSubmit = async () => {
    if (!email.value || !password.value) {
        errorMsg.value = 'Please enter your email and password.'
        return
    }

    isSubmitting.value = true;

    try {
        const response = await fetch('http://127.0.0.1:8000/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email.value, password: password.value })
        })
        const data = await response.json()
        if (!response.ok) throw new Error(data.message)
        if (data.user.role !== 'driver') throw new Error('This account is not a driver account')
        localStorage.setItem('driver_token', data.token)
        localStorage.setItem('driver_user', JSON.stringify(data.user))
        emit('login-success', {
            id: data.user.id,
            fullName: data.user.full_name,
            email: data.user.email,
            role: data.user.role,
            campusId: data.user.campus_id,
            campusName: null
        })
    } catch (error) {
        errorMsg.value = error instanceof Error ? error.message : 'Could not reach server'
    } finally {
        isSubmitting.value = false;
        if (errorMsg.value) {
            setTimeout(() => {
                errorMsg.value = ''
            }, 5000)
        }
    }
}

</script>
<template>
  <div class="min-h-screen w-full bg-[#131313] flex items-center justify-center p-8 font-sans">
    <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(at_30%_20%,rgba(78,222,163,0.05)_0px,transparent_60%)]"></div>

    <main class="w-full max-w-[420px] z-10">
      <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-xl bg-brand-tertiary/20 border border-brand-tertiary/30 flex items-center justify-center mx-auto mb-4">
          <span class="text-2xl">🚗</span>
        </div>
        <h1 class="text-2xl font-bold text-on-surface tracking-tight">CampusGo Driver</h1>
        <p class="text-sm text-on-surface-variant mt-1">Driver Portal</p>
      </div>

      <div class="bg-[#1e1e1e] border border-[#2a2a2a] rounded-xl p-8 shadow-2xl">
        <h2 class="text-lg font-bold text-on-surface mb-6">Sign in</h2>

        <div v-if="errorMsg" class="mb-4 p-3 bg-brand-error-container/20 border border-brand-error-container/40 rounded-lg text-xs text-[#ffb4ab] font-medium">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Email</label>
            <input type="email" v-model="email"
              class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg px-4 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary transition-all"
              placeholder="driver@university.edu" required />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Password</label>
            <input type="password" v-model="password"
              class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg px-4 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary transition-all"
              placeholder="••••••••" required />
          </div>

          <button type="submit" :disabled="isSubmitting"
            class="w-full h-11 bg-brand-tertiary text-[#131313] font-bold text-sm rounded-lg hover:brightness-110 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed mt-2">
            <template v-if="isSubmitting">
              <div class="w-4 h-4 rounded-full border-2 border-[#131313]/20 border-t-[#131313] animate-spin"></div>
              <span>Signing in...</span>
            </template>
            <template v-else>
              <span>Sign In as Driver</span>
            </template>
          </button>
        </form>
      </div>

      <p class="text-center text-xs text-on-surface-variant/50 mt-6">
        CampusGo Driver Portal · Restricted Access
      </p>
    </main>
  </div>
</template>