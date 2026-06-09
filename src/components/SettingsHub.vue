<script setup lang="ts">
import { ref, watch } from 'vue';
import { User } from '../types';
import { 
  Bell, 
  ShieldAlert, 
  User as UserIcon
} from 'lucide-vue-next';

import { showAlert as alert } from '../alert';

const props = defineProps<{
  user: User
}>();

const emit = defineEmits(['update-user']);

const nameInput = ref(props.user.fullName);

watch(() => props.user.fullName, (newVal) => {
  nameInput.value = newVal;
});

const notifyToggles = ref({
  transDelays: true,
  poolApprovals: true,
  safetyAlerts: true
});

const privacyToggles = ref({
  shareLocation: true,
  showInRadar: true
});

const handleProfileSave = () => {
  if (!nameInput.value.trim()) {
    alert("Name field cannot be left empty.");
    return;
  }
  emit('update-user', { fullName: nameInput.value });
  alert("Profile settings synchronized successfully!");
};

const handleNotifyToggle = (key: 'transDelays' | 'poolApprovals' | 'safetyAlerts') => {
  notifyToggles.value[key] = !notifyToggles.value[key];
};

const handlePrivacyToggle = (key: 'shareLocation' | 'showInRadar') => {
  privacyToggles.value[key] = !privacyToggles.value[key];
};
</script>

<template>
  <div class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] max-md:overflow-y-auto overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">
    
    <!-- MAIN CONFIGURATION FORM -->
    <section class="flex-1 max-md:p-4 p-10 max-md:overflow-visible overflow-y-auto">
      <div class="max-w-2xl space-y-8 pb-12">
        <header class="select-none">
          <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Preferences Settings</h2>
          <p class="text-sm text-on-surface-variant font-light">Configure transit notification triggers and verify educational academic details.</p>
        </header>

        <!-- PROFILE CARD -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 space-y-6">
          <h3 class="text-sm font-bold text-on-surface uppercase tracking-widest flex items-center gap-2 select-none">
            <UserIcon class="w-5 h-5 text-brand-primary" />
            University Profile
          </h3>

          <form @submit.prevent="handleProfileSave" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Academic Name</label>
                <input 
                  type="text"
                  v-model="nameInput"
                  class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3.5 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
                />
              </div>

              <div class="space-y-1.5">
                <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Academic Email</label>
                <input 
                  type="email"
                  :value="user.email"
                  disabled
                  class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3.5 py-2 text-xs text-on-surface-variant opacity-60 cursor-not-allowed"
                />
              </div>
            </div>

            <button 
              type="submit"
              class="px-5 py-2 bg-brand-primary-container text-white font-bold text-xs rounded hover:brightness-110 active:scale-97 cursor-pointer transition-all border-none"
            >
              Save Profile Changes
            </button>
          </form>
        </div>

        <!-- NOTIFICATIONS SWITCHERS -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 space-y-6">
          <h3 class="text-sm font-bold text-on-surface uppercase tracking-widest flex items-center gap-2 select-none">
            <Bell class="w-5 h-5 text-brand-secondary" />
            Transit Notification Alarms
          </h3>

          <div class="space-y-4 divide-y divide-[#2d2d2d]/60">
            <!-- Toggle 1 -->
            <div class="flex justify-between items-center pt-2 first:pt-0">
              <div>
                <h4 class="text-xs font-bold text-on-surface">Transit Route Delay Warnings</h4>
                <p class="text-xxs text-on-surface-variant mt-0.5 font-light max-w-sm leading-relaxed">Alert me when red, blue, or green shuttles experience delays exceeding +10 minutes.</p>
              </div>
              <button 
                type="button" 
                @click="handleNotifyToggle('transDelays')"
                class="w-10 h-6.5 rounded-full p-1 transition-colors cursor-pointer border-none"
                :class="notifyToggles.transDelays ? 'bg-brand-primary' : 'bg-[#353534]'"
              >
                <div class="w-4.5 h-4.5 rounded-full bg-black transition-transform" :class="notifyToggles.transDelays ? 'translate-x-3.5' : 'translate-x-0'"></div>
              </button>
            </div>

            <!-- Toggle 2 -->
            <div class="flex justify-between items-center pt-4">
              <div>
                <h4 class="text-xs font-bold text-on-surface">Carpool Seat approvals</h4>
                <p class="text-xxs text-on-surface-variant mt-0.5 font-light max-w-sm leading-relaxed">Alert me instantly when a carpool companion companion accepts or updates seat allocations.</p>
              </div>
              <button 
                type="button" 
                @click="handleNotifyToggle('poolApprovals')"
                class="w-10 h-6.5 rounded-full p-1 transition-colors cursor-pointer border-none"
                :class="notifyToggles.poolApprovals ? 'bg-brand-primary' : 'bg-[#353534]'"
              >
                <div class="w-4.5 h-4.5 rounded-full bg-black transition-transform" :class="notifyToggles.poolApprovals ? 'translate-x-3.5' : 'translate-x-0'"></div>
              </button>
            </div>

            <!-- Toggle 3 -->
            <div class="flex justify-between items-center pt-4">
              <div>
                <h4 class="text-xs font-bold text-on-surface">SOS dispatcher broadcasts</h4>
                <p class="text-xxs text-on-surface-variant mt-0.5 font-light max-w-sm leading-relaxed">Alert me of safety alerts near campus blocks.</p>
              </div>
              <button 
                type="button" 
                @click="handleNotifyToggle('safetyAlerts')"
                class="w-10 h-6.5 rounded-full p-1 transition-colors cursor-pointer border-none"
                :class="notifyToggles.safetyAlerts ? 'bg-brand-primary' : 'bg-[#353534]'"
              >
                <div class="w-4.5 h-4.5 rounded-full bg-black transition-transform" :class="notifyToggles.safetyAlerts ? 'translate-x-3.5' : 'translate-x-0'"></div>
              </button>
            </div>
          </div>
        </div>

        <!-- SECURITY & PRIVACY -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 space-y-6">
          <h3 class="text-sm font-bold text-on-surface uppercase tracking-widest flex items-center gap-2 select-none">
            <ShieldAlert class="w-5 h-5 text-brand-tertiary" />
            Security & Buddy Privacy
          </h3>

          <div class="space-y-4 divide-y divide-[#2d2d2d]/60">
            <!-- Toggle 1 -->
            <div class="flex justify-between items-center pt-2 first:pt-0">
              <div>
                <h4 class="text-xs font-bold text-on-surface">Show in Buddy Radar circles</h4>
                <p class="text-xxs text-on-surface-variant mt-0.5 font-light max-w-sm leading-relaxed">Let available buddies see my coarse location node on the SafeWalk concentric radar grid when I launch a request.</p>
              </div>
              <button 
                type="button" 
                @click="handlePrivacyToggle('showInRadar')"
                class="w-10 h-6.5 rounded-full p-1 transition-colors cursor-pointer border-none"
                :class="privacyToggles.showInRadar ? 'bg-brand-primary' : 'bg-[#353534]'"
              >
                <div class="w-4.5 h-4.5 rounded-full bg-black transition-transform" :class="privacyToggles.showInRadar ? 'translate-x-3.5' : 'translate-x-0'"></div>
              </button>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>
</template>
