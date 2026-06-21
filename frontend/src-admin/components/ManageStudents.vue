<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { adminApi } from '../api'
import { Search, UserCheck, UserX } from 'lucide-vue-next'

const students = ref<any[]>([])
const isLoading = ref(false)
const searchQuery = ref('')
const filterStatus = ref<'all' | 'active' | 'inactive'>('all')

const fetchStudents = async () => {
  isLoading.value = true
  try {
    const params = searchQuery.value ? `?search=${searchQuery.value}` : ''
    const data = await adminApi.get(`/admin/students${params}`)
    students.value = Array.isArray(data) ? data : []
  } catch (e) {
    students.value = []
  } finally {
    isLoading.value = false
  }
}

const toggleStudent = async (id: number) => {
  try {
    const data = await adminApi.patch(`/admin/students/${id}/toggle`)
    const idx = students.value.findIndex(s => s.id === id)
    if (idx !== -1) students.value[idx] = data
  } catch (e) {
    window.alert('Failed to update student')
  }
}

const filteredStudents = () => {
  if (filterStatus.value === 'all') return students.value
  return students.value.filter(s =>
    filterStatus.value === 'active' ? s.is_active : !s.is_active
  )
}

let searchTimeout: ReturnType<typeof setTimeout>
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchStudents, 400)
})

onMounted(() => fetchStudents())
</script>

<template>
  <div class="p-8 space-y-6 overflow-y-auto h-full">
    <div>
      <h2 class="text-2xl font-bold text-on-surface tracking-tight">Students</h2>
      <p class="text-sm text-on-surface-variant mt-1">{{ students.length }} registered students.</p>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 items-center">
      <div class="relative flex-1 min-w-[200px]">
        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" />
        <input v-model="searchQuery" type="text" placeholder="Search by name or email..."
          class="w-full h-10 bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg pl-9 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all" />
      </div>
      <div class="flex bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg p-1 gap-1">
        <button v-for="opt in (['all', 'active', 'inactive'] as const)" :key="opt"
          @click="filterStatus = opt"
          class="px-3 py-1.5 text-xs font-semibold rounded-md capitalize transition-all cursor-pointer border-none"
          :class="filterStatus === opt
            ? 'bg-brand-primary-container text-white'
            : 'text-on-surface-variant bg-transparent hover:text-on-surface'">
          {{ opt }}
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="w-6 h-6 rounded-full border-2 border-brand-primary/20 border-t-brand-primary animate-spin"></div>
    </div>

    <!-- Table -->
    <div v-else class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-[#2d2d2d] text-xs text-on-surface-variant uppercase tracking-wide">
            <th class="text-left px-6 py-3 font-semibold">Student</th>
            <th class="text-left px-6 py-3 font-semibold">Campus</th>
            <th class="text-left px-6 py-3 font-semibold">Points</th>
            <th class="text-left px-6 py-3 font-semibold">Status</th>
            <th class="text-right px-6 py-3 font-semibold">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredStudents().length === 0">
            <td colspan="5" class="px-6 py-8 text-center text-xs text-on-surface-variant">
              No students found.
            </td>
          </tr>
          <tr v-for="student in filteredStudents()" :key="student.id"
            class="border-b border-[#2d2d2d] last:border-0 hover:bg-[#222] transition-colors">
            <td class="px-6 py-4">
              <div>
                <p class="font-semibold text-on-surface text-xs">{{ student.full_name }}</p>
                <p class="text-[10px] text-on-surface-variant mt-0.5">{{ student.email }}</p>
              </div>
            </td>
            <td class="px-6 py-4 text-xs text-on-surface-variant">
              {{ student.campus?.name || '—' }}
            </td>
            <td class="px-6 py-4 text-xs text-on-surface font-bold">
              {{ student.points || 0 }}
            </td>
            <td class="px-6 py-4">
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                :class="student.is_active !== false
                  ? 'bg-brand-tertiary/15 text-brand-tertiary'
                  : 'bg-[#2a2a2a] text-on-surface-variant'">
                {{ student.is_active !== false ? 'Active' : 'Suspended' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <button @click="toggleStudent(student.id)"
                class="text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-pointer border-none transition-all"
                :class="student.is_active !== false
                  ? 'bg-[#2a2a2a] text-[#ff5f52] hover:brightness-110'
                  : 'bg-brand-tertiary/15 text-brand-tertiary hover:brightness-110'">
                <span class="flex items-center gap-1.5">
                  <UserX v-if="student.is_active !== false" class="w-3 h-3" />
                  <UserCheck v-else class="w-3 h-3" />
                  {{ student.is_active !== false ? 'Suspend' : 'Activate' }}
                </span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>