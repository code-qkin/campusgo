<script setup lang="ts">
import { ref } from 'vue';
import { Mail, Lock, User as UserIcon, School, ArrowRight, ShieldCheck, Shield } from 'lucide-vue-next';

import { showAlert as alert } from '../alert';

const props = withDefaults(defineProps<{
  initialMode?: 'login' | 'signup'
}>(), {
  initialMode: 'login'
});

const emit = defineEmits(['auth-success']);

const mode = ref<'login' | 'signup'>(props.initialMode);
const fullName = ref('');
const email = ref('alex.s@university.edu');
const password = ref('campusgo123');
const isSubmitting = ref(false);
const errorMsg = ref('');

const handleSubmit = () => {
  if (!email.value) {
    errorMsg.value = 'Please enter your university email';
    return;
  }
  if (!email.value.endsWith('.edu') && !email.value.includes('@')) {
    errorMsg.value = 'Must be a valid .edu university email to verify academic status';
    return;
  }
  if (!password.value || password.value.length < 5) {
    errorMsg.value = 'Password must be at least 5 characters';
    return;
  }

  isSubmitting.value = true;
  errorMsg.value = '';

  setTimeout(() => {
    isSubmitting.value = false;
    emit(
      'auth-success',
      fullName.value || (email.value.split('@')[0] ? email.value.split('@')[0].toUpperCase() : 'Alex'),
      email.value
    );
  }, 1200);
};

const handleSSOLogin = () => {
  isSubmitting.value = true;
  setTimeout(() => {
    isSubmitting.value = false;
    emit('auth-success', 'Alex', 'alex.s@university.edu');
  }, 1000);
};
</script>

