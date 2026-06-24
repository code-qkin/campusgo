<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { Clock, XCircle, LogOut, Car } from 'lucide-vue-next'

const router = useRouter()

const status = computed(() => localStorage.getItem('driver_status') || 'pending')
const driver = computed(() => {
  try { return JSON.parse(localStorage.getItem('driver_user') || '{}') } catch { return {} }
})

const handleLogout = () => {
  localStorage.removeItem('driver_token')
  localStorage.removeItem('driver_user')
  localStorage.removeItem('driver_status')
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen w-full bg-[#131313] flex flex-col items-center justify-center p-8 font-sans text-center">
    <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(at_50%_30%,rgba(78,222,163,0.04)_0px,transparent_60%)]"></div>

    <div class="z-10 max-w-md w-full space-y-6">
      <!-- Icon -->
      <div class="w-20 h-20 rounded-2xl mx-auto flex items-center justify-center"
        :class="status === 'rejected' ? 'bg-red-500/15 border border-red-500/20' : 'bg-yellow-500/15 border border-yellow-500/20'">
        <XCircle v-if="status === 'rejected'" class="w-10 h-10 text-red-400" />
        <Clock v-else class="w-10 h-10 text-yellow-400" />
      </div>

      <!-- Message -->
      <div>
        <h1 class="text-2xl font-bold text-on-surface mb-2">
          {{ status === 'rejected' ? 'Application Rejected' : 'Awaiting Verification' }}
        </h1>
        <p class="text-sm text-on-surface-variant leading-relaxed">
          <template v-if="status === 'rejected'">
            Your driver application was not approved. Please contact support or re-apply with correct details.
          </template>
          <template v-else>
            Hi <strong class="text-on-surface">{{ driver.full_name || 'Driver' }}</strong>, your application is being reviewed
            by the campus admin. You will be able to start accepting rides once approved.
          </template>
        </p>
      </div>

      <!-- Status card -->
      <div class="bg-[#1e1e1e] border border-[#2a2a2a] rounded-xl p-6 space-y-3 text-left">
        <div class="flex items-center gap-3">
          <Car class="w-5 h-5 text-on-surface-variant/60 shrink-0" />
          <div>
            <p class="text-[10px] text-on-surface-variant uppercase tracking-wide font-semibold">Status</p>
            <p class="text-sm font-bold capitalize"
              :class="status === 'rejected' ? 'text-red-400' : 'text-yellow-400'">
              {{ status }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-5 h-5 shrink-0 flex items-center justify-center">
            <span class="text-sm">📧</span>
          </div>
          <div>
            <p class="text-[10px] text-on-surface-variant uppercase tracking-wide font-semibold">Account</p>
            <p class="text-sm text-on-surface">{{ driver.email || '—' }}</p>
          </div>
        </div>
      </div>

      <p v-if="status !== 'rejected'" class="text-xs text-on-surface-variant/60">
        Approval usually takes 24–48 hours. Check back later.
      </p>

      <button @click="handleLogout"
        class="flex items-center gap-2 mx-auto text-xs font-semibold text-on-surface-variant hover:text-red-400 cursor-pointer border-none bg-transparent transition-colors">
        <LogOut class="w-4 h-4" /> Sign Out
      </button>
    </div>
  </div>
</template>
