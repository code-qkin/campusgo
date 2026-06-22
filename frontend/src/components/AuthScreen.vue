<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Mail, Lock, User as UserIcon, ArrowRight, ShieldCheck, Shield, KeyRound, RefreshCw } from 'lucide-vue-next';
import FutaLogo from './FutaLogo.vue';

const router = useRouter();

const API_BASE  = import.meta.env.VITE_API_BASE  || 'http://127.0.0.1:8000/api'
const BACKEND_URL = import.meta.env.VITE_BACKEND_URL || 'http://127.0.0.1:8000'

const props = withDefaults(defineProps<{
  initialMode?: 'login' | 'signup'
}>(), {
  initialMode: 'login'
})


type Mode = 'login' | 'signup' | 'forgot' | 'reset'

const campuses       = ref<any[]>([])
const selectedCampus = ref<string>('')
const mode           = ref<Mode>(props.initialMode)
const fullName       = ref('')
const email          = ref('')
const password       = ref('')
const isSubmitting   = ref(false)
const errorMsg       = ref('')
const successMsg     = ref('')

// Reset-password fields populated from URL params
const resetToken          = ref('')
const resetEmail          = ref('')
const newPassword         = ref('')
const newPasswordConfirm  = ref('')

// Post-register email verification banner
const showVerifyBanner = ref(false)
const resendLoading    = ref(false)

const fetchCampuses = async () => {
  try {
    const res = await fetch(`${API_BASE}/campuses`)
    if (!res.ok) throw new Error()
    campuses.value = await res.json()
  } catch {
    errorMsg.value = 'Unable to load campus list. Please try again.'
  }
}

onMounted(() => {
  fetchCampuses()
  // Detect reset-password URL params sent by the reset email
  const params = new URLSearchParams(window.location.search)
  if (params.get('token') && params.get('email')) {
    resetToken.value = params.get('token')!
    resetEmail.value = params.get('email')!
    email.value      = params.get('email')!
    mode.value       = 'reset'
    window.history.replaceState({}, '', window.location.pathname)
  }
})

const handleSubmit = async () => {
  errorMsg.value   = ''
  successMsg.value = ''

  if (mode.value === 'login') {
    if (!email.value || !password.value) { errorMsg.value = 'Please enter both email and password.'; return }
    isSubmitting.value = true
    try {
      const res  = await fetch(`${API_BASE}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email: email.value, password: password.value }),
      })
      const data = await res.json()
      if (!res.ok) { errorMsg.value = data.message || 'Login failed.'; return }
      localStorage.setItem('campusgo_token', data.token)
      localStorage.setItem('campusgo_user',  JSON.stringify(data.user))
      router.push('/home')
    } catch {
      errorMsg.value = 'Could not reach server. Please try again.'
    } finally {
      isSubmitting.value = false
    }
  }

  else if (mode.value === 'signup') {
    if (!fullName.value || !email.value || !password.value) { errorMsg.value = 'Please fill in all fields.'; return }
    if (!selectedCampus.value) { errorMsg.value = 'Please select your university.'; return }
    isSubmitting.value = true
    try {
      const res  = await fetch(`${API_BASE}/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          full_name:             fullName.value,
          email:                 email.value,
          password:              password.value,
          password_confirmation: password.value,
          campus_id:             selectedCampus.value,
        }),
      })
      const data = await res.json()
      if (!res.ok) { errorMsg.value = data.message || 'Registration failed.'; return }
      localStorage.setItem('campusgo_token', data.token)
      localStorage.setItem('campusgo_user',  JSON.stringify(data.user))
      showVerifyBanner.value = true
      router.push('/home')
    } catch {
      errorMsg.value = 'Could not reach server. Please try again.'
    } finally {
      isSubmitting.value = false
    }
  }

  else if (mode.value === 'forgot') {
    if (!email.value) { errorMsg.value = 'Please enter your email address.'; return }
    isSubmitting.value = true
    try {
      const res  = await fetch(`${API_BASE}/forgot-password`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email: email.value }),
      })
      const data = await res.json()
      if (!res.ok) { errorMsg.value = data.message || 'Request failed.'; return }
      successMsg.value = data.message
    } catch {
      errorMsg.value = 'Could not reach server. Please try again.'
    } finally {
      isSubmitting.value = false
    }
  }

  else if (mode.value === 'reset') {
    if (!newPassword.value || !newPasswordConfirm.value) { errorMsg.value = 'Please fill in both password fields.'; return }
    if (newPassword.value !== newPasswordConfirm.value)  { errorMsg.value = 'Passwords do not match.'; return }
    isSubmitting.value = true
    try {
      const res  = await fetch(`${API_BASE}/reset-password`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          token: resetToken.value,
          email: resetEmail.value,
          password: newPassword.value,
          password_confirmation: newPasswordConfirm.value,
        }),
      })
      const data = await res.json()
      if (!res.ok) { errorMsg.value = data.message || 'Reset failed.'; return }
      successMsg.value = data.message
      setTimeout(() => router.push('/login'), 2500)
    } catch {
      errorMsg.value = 'Could not reach server. Please try again.'
    } finally {
      isSubmitting.value = false
    }
  }
}