<template>
  <div class="min-h-screen w-full bg-[#131313] text-on-surface flex items-center justify-center p-8 relative font-sans">
    <!-- Background Graphic Setup -->
    <img 
      alt="Campus Background" 
      class="fixed inset-0 w-full h-full object-cover z-0 filter brightness-25 saturate-50 pointer-events-none" 
      src="https://lh3.googleusercontent.com/aida-public/AB6AXuCIBJ8U5F-0_Y7cQx7lSzZ26fu-Zr5z3ccwC3VC5fxHh2JtMP34pBsxCBOk6OstVEyrAlXBwGVfcjKTk-sIfmkkCQY6uYKOFz-kVSSDAFJNJqYLR366nWVTiIvN0CVMDGEInHT0aolFD0yUSTlKEbhhqyyF0Kb2Q50q3pQv0raDXBZE4HED3bDiHIR08GEQxfiZYol3FugYcc-holrhWyW6vPxbw5ni1qkR52XAmQT_gug6wH9fSguvIhUQw4N7ZCDnlqDtDgMx2e4"
    />
    <div class="fixed inset-0 pointer-events-none opacity-25 z-0 bg-gradient-to-t from-black via-transparent to-black"></div>

    <!-- Atmospheric color gradient mesh -->
    <div class="absolute inset-0 z-0 pointer-events-none bg-[radial-gradient(at_0%_0%,rgba(255,95,82,0.04)_0px,transparent_50%),radial-gradient(at_100%_0%,rgba(111,251,190,0.03)_0px,transparent_50%)]"></div>

    <main class="w-full max-w-[460px] z-10 animate-in fade-in slide-in-from-bottom-4 duration-300">
      <!-- Logo and Brand -->
      <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-brand-primary-container tracking-tight mb-1">CampusGo</h1>
        <p class="text-sm text-on-surface-variant font-medium tracking-wide opacity-80">
          {{ mode === 'login' ? 'University Transit Management' : 'University Transit Ecosystem' }}
        </p>
      </div>

      <!-- Glass Shadow Card -->
      <div class="bg-[#1e1e1e]/85 backdrop-blur-xl border border-[#2a2a2a] rounded-xl p-8 md:p-10 shadow-2xl">
        <header class="mb-6">
          <h2 class="text-2xl font-bold text-on-surface mb-2">
            {{ mode === 'login' ? 'Login' : 'Create Your Account' }}
          </h2>
          <p class="text-sm text-on-surface-variant leading-relaxed">
            {{ mode === 'login' 
              ? 'Enter your university credentials to access the transit hub.' 
              : 'Join the CampusGo network. Verify student status to get started.'
            }}
          </p>
        </header>

        <div v-if="mode === 'login'" class="mb-5 p-3.5 bg-brand-primary-container/10 border border-brand-primary-container/20 rounded-lg text-xs leading-relaxed text-on-surface-variant flex items-start gap-2.5 select-none animate-in fade-in duration-200">
          <span class="text-brand-primary-container font-bold text-sm">💡</span>
          <div>
            <span class="text-[#fff] font-medium">Demo Mode Active:</span> We packed the form with test student credentials. Simply click <span class="text-brand-primary-container font-bold">Login</span> below or choose <span class="text-brand-primary-container font-bold">University SSO</span> to jump right in!
          </div>
        </div>

        <div v-if="errorMsg" class="mb-4 p-3 bg-brand-error/15 border border-brand-error/30 rounded-lg text-xs text-brand-error font-medium">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div v-if="mode === 'signup'" class="space-y-1.5 animate-in slide-in-from-top-2 duration-150">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">Full Name</label>
            <div class="relative">
              <UserIcon class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input 
                type="text" 
                v-model="fullName"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="John Doe"
              />
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">University Email</label>
            <div class="relative">
              <Mail class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input 
                type="email" 
                v-model="email"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="name@university.edu"
                required
              />
            </div>
          </div>

          <div class="space-y-1.5">
            <div class="flex justify-between items-center">
              <label class="text-xs font-semibold text-on-surface-variant block uppercase tracking-wide">Password</label>
              <button 
                v-if="mode === 'login'" 
                type="button" 
                @click="alert('Password reset route simulated.')" 
                class="text-xs text-brand-primary-container hover:underline font-bold"
              >
                Forgot Password?
              </button>
            </div>
            <div class="relative">
              <Lock class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-60" />
              <input 
                type="password" 
                v-model="password"
                class="w-full h-11 bg-[#131313] border border-[#2d2d2d] rounded-lg pl-12 pr-4 text-sm text-on-surface focus:outline-none focus:border-brand-primary-container focus:ring-1 focus:ring-brand-primary-container transition-all"
                placeholder="••••••••"
                required
              />
            </div>
          </div>

          <button 
            type="submit" 
            :disabled="isSubmitting"
            class="w-full h-12 bg-brand-primary-container active:scale-[0.98] text-white font-bold text-sm rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed"
          >
            <template v-if="isSubmitting">
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></div>
                <span>Authenticating...</span>
              </div>
            </template>
            <template v-else>
              <span>{{ mode === 'login' ? 'Login' : 'Verify .edu Email' }}</span>
              <ArrowRight class="w-4 h-4" />
            </template>
          </button>

          <template v-if="mode === 'login'">
            <div class="relative py-2 flex items-center">
              <div class="flex-grow border-t border-[#2d2d2d]"></div>
              <span class="flex-shrink mx-4 text-xs font-semibold text-on-surface-variant/70 uppercase tracking-widest">OR</span>
              <div class="flex-grow border-t border-[#2d2d2d]"></div>
            </div>

            <button 
              type="button"
              @click="handleSSOLogin"
              :disabled="isSubmitting"
              class="w-full h-12 bg-[#201f1f] border border-[#2d2d2d] text-on-surface font-semibold text-sm rounded-lg hover:bg-brand-surface-high transition-all flex items-center justify-center gap-3 cursor-pointer"
            >
              <School class="w-5 h-5 text-brand-secondary" />
              <span>Login with University SSO</span>
            </button>
          </template>
        </form>

        <!-- Secure details reminder for signup -->
        <div v-if="mode === 'signup'" class="mt-6 pt-5 border-t border-[#2d2d2d] animate-in fade-in duration-200">
          <div class="flex items-start gap-3 p-4 rounded-lg bg-[#131313] border border-[#2d2d2d]">
            <ShieldCheck class="w-5 h-5 text-brand-tertiary shrink-0 mt-0.5" />
            <p class="text-xs text-on-surface-variant leading-relaxed">
              <span class="text-on-surface font-bold">Secure Verification:</span> We use your .edu email to ensure a closed, safe network for students only.
            </p>
          </div>
        </div>

        <!-- Sibling Toggle switcher -->
        <footer class="mt-8 text-center pt-2 border-t border-transparent">
          <p class="text-sm text-on-surface-variant">
            {{ mode === 'login' ? "Don't have an account?" : "Already have an account?" }}
            <button 
              @click="mode = (mode === 'login' ? 'signup' : 'login'); errorMsg = ''"
              class="text-brand-primary-container font-extrabold hover:underline ml-1.5 bg-transparent border-none p-0 cursor-pointer"
            >
              {{ mode === 'login' ? 'Sign up.' : 'Log in.' }}
            </button>
          </p>
        </footer>
      </div>

      <!-- Bottom Utility Footer -->
      <div class="mt-8 flex justify-center gap-8 text-xs font-semibold text-on-surface-variant/60">
        <a href="#" class="hover:text-on-surface transition-colors">Privacy Policy</a>
        <a href="#" class="hover:text-on-surface transition-colors">Terms of Service</a>
        <a href="#" class="hover:text-on-surface transition-colors">Accessibility</a>
      </div>

      <!-- Decorative Badge Icons -->
      <div class="mt-10 flex justify-center items-center gap-6 opacity-40">
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
