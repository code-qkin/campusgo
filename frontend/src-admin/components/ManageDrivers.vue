<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { adminApi } from '../api'
import { CheckCircle, XCircle, Clock, Search, Car } from 'lucide-vue-next'

const drivers = ref<any[]>([])
const isLoading = ref(false)
const searchQuery = ref('')
const filterStatus = ref<'all' | 'pending' | 'approved' | 'rejected'>('all')
const rejectReason = ref('')
const rejectingId = ref<number | null>(null)
const actionMsg = ref('')
const actionError = ref('')

const load = async () => {
  isLoading.value = true
  try {
    const params = searchQuery.value ? `?search=${encodeURIComponent(searchQuery.value)}` : ''
    drivers.value = await adminApi.get(`/admin/drivers${params}`)
  } catch (e: any) {
    actionError.value = e.message
  } finally {
    isLoading.value = false
  }
}

onMounted(load)

const filtered = () => {
  if (filterStatus.value === 'all') return drivers.value
  return drivers.value.filter(d => d.driver_profile?.status === filterStatus.value)
}

const approve = async (id: number) => {
  try {
    await adminApi.patch(`/admin/drivers/${id}/approve`, {})
    actionMsg.value = 'Driver approved.'
    load()
  } catch (e: any) { actionError.value = e.message }
  setTimeout(() => { actionMsg.value = ''; actionError.value = '' }, 3000)
}

const reject = async (id: number) => {
  try {
    await adminApi.patch(`/admin/drivers/${id}/reject`, { reason: rejectReason.value })
    rejectingId.value = null
    rejectReason.value = ''
    actionMsg.value = 'Driver rejected.'
    load()
  } catch (e: any) { actionError.value = e.message }
  setTimeout(() => { actionMsg.value = ''; actionError.value = '' }, 3000)
}

const statusColor = (status: string) => ({
  pending:  'text-yellow-400 bg-yellow-400/10',
  approved: 'text-green-400 bg-green-400/10',
  rejected: 'text-red-400 bg-red-400/10',
}[status] || '')

const statusIcon = { pending: Clock, approved: CheckCircle, rejected: XCircle }
</script>

<template>
  <div class="h-full overflow-y-auto p-8">
    <div class="max-w-5xl space-y-6">

      <div v-if="actionMsg" class="p-3 bg-green-500/15 border border-green-500/30 rounded-lg text-xs text-green-400 font-medium">{{ actionMsg }}</div>
      <div v-if="actionError" class="p-3 bg-red-500/15 border border-red-500/30 rounded-lg text-xs text-red-400 font-medium">{{ actionError }}</div>

      <!-- Filters -->
      <div class="flex items-center gap-3 flex-wrap">
        <div class="relative flex-1 min-w-48">
          <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/50" />
          <input v-model="searchQuery" @input="load" type="text" placeholder="Search drivers..."
            class="w-full h-9 bg-[#1c1b1b] border border-[#2d2d2d] rounded-lg pl-9 pr-4 text-xs text-on-surface focus:outline-none focus:border-brand-primary-container" />
        </div>
        <div class="flex gap-1.5">
          <button v-for="s in ['all','pending','approved','rejected']" :key="s"
            @click="filterStatus = s as any"
            class="px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer border-none capitalize transition-all"
            :class="filterStatus === s ? 'bg-brand-primary-container text-white' : 'bg-[#1c1b1b] text-on-surface-variant hover:bg-[#252525]'">
            {{ s }}
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-[#1c1b1b] border border-[#2d2d2d] rounded-xl overflow-hidden">
        <div v-if="isLoading" class="p-12 text-center text-on-surface-variant text-xs">Loading...</div>

        <div v-else-if="filtered().length === 0" class="p-12 text-center">
          <Car class="w-8 h-8 text-on-surface-variant/30 mx-auto mb-3" />
          <p class="text-on-surface-variant text-sm font-medium">No drivers found</p>
        </div>

        <template v-else>
          <div class="grid grid-cols-[1fr_1fr_100px_100px_160px] gap-0 border-b border-[#2d2d2d] px-4 py-3">
            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Driver</span>
            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Vehicle</span>
            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Type</span>
            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Status</span>
            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Actions</span>
          </div>

          <div v-for="driver in filtered()" :key="driver.id"
            class="grid grid-cols-[1fr_1fr_100px_100px_160px] gap-0 px-4 py-4 border-b border-[#2d2d2d]/50 last:border-0 items-center">

            <div>
              <p class="text-sm font-semibold text-on-surface">{{ driver.full_name }}</p>
              <p class="text-xs text-on-surface-variant mt-0.5">{{ driver.email }}</p>
            </div>

            <div>
              <a v-if="driver.driver_profile?.vehicle_image_url"
                :href="driver.driver_profile.vehicle_image_url" target="_blank"
                class="text-xs text-brand-primary-container hover:underline">View photo</a>
              <span v-else class="text-xs text-on-surface-variant/50">No photo</span>
            </div>

            <div>
              <span class="text-xs font-bold text-on-surface capitalize">
                {{ driver.driver_profile?.vehicle_type || '—' }}
              </span>
            </div>

            <div>
              <span class="px-2 py-1 rounded-full text-[10px] font-bold capitalize"
                :class="statusColor(driver.driver_profile?.status)">
                {{ driver.driver_profile?.status || 'no profile' }}
              </span>
            </div>

            <div class="flex items-center gap-2">
              <template v-if="driver.driver_profile?.status !== 'approved'">
                <button @click="approve(driver.id)"
                  class="px-3 py-1.5 bg-green-500/15 text-green-400 hover:bg-green-500/25 text-xs font-bold rounded-lg cursor-pointer border-none transition-all">
                  Approve
                </button>
              </template>
              <template v-if="driver.driver_profile?.status !== 'rejected'">
                <button @click="rejectingId = driver.id"
                  class="px-3 py-1.5 bg-red-500/15 text-red-400 hover:bg-red-500/25 text-xs font-bold rounded-lg cursor-pointer border-none transition-all">
                  Reject
                </button>
              </template>
            </div>
          </div>
        </template>
      </div>

      <!-- Reject modal -->
      <div v-if="rejectingId !== null" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-[#1c1b1b] border border-[#2d2d2d] rounded-xl p-6 w-full max-w-sm space-y-4">
          <h3 class="text-sm font-bold text-on-surface">Reject Driver</h3>
          <p class="text-xs text-on-surface-variant">Optionally provide a reason (shown to driver):</p>
          <textarea v-model="rejectReason" rows="3" placeholder="e.g. Vehicle photo unclear..."
            class="w-full bg-[#131313] border border-[#2d2d2d] rounded-lg px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-red-500/50 resize-none" />
          <div class="flex gap-2">
            <button @click="reject(rejectingId!)"
              class="flex-1 py-2 bg-red-500/20 text-red-400 hover:bg-red-500/30 text-xs font-bold rounded-lg cursor-pointer border-none transition-all">
              Confirm Reject
            </button>
            <button @click="rejectingId = null; rejectReason = ''"
              class="flex-1 py-2 bg-[#252525] text-on-surface-variant text-xs font-bold rounded-lg cursor-pointer border-none hover:bg-[#2d2d2d] transition-all">
              Cancel
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