const handleResendVerification = async () => {
  resendLoading.value = true
  errorMsg.value = ''
  try {
    const token = localStorage.getItem('campusgo_token')
    const res = await fetch(`${API_BASE}/email/resend`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
    })
    const data = await res.json()
    if (!res.ok) { errorMsg.value = data.message || 'Failed to resend.'; return }
    successMsg.value = 'Verification email resent. Check your inbox.'
  } catch {
    errorMsg.value = 'Failed to resend. Please try again.'
  } finally {
    resendLoading.value = false
  }
}

const switchMode = (to: Mode) => {
  mode.value       = to
  errorMsg.value   = ''
  successMsg.value = ''
}

const signInWithGoogle = () => {
  // Redirect browser to backend which redirects to Google
  window.location.href = `${BACKEND_URL}/auth/google/redirect`
}
</script>

<template>
  <div
    class="min-h-screen w-full bg-[#131313] text-on-surface flex items-center justify-center max-md:p-4 p-8 relative font-sans">

    <img alt="Campus Background"
      class="fixed inset-0 w-full h-full object-cover z-0 filter brightness-25 saturate-50 pointer-events-none"
      src="https://lh3.googleusercontent.com/aida-public/AB6AXuCIBJ8U5F-0_Y7cQx7lSzZ26fu-Zr5z3ccwC3VC5fxHh2JtMP34pBsxCBOk6OstVEyrAlXBwGVfcjKTk-sIfmkkCQY6uYKOFz-kVSSDAFJNJqYLR366nWVTiIvN0CVMDGEInHT0aolFD0yUSTlKEbhhqyyF0Kb2Q50q3pQv0raDXBZE4HED3bDiHIR08GEQxfiZYol3FugYcc-holrhWyW6vPxbw5ni1qkR52XAmQT_gug6wH9fSguvIhUQw4N7ZCDnlqDtDgMx2e4" />
    <div class="fixed inset-0 pointer-events-none opacity-25 z-0 bg-gradient-to-t from-black via-transparent to-black" />
    <div
      class="absolute inset-0 z-0 pointer-events-none bg-[radial-gradient(at_0%_0%,rgba(255,95,82,0.04)_0px,transparent_50%),radial-gradient(at_100%_0%,rgba(111,251,190,0.03)_0px,transparent_50%)]" />

    <main class="w-full max-w-[460px] z-10 animate-in fade-in slide-in-from-bottom-4 duration-300">
      <div class="text-center mb-6">
        <div class="flex items-center justify-center gap-3 mb-3">
          <FutaLogo size="w-10 h-10" />
          <h1 class="text-3xl font-bold text-brand-primary-container tracking-tight">CampusGo</h1>
        </div>
        <p class="text-sm text-on-surface-variant font-medium tracking-wide opacity-80">University Transit Ecosystem</p>
      </div>

      <!-- Email verification banner shown after registration -->
      <div v-if="showVerifyBanner"
        class="mb-4 p-4 bg-brand-secondary/10 border border-brand-secondary/30 rounded-xl flex items-start gap-3 animate-in fade-in duration-200">
        <Mail class="w-5 h-5 text-brand-secondary shrink-0 mt-0.5" />
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold text-brand-secondary">Verify your email address</p>
          <p class="text-xs text-on-surface-variant mt-0.5 leading-relaxed">
            We sent a link to <span class="font-semibold text-on-surface">{{ email }}</span>. Click it to activate your
            account.
          </p>
          <button @click="handleResendVerification" :disabled="resendLoading"
            class="mt-2 text-xs text-brand-secondary font-bold hover:underline flex items-center gap-1 disabled:opacity-50 bg-transparent border-none cursor-pointer p-0">
            <RefreshCw class="w-3 h-3" :class="resendLoading ? 'animate-spin' : ''" />
            {{ resendLoading ? 'Sending...' : 'Resend email' }}
          </button>
        </div>
      </div>

      <div class="bg-[#1e1e1e]/85 backdrop-blur-xl border border-[#2a2a2a] rounded-xl p-6 md:p-10 shadow-2xl">
        <header class="mb-6">
          <h2 class="text-2xl font-bold text-on-surface mb-2">
            <span v-if="mode === 'login'">Login</span>
            <span v-else-if="mode === 'signup'">Create Your Account</span>
            <span v-else-if="mode === 'forgot'">Reset Password</span>
            <span v-else-if="mode === 'reset'">Set New Password</span>
          </h2>
          <p class="text-sm text-on-surface-variant leading-relaxed">
            <span v-if="mode === 'login'">Enter your FUTA credentials to access the transit hub.</span>
            <span v-else-if="mode === 'signup'">Join the CampusGo network and verify your student status.</span>
            <span v-else-if="mode === 'forgot'">Enter your email and we'll send a password reset link.</span>
            <span v-else-if="mode === 'reset'">Choose a strong new password for your account.</span>
          </p>
        </header>

        <div v-if="errorMsg"
          class="mb-4 p-3 bg-brand-error/15 border border-brand-error/30 rounded-lg text-xs text-brand-error font-medium">
          {{ errorMsg }}
        </div>
        <div v-if="successMsg"
          class="mb-4 p-3 bg-brand-tertiary/15 border border-brand-tertiary/30 rounded-lg text-xs text-brand-tertiary font-medium">
          {{ successMsg }}
        </div>

        <!-- LOGIN -->
        <form v-if="mode === 'login'" @submit.prevent="handleSubmit" class="space-y-5">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">University Email</label>
            <div class="relative">
              <Mail class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="email" v-model="email"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="name@university.edu" required />
            </div>
          </div>
          <div class="space-y-1.5">
            <div class="flex justify-between items-center">
              <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">Password</label>
              <button type="button" @click="switchMode('forgot')"
                class="text-xs text-brand-primary-container hover:underline font-bold bg-transparent border-none cursor-pointer p-0">
                Forgot Password?
              </button>
            </div>
            <div class="relative">
              <Lock class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="password" v-model="password"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="••••••••" required />
            </div>
          </div>
          <button type="submit" :disabled="isSubmitting"
            class="w-full h-12 bg-brand-primary-container active:scale-[0.98] text-white font-bold text-sm rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
            <div v-if="isSubmitting" class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></div>
              <span>Signing in...</span>
            </div>
            <template v-else>
              <span>Login</span><ArrowRight class="w-4 h-4" />
            </template>
          </button>
          <!-- Divider -->
          <div class="relative flex items-center gap-3 py-1">
            <div class="flex-1 h-px bg-[#2d2d2d]"></div>
            <span class="text-xs text-on-surface-variant/50 font-semibold uppercase tracking-widest">or</span>
            <div class="flex-1 h-px bg-[#2d2d2d]"></div>
          </div>

          <!-- Google -->
          <button type="button" @click="signInWithGoogle"
            class="w-full h-11 bg-white hover:bg-gray-50 active:scale-[0.98] text-[#1f1f1f] font-semibold text-sm rounded-lg transition-all flex items-center justify-center gap-3 cursor-pointer border border-[#dadce0]">
            <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continue with Google
          </button>

          <p class="text-center text-sm text-on-surface-variant pt-1">
            Don't have an account?
            <button type="button" @click="switchMode('signup')"
              class="text-brand-primary-container font-extrabold hover:underline ml-1.5 bg-transparent border-none p-0 cursor-pointer">
              Sign up.
            </button>
          </p>
        </form>

        <!-- SIGN UP -->
        <form v-else-if="mode === 'signup'" @submit.prevent="handleSubmit" class="space-y-5">
          <div class="space-y-1.5 animate-in slide-in-from-top-2 duration-150">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">Full Name</label>
            <div class="relative">
              <UserIcon class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="text" v-model="fullName"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="John Doe" required />
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">Select University</label>
            <select v-model="selectedCampus"
              class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg px-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container transition-all">
              <option value="" disabled>-- Select your university --</option>
              <option v-for="campus in campuses" :key="campus.id" :value="campus.id">{{ campus.name }}</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">University Email</label>
            <div class="relative">
              <Mail class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="email" v-model="email"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="name@university.edu" required />
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">Password</label>
            <div class="relative">
              <Lock class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="password" v-model="password"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="Min 8 chars, uppercase &amp; number" required />
            </div>
            <p class="text-xxs text-on-surface-variant/60 pl-1">Min. 8 characters with at least one uppercase letter and one number.</p>
          </div>
          <button type="submit" :disabled="isSubmitting"
            class="w-full h-12 bg-brand-primary-container active:scale-[0.98] text-white font-bold text-sm rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
            <div v-if="isSubmitting" class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></div>
              <span>Creating account...</span>
            </div>
            <template v-else>
              <span>Create Account</span><ArrowRight class="w-4 h-4" />
            </template>
          </button>
          <!-- Divider -->
          <div class="relative flex items-center gap-3 py-1">
            <div class="flex-1 h-px bg-[#2d2d2d]"></div>
            <span class="text-xs text-on-surface-variant/50 font-semibold uppercase tracking-widest">or</span>
            <div class="flex-1 h-px bg-[#2d2d2d]"></div>
          </div>

          <!-- Google -->
          <button type="button" @click="signInWithGoogle"
            class="w-full h-11 bg-white hover:bg-gray-50 active:scale-[0.98] text-[#1f1f1f] font-semibold text-sm rounded-lg transition-all flex items-center justify-center gap-3 cursor-pointer border border-[#dadce0]">
            <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84z" fill="#EA4335"/>
            </svg>
            Sign up with Google
          </button>

          <div class="pt-4 border-t border-[#2d2d2d]">
            <div class="flex items-start gap-3 p-4 rounded-lg bg-[#131313] border border-[#2d2d2d]">
              <ShieldCheck class="w-5 h-5 text-brand-tertiary shrink-0 mt-0.5" />
              <p class="text-xs text-on-surface-variant leading-relaxed">
                <span class="text-on-surface font-bold">Secure Verification:</span> We use your .edu email to maintain a
                closed, safe network for verified students only.
              </p>
            </div>
          </div>
          <p class="text-center text-sm text-on-surface-variant">
            Already have an account?
            <button type="button" @click="switchMode('login')"
              class="text-brand-primary-container font-extrabold hover:underline ml-1.5 bg-transparent border-none p-0 cursor-pointer">
              Log in.
            </button>
          </p>
        </form>

        <!-- FORGOT PASSWORD -->
        <form v-else-if="mode === 'forgot'" @submit.prevent="handleSubmit" class="space-y-5">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">University Email</label>
            <div class="relative">
              <Mail class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="email" v-model="email"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="name@university.edu" required />
            </div>
          </div>
          <button type="submit" :disabled="isSubmitting || !!successMsg"
            class="w-full h-12 bg-brand-primary-container active:scale-[0.98] text-white font-bold text-sm rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
            <div v-if="isSubmitting" class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></div>
              <span>Sending...</span>
            </div>
            <template v-else><span>Send Reset Link</span><ArrowRight class="w-4 h-4" /></template>
          </button>
          <p class="text-center text-sm text-on-surface-variant">
            <button type="button" @click="switchMode('login')"
              class="text-brand-primary-container font-bold hover:underline bg-transparent border-none p-0 cursor-pointer">
              ← Back to login
            </button>
          </p>
        </form>

        <!-- RESET PASSWORD -->
        <form v-else-if="mode === 'reset'" @submit.prevent="handleSubmit" class="space-y-5">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">New Password</label>
            <div class="relative">
              <KeyRound class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="password" v-model="newPassword"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="Min 8 chars, uppercase &amp; number" required />
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">Confirm New Password</label>
            <div class="relative">
              <KeyRound class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input type="password" v-model="newPasswordConfirm"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="••••••••" required />
            </div>
          </div>
          <button type="submit" :disabled="isSubmitting || !!successMsg"
            class="w-full h-12 bg-brand-primary-container active:scale-[0.98] text-white font-bold text-sm rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
            <div v-if="isSubmitting" class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></div>
              <span>Resetting...</span>
            </div>
            <template v-else><span>Set New Password</span><ArrowRight class="w-4 h-4" /></template>
          </button>
        </form>
      </div>

      <div class="mt-6 flex justify-center gap-5 text-xs font-semibold text-on-surface-variant/60">
        <a href="#" class="hover:text-on-surface transition-colors">Privacy Policy</a>
        <a href="#" class="hover:text-on-surface transition-colors">Terms of Service</a>
        <a href="#" class="hover:text-on-surface transition-colors">Accessibility</a>
      </div>
      <div class="mt-6 flex justify-center items-center gap-6 opacity-40">
        <div class="flex items-center gap-2">
          <Shield class="w-4 h-4 text-on-surface-variant" />
          <span class="text-xxs uppercase tracking-widest font-semibold">Encrypted</span>
        </div>
        <div class="flex items-center gap-2">
          <ShieldCheck class="w-4 h-4 text-on-surface-variant" />
          <span class="text-xxs uppercase tracking-widest font-semibold">Verified Network</span>
        </div>
      </div>
    </main>
  </div>
</template>
