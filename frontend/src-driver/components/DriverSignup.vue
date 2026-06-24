<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { User, Mail, Lock, Car, Upload, ImageIcon } from 'lucide-vue-next'

const router = useRouter()
const API = import.meta.env.VITE_API_BASE || 'http://127.0.0.1:8000/api'

// Form fields
const fullName     = ref('')
const email        = ref('')
const password     = ref('')
const confirmPass  = ref('')
const campusId     = ref('')
const vehicleType  = ref<'keke' | 'car' | ''>('')
const vehicleImage = ref<File | null>(null)
const imagePreview = ref<string | null>(null)

const campuses     = ref<any[]>([])
const isSubmitting = ref(false)
const errorMsg     = ref('')

onMounted(async () => {
  try {
    const res  = await fetch(`${API}/campuses`)
    campuses.value = await res.json()
    if (campuses.value.length === 1) campusId.value = String(campuses.value[0].id)
  } catch {}
})

const onImagePick = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  vehicleImage.value = file
  const reader = new FileReader()
  reader.onload = (ev) => { imagePreview.value = ev.target?.result as string }
  reader.readAsDataURL(file)
}

const handleSubmit = async () => {
  errorMsg.value = ''
  if (!fullName.value || !email.value || !password.value || !confirmPass.value || !campusId.value || !vehicleType.value) {
    errorMsg.value = 'Please fill in all fields.'; return
  }
  if (password.value !== confirmPass.value) {
    errorMsg.value = 'Passwords do not match.'; return
  }
  if (!vehicleImage.value) {
    errorMsg.value = 'Please upload a photo of your vehicle.'; return
  }

  isSubmitting.value = true
  try {
    const form = new FormData()
    form.append('full_name', fullName.value)
    form.append('email', email.value)
    form.append('password', password.value)
    form.append('password_confirmation', confirmPass.value)
    form.append('campus_id', campusId.value)
    form.append('vehicle_type', vehicleType.value)
    form.append('vehicle_image', vehicleImage.value)

    const res  = await fetch(`${API}/driver/register`, {
      method: 'POST',
      headers: { Accept: 'application/json' },
      body: form,
    })
    const data = await res.json()
    if (!res.ok) {
      // Laravel validation errors come as { errors: { field: [msg] } }
      if (data.errors) {
        const first = Object.values(data.errors as Record<string, string[]>)[0]
        errorMsg.value = Array.isArray(first) ? first[0] : String(first)
      } else {
        errorMsg.value = data.message || 'Registration failed.'
      }
      return
    }

    localStorage.setItem('driver_token', data.token)
    localStorage.setItem('driver_user', JSON.stringify(data.user))
    localStorage.setItem('driver_status', 'pending')

    router.push('/pending')
  } catch (e) {
    errorMsg.value = 'Could not reach server.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen w-full bg-[#131313] flex items-center justify-center p-6 font-sans">
    <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(at_30%_20%,rgba(78,222,163,0.05)_0px,transparent_60%)]"></div>

    <main class="w-full max-w-[480px] z-10 py-8">
      <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-xl bg-brand-tertiary/20 border border-brand-tertiary/30 flex items-center justify-center mx-auto mb-4">
          <Car class="w-6 h-6 text-brand-tertiary" />
        </div>
        <h1 class="text-2xl font-bold text-on-surface tracking-tight">Become a Driver</h1>
        <p class="text-sm text-on-surface-variant mt-1">Register your vehicle to start driving on CampusGo</p>
      </div>

      <div class="bg-[#1e1e1e] border border-[#2a2a2a] rounded-xl p-8 shadow-2xl space-y-5">
        <div v-if="errorMsg" class="p-3 bg-red-500/15 border border-red-500/30 rounded-lg text-xs text-red-400 font-medium">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">

          <!-- Personal info -->
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Full Name</label>
            <div class="relative">
              <User class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="text" v-model="fullName"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-10 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary transition-all"
                placeholder="Your full name" required />
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Email</label>
            <div class="relative">
              <Mail class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="email" v-model="email"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-10 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary transition-all"
                placeholder="you@email.com" required />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Password</label>
              <div class="relative">
                <Lock class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
                <input type="password" v-model="password"
                  class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-10 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary transition-all"
                  placeholder="Min 8 chars" required />
              </div>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Confirm</label>
              <input type="password" v-model="confirmPass"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg px-4 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary transition-all"
                placeholder="••••••••" required />
            </div>
          </div>

          <!-- Campus (hidden if only one) -->
          <div v-if="campuses.length > 1" class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Campus</label>
            <select v-model="campusId"
              class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg px-4 text-sm text-on-surface focus:outline-none focus:border-brand-tertiary transition-all appearance-none cursor-pointer">
              <option value="" disabled>Select your campus</option>
              <option v-for="c in campuses" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
            </select>
          </div>

          <!-- Vehicle type -->
          <div class="space-y-2">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">Vehicle Type</label>
            <div class="grid grid-cols-2 gap-3">
              <button type="button" @click="vehicleType = 'keke'"
                class="h-20 rounded-xl border-2 flex flex-col items-center justify-center gap-2 cursor-pointer transition-all font-bold text-sm"
                :class="vehicleType === 'keke'
                  ? 'border-brand-tertiary bg-brand-tertiary/10 text-brand-tertiary'
                  : 'border-[#2d2d2d] bg-[#131313] text-on-surface-variant hover:border-[#444]'">
                <span class="text-2xl">🛺</span>
                Keke
              </button>
              <button type="button" @click="vehicleType = 'car'"
                class="h-20 rounded-xl border-2 flex flex-col items-center justify-center gap-2 cursor-pointer transition-all font-bold text-sm"
                :class="vehicleType === 'car'
                  ? 'border-brand-tertiary bg-brand-tertiary/10 text-brand-tertiary'
                  : 'border-[#2d2d2d] bg-[#131313] text-on-surface-variant hover:border-[#444]'">
                <span class="text-2xl">🚗</span>
                Car
              </button>
            </div>
          </div>

          <!-- Vehicle photo -->
          <div class="space-y-2">
            <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide block">
              Vehicle Photo <span class="text-brand-error normal-case font-normal">(required)</span>
            </label>
            <label class="block cursor-pointer">
              <input type="file" accept="image/*" @change="onImagePick" class="sr-only" />
              <div v-if="imagePreview"
                class="w-full h-48 rounded-xl overflow-hidden border-2 border-brand-tertiary relative">
                <img :src="imagePreview" class="w-full h-full object-cover" alt="Vehicle preview" />
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                  <span class="text-white text-xs font-bold flex items-center gap-1.5"><Upload class="w-4 h-4" /> Change photo</span>
                </div>
              </div>
              <div v-else
                class="w-full h-48 rounded-xl border-2 border-dashed border-[#2d2d2d] hover:border-brand-tertiary/50 bg-[#131313] flex flex-col items-center justify-center gap-3 transition-all">
                <ImageIcon class="w-8 h-8 text-on-surface-variant/30" />
                <div class="text-center">
                  <p class="text-sm font-semibold text-on-surface-variant">Click to upload vehicle photo</p>
                  <p class="text-xs text-on-surface-variant/60 mt-0.5">JPG, PNG or WebP — max 5MB</p>
                </div>
              </div>
            </label>
          </div>

          <button type="submit" :disabled="isSubmitting"
            class="w-full h-11 bg-brand-tertiary text-black font-bold text-sm rounded-lg hover:brightness-110 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 border-none mt-2">
            <div v-if="isSubmitting" class="w-4 h-4 rounded-full border-2 border-black/20 border-t-black animate-spin"></div>
            {{ isSubmitting ? 'Submitting...' : 'Submit for Verification' }}
          </button>
        </form>
      </div>

      <p class="text-center text-xs text-on-surface-variant mt-5">
        Already registered?
        <button @click="router.push('/login')"
          class="text-brand-tertiary font-semibold cursor-pointer border-none bg-transparent hover:underline">
          Sign in
        </button>
      </p>
    </main>
  </div>
</template>
