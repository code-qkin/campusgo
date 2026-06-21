<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { adminApi } from '../api'
import { Building2, Plus, X, ToggleLeft, ToggleRight } from 'lucide-vue-next'

const campuses = ref<any[]>([])
const isLoading = ref(false)
const showForm = ref(false)
const isSaving = ref(false)
const formName = ref('')
const formDomain = ref('')
const formSlug = ref('')

const totalStudents = computed(() =>
  campuses.value.reduce((sum, c) => sum + (c.students_count || 0), 0)
)

const fetchCampuses = async () => {
  isLoading.value = true
  try {
    const data = await adminApi.get('/campuses')
    campuses.value = Array.isArray(data) ? data : []
  } catch (e) {
    campuses.value = []
  } finally {
    isLoading.value = false
  }
}

const handleAdd = async () => {
  if (!formName.value.trim() || !formDomain.value.trim() || !formSlug.value.trim()) {
    window.alert('Please fill in all fields.')
    return
  }
  isSaving.value = true
  try {
    const data = await adminApi.post('/campuses', {
      name:         formName.value,
      email_domain: formDomain.value,
      slug:         formSlug.value,
      is_active:    true,
    })
    campuses.value.push(data)
    showForm.value = false
    formName.value = ''
    formDomain.value = ''
    formSlug.value = ''
  } catch (e: any) {
    window.alert(e.message || 'Failed to add campus')
  } finally {
    isSaving.value = false
  }
}

onMounted(() => fetchCampuses())
</script>

<template>
  <div class="p-8 space-y-6 overflow-y-auto h-full">

    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-on-surface tracking-tight">Campuses</h2>
        <p class="text-sm text-on-surface-variant mt-1">
          {{ campuses.length }} campuses on the platform.
        </p>
      </div>
      <button @click="showForm = !showForm"
        class="flex items-center gap-2 bg-brand-primary-container text-white text-xs font-bold px-4 py-2.5 rounded-lg hover:brightness-110 cursor-pointer border-none">
        <Plus class="w-3.5 h-3.5" />
        Add Campus
      </button>
    </div>

    <!-- Add form -->
    <div v-if="showForm" class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-on-surface">New Campus</h3>
        <button @click="showForm = false"
          class="w-7 h-7 rounded-full hover:bg-[#2a2a2a] flex items-center justify-center text-on-surface-variant cursor-pointer border-none bg-transparent">
          <X class="w-4 h-4" />
        </button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Campus Name</label>
          <input v-model="formName" type="text" placeholder="e.g. University of Abuja"
            class="w-full h-10 bg-[#131313] border border-[#2d2d2d] rounded-lg px-3 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all" />
        </div>
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Email Domain</label>
          <input v-model="formDomain" type="text" placeholder="e.g. student.uniabuja.edu.ng"
            class="w-full h-10 bg-[#131313] border border-[#2d2d2d] rounded-lg px-3 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all" />
        </div>
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Slug</label>
          <input v-model="formSlug" type="text" placeholder="e.g. uniabuja"
            class="w-full h-10 bg-[#131313] border border-[#2d2d2d] rounded-lg px-3 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all" />
        </div>
      </div>
      <button @click="handleAdd" :disabled="isSaving"
        class="px-6 py-2.5 bg-brand-primary-container text-white text-xs font-bold rounded-lg cursor-pointer border-none hover:brightness-110 disabled:opacity-60">
        {{ isSaving ? 'Saving...' : 'Save Campus' }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="w-6 h-6 rounded-full border-2 border-brand-primary/20 border-t-brand-primary animate-spin"></div>
    </div>

    <!-- Campuses grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="campus in campuses" :key="campus.id"
        class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-brand-primary/10 flex items-center justify-center shrink-0">
              <Building2 class="w-4 h-4 text-brand-primary" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-on-surface">{{ campus.name }}</h3>
              <p class="text-[10px] text-on-surface-variant mt-0.5">{{ campus.email_domain }}</p>
            </div>
          </div>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
            :class="campus.is_active
              ? 'bg-brand-tertiary/15 text-brand-tertiary'
              : 'bg-[#2a2a2a] text-on-surface-variant'">
            {{ campus.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
        <div class="flex items-center justify-between text-xs text-on-surface-variant pt-3 border-t border-[#2d2d2d]">
          <span class="font-mono bg-[#131313] px-2 py-0.5 rounded text-[10px]">{{ campus.slug }}</span>
        </div>
      </div>
    </div>

  </div>
</template>