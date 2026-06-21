<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { AdminUser } from '../types'
import { adminApi } from '../api'
import { Users, Car, MapPin, Search, Activity, CheckCircle } from 'lucide-vue-next'

const props = defineProps<{ admin: AdminUser }>()

const stats = ref({
  total_students:  0,
  active_rides:    0,
  completed_rides: 0,
  lost_found_open: 0,
  carpools_today:  0,
})
const isLoading = ref(false)

const fetchStats = async () => {
  isLoading.value = true
  try {
    const data = await adminApi.get('/admin/stats')
    stats.value = data
  } catch (e) {
    // silently fail
  } finally {
    isLoading.value = false
  }
}

onMounted(() => fetchStats())
</script>

<template>
  <div class="p-8 space-y-8 overflow-y-auto h-full">
    <div>
      <h2 class="text-2xl font-bold text-on-surface tracking-tight">
        {{ admin.role === 'super_admin' ? 'Platform Overview' : `${admin.campusName} Overview` }}
      </h2>
      <p class="text-sm text-on-surface-variant mt-1">
        {{ new Date().toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
      </p>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="w-6 h-6 rounded-full border-2 border-brand-primary/20 border-t-brand-primary animate-spin"></div>
    </div>

    <template v-else>
      <!-- Stat cards -->
      <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-brand-secondary/15 flex items-center justify-center">
              <Users class="w-4 h-4 text-brand-secondary" />
            </div>
          </div>
          <div class="text-2xl font-extrabold text-on-surface">{{ stats.total_students.toLocaleString() }}</div>
          <div class="text-xs text-on-surface-variant mt-1">Total Students</div>
        </div>

        <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-brand-primary/15 flex items-center justify-center">
              <Car class="w-4 h-4 text-brand-primary" />
            </div>
          </div>
          <div class="text-2xl font-extrabold text-on-surface">{{ stats.active_rides }}</div>
          <div class="text-xs text-on-surface-variant mt-1">Active Rides</div>
        </div>

        <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-brand-tertiary/15 flex items-center justify-center">
              <CheckCircle class="w-4 h-4 text-brand-tertiary" />
            </div>
          </div>
          <div class="text-2xl font-extrabold text-on-surface">{{ stats.completed_rides }}</div>
          <div class="text-xs text-on-surface-variant mt-1">Completed Rides</div>
        </div>

        <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-[#ffb4aa]/15 flex items-center justify-center">
              <Search class="w-4 h-4 text-[#ffb4aa]" />
            </div>
          </div>
          <div class="text-2xl font-extrabold text-on-surface">{{ stats.lost_found_open }}</div>
          <div class="text-xs text-on-surface-variant mt-1">Open Lost & Found</div>
        </div>

        <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-brand-primary/15 flex items-center justify-center">
              <Activity class="w-4 h-4 text-brand-primary" />
            </div>
          </div>
          <div class="text-2xl font-extrabold text-on-surface">{{ stats.carpools_today }}</div>
          <div class="text-xs text-on-surface-variant mt-1">Carpools Today</div>
        </div>
      </div>
    </template>
  </div>
</template>