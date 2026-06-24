<script setup lang="ts">
import { ref } from 'vue'
import { Lock, CheckCircle } from 'lucide-vue-next'

const API = import.meta.env.VITE_API_BASE || 'http://127.0.0.1:8000/api'
const getToken = () => localStorage.getItem('driver_token') || ''

const currentPassword  = ref('')
const newPassword      = ref('')
const confirmPassword  = ref('')
const saving           = ref(false)
const successMsg       = ref('')
const errorMsg         = ref('')

const handleSubmit = async () => {
  successMsg.value = ''
  errorMsg.value   = ''
  if (!currentPassword.value || !newPassword.value || !confirmPassword.value) {
    errorMsg.value = 'Please fill in all fields.'; return
  }
  if (newPassword.value !== confirmPassword.value) {
    errorMsg.value = 'New passwords do not match.'; return
  }
  saving.value = true
  try {
    const res  = await fetch(`${API}/user/password`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${getToken()}`,
      },
      body: JSON.stringify({
        current_password:      currentPassword.value,
        password:              newPassword.value,
        password_confirmation: confirmPassword.value,
      }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Failed to change password.')
    successMsg.value      = data.message || 'Password changed successfully.'
    currentPassword.value = ''
    newPassword.value     = ''
    confirmPassword.value = ''
  } catch (e: any) {
    errorMsg.value = e.message || 'Failed to change password.'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="flex-1 overflow-y-auto p-8">
    <div class="max-w-md mx-auto space-y-6">
      <div class="bg-[#1e1e1e] border border-[#2a2a2a] rounded-xl p-6 space-y-5">
        <h2 class="text-sm font-bold text-on-surface flex items-center gap-2">
          <Lock class="w-4 h-4 text-brand-tertiary" /> Change Password
        </h2>

        <div v-if="errorMsg" class="p-3 bg-red-500/15 border border-red-500/30 rounded-lg text-xs text-red-400 font-medium">{{ errorMsg }}</div>
        <div v-if="successMsg" class="p-3 bg-green-500/15 border border-green-500/30 rounded-lg text-xs text-green-400 font-medium flex items-center gap-2">
          <CheckCircle class="w-4 h-4" /> {{ successMsg }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Current Password</label>
            <input type="password" v-model="currentPassword"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded-lg px-3.5 py-2.5 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary"
              placeholder="Your current password" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">New Password</label>
            <input type="password" v-model="newPassword"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded-lg px-3.5 py-2.5 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary"
              placeholder="Min 8 characters" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Confirm New Password</label>
            <input type="password" v-model="confirmPassword"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded-lg px-3.5 py-2.5 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary"
              placeholder="••••••••" />
          </div>
          <button type="submit" :disabled="saving"
            class="w-full py-2.5 bg-brand-tertiary text-black font-bold text-sm rounded-lg hover:brightness-110 cursor-pointer border-none disabled:opacity-60 transition-all">
            {{ saving ? 'Changing...' : 'Change Password' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
