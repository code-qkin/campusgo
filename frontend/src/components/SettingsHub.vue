<script setup lang="ts">
import { ref, watch } from 'vue';
import { User } from '../types';
import { Bell, ShieldAlert, User as UserIcon, Lock, CheckCircle, Camera } from 'lucide-vue-next';
import { api } from '../api';

const props = defineProps<{ user: User }>();
const emit  = defineEmits(['update-user']);

// Profile
const nameInput       = ref(props.user.fullName);
const profileSaving   = ref(false);
const profileMsg      = ref('');
const profileError    = ref('');

watch(() => props.user.fullName, (v) => { nameInput.value = v });

const handleProfileSave = async () => {
  if (!nameInput.value.trim()) { profileError.value = 'Name cannot be empty.'; return }
  profileSaving.value = true;
  profileMsg.value    = '';
  profileError.value  = '';
  try {
    const data = await api.patch('/user/profile', { full_name: nameInput.value.trim() });
    emit('update-user', { fullName: data.user.full_name });
    // update localStorage
    const stored = JSON.parse(localStorage.getItem('campusgo_user') || '{}');
    localStorage.setItem('campusgo_user', JSON.stringify({ ...stored, full_name: data.user.full_name }));
    profileMsg.value = 'Profile updated successfully.';
  } catch (e: any) {
    profileError.value = e.message || 'Failed to update profile.';
  } finally {
    profileSaving.value = false;
  }
};

// Password change
const currentPassword  = ref('');
const newPassword      = ref('');
const confirmPassword  = ref('');
const passwordSaving   = ref(false);
const passwordMsg      = ref('');
const passwordError    = ref('');

const handlePasswordChange = async () => {
  passwordMsg.value   = '';
  passwordError.value = '';
  if (!currentPassword.value || !newPassword.value || !confirmPassword.value) {
    passwordError.value = 'Please fill in all password fields.'; return;
  }
  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = 'New passwords do not match.'; return;
  }
  passwordSaving.value = true;
  try {
    const data = await api.patch('/user/password', {
      current_password: currentPassword.value,
      password:         newPassword.value,
      password_confirmation: confirmPassword.value,
    });
    passwordMsg.value     = data.message;
    currentPassword.value = '';
    newPassword.value     = '';
    confirmPassword.value = '';
  } catch (e: any) {
    passwordError.value = e.message || 'Failed to change password.';
  } finally {
    passwordSaving.value = false;
  }
};

// Avatar upload
const avatarPreview    = ref<string | null>(props.user.avatarUrl || null);
const avatarUploading  = ref(false);
const avatarMsg        = ref('');

watch(() => props.user.avatarUrl, (v) => { if (v) avatarPreview.value = v });

const onAvatarPick = async (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (ev) => { avatarPreview.value = ev.target?.result as string };
  reader.readAsDataURL(file);

  avatarUploading.value = true;
  avatarMsg.value = '';
  try {
    const form = new FormData();
    form.append('avatar', file);
    const token = localStorage.getItem('campusgo_token') || '';
    const res   = await fetch(`${import.meta.env.VITE_API_BASE}/user/avatar`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      body: form,
    });
    const data  = await res.json();
    if (!res.ok) throw new Error(data.message || 'Upload failed');
    emit('update-user', { avatarUrl: data.avatar_url });
    const stored = JSON.parse(localStorage.getItem('campusgo_user') || '{}');
    localStorage.setItem('campusgo_user', JSON.stringify({ ...stored, avatar_url: data.avatar_url }));
    avatarMsg.value = 'Photo updated!';
    setTimeout(() => { avatarMsg.value = '' }, 3000);
  } catch (e: any) {
    avatarMsg.value = e.message || 'Upload failed';
  } finally {
    avatarUploading.value = false;
  }
};

// Notification toggles (UI only — persisted locally until backend supports it)
const notifyToggles = ref({ transDelays: true, poolApprovals: true, safetyAlerts: true });
const privacyToggles = ref({ shareLocation: true });
</script>

<template>
  <div
    class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] max-md:overflow-y-auto overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">

    <section class="flex-1 max-md:p-4 p-10 max-md:overflow-visible overflow-y-auto">
      <div class="max-w-2xl space-y-8 pb-12">
        <header class="select-none">
          <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Settings</h2>
          <p class="text-sm text-on-surface-variant font-light">Manage your profile and account preferences.</p>
        </header>

        <!-- PROFILE -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 space-y-6">
          <h3 class="text-sm font-bold text-on-surface uppercase tracking-widest flex items-center gap-2 select-none">
            <UserIcon class="w-5 h-5 text-brand-primary" /> University Profile
          </h3>

          <!-- Avatar -->
          <div class="flex items-center gap-4">
            <label class="relative cursor-pointer group">
              <div class="w-16 h-16 rounded-full overflow-hidden bg-[#131313] border-2 border-[#2d2d2d] group-hover:border-brand-primary transition-all flex items-center justify-center shrink-0">
                <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" alt="Avatar" />
                <UserIcon v-else class="w-7 h-7 text-on-surface-variant/40" />
              </div>
              <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-brand-primary flex items-center justify-center border-2 border-[#1e1e1e]">
                <Camera class="w-3 h-3 text-white" />
              </div>
              <input type="file" accept="image/*" @change="onAvatarPick" class="sr-only" :disabled="avatarUploading" />
            </label>
            <div>
              <p class="text-sm font-bold text-on-surface">{{ user.fullName }}</p>
              <p class="text-xs text-on-surface-variant mt-0.5">{{ avatarUploading ? 'Uploading...' : (avatarMsg || 'Click photo to change') }}</p>
            </div>
          </div>

          <div v-if="profileError"
            class="p-3 bg-brand-error/15 border border-brand-error/30 rounded-lg text-xs text-brand-error font-medium">
            {{ profileError }}
          </div>
          <div v-if="profileMsg"
            class="p-3 bg-brand-tertiary/15 border border-brand-tertiary/30 rounded-lg text-xs text-brand-tertiary font-medium flex items-center gap-2">
            <CheckCircle class="w-4 h-4" /> {{ profileMsg }}
          </div>

          <form @submit.prevent="handleProfileSave" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Display Name</label>
                <input type="text" v-model="nameInput"
                  class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3.5 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary" />
              </div>
              <div class="space-y-1.5">
                <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Email (read-only)</label>
                <input type="email" :value="user.email" disabled
                  class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3.5 py-2 text-xs text-on-surface-variant opacity-60 cursor-not-allowed" />
              </div>
            </div>
            <button type="submit" :disabled="profileSaving"
              class="px-5 py-2 bg-brand-primary-container text-white font-bold text-xs rounded hover:brightness-110 active:scale-97 cursor-pointer transition-all border-none disabled:opacity-60">
              {{ profileSaving ? 'Saving...' : 'Save Profile' }}
            </button>
          </form>
        </div>

        <!-- CHANGE PASSWORD -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 space-y-6">
          <h3 class="text-sm font-bold text-on-surface uppercase tracking-widest flex items-center gap-2 select-none">
            <Lock class="w-5 h-5 text-brand-secondary" /> Change Password
          </h3>

          <div v-if="passwordError"
            class="p-3 bg-brand-error/15 border border-brand-error/30 rounded-lg text-xs text-brand-error font-medium">
            {{ passwordError }}
          </div>
          <div v-if="passwordMsg"
            class="p-3 bg-brand-tertiary/15 border border-brand-tertiary/30 rounded-lg text-xs text-brand-tertiary font-medium flex items-center gap-2">
            <CheckCircle class="w-4 h-4" /> {{ passwordMsg }}
          </div>

          <form @submit.prevent="handlePasswordChange" class="space-y-4">
            <div class="space-y-1.5">
              <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Current Password</label>
              <input type="password" v-model="currentPassword"
                class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3.5 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
                placeholder="Your current password" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">New Password</label>
                <input type="password" v-model="newPassword"
                  class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3.5 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
                  placeholder="Min 8 chars, uppercase &amp; number" />
              </div>
              <div class="space-y-1.5">
                <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Confirm New Password</label>
                <input type="password" v-model="confirmPassword"
                  class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3.5 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
                  placeholder="••••••••" />
              </div>
            </div>
            <button type="submit" :disabled="passwordSaving"
              class="px-5 py-2 bg-[#2a2a2a] text-on-surface font-bold text-xs rounded hover:bg-[#333] active:scale-97 cursor-pointer transition-all border-none disabled:opacity-60">
              {{ passwordSaving ? 'Changing...' : 'Change Password' }}
            </button>
          </form>
        </div>

        <!-- NOTIFICATIONS -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 space-y-6">
          <h3 class="text-sm font-bold text-on-surface uppercase tracking-widest flex items-center gap-2 select-none">
            <Bell class="w-5 h-5 text-brand-secondary" /> Notification Preferences
          </h3>
          <div class="space-y-4 divide-y divide-[#2d2d2d]/60">
            <div class="flex justify-between items-center pt-2 first:pt-0">
              <div>
                <h4 class="text-xs font-bold text-on-surface">Carpool Seat Approvals</h4>
                <p class="text-xxs text-on-surface-variant mt-0.5 font-light max-w-sm leading-relaxed">
                  Alert me when a driver accepts or rejects my ride request.
                </p>
              </div>
              <button type="button" @click="notifyToggles.poolApprovals = !notifyToggles.poolApprovals"
                class="w-10 h-6.5 rounded-full p-1 transition-colors cursor-pointer border-none shrink-0"
                :class="notifyToggles.poolApprovals ? 'bg-brand-primary' : 'bg-[#353534]'">
                <div class="w-4.5 h-4.5 rounded-full bg-black transition-transform"
                  :class="notifyToggles.poolApprovals ? 'translate-x-3.5' : 'translate-x-0'" />
              </button>
            </div>
            <div class="flex justify-between items-center pt-4">
              <div>
                <h4 class="text-xs font-bold text-on-surface">Lost &amp; Found Matches</h4>
                <p class="text-xxs text-on-surface-variant mt-0.5 font-light max-w-sm leading-relaxed">
                  Alert me when someone contacts me about a reported item.
                </p>
              </div>
              <button type="button" @click="notifyToggles.transDelays = !notifyToggles.transDelays"
                class="w-10 h-6.5 rounded-full p-1 transition-colors cursor-pointer border-none shrink-0"
                :class="notifyToggles.transDelays ? 'bg-brand-primary' : 'bg-[#353534]'">
                <div class="w-4.5 h-4.5 rounded-full bg-black transition-transform"
                  :class="notifyToggles.transDelays ? 'translate-x-3.5' : 'translate-x-0'" />
              </button>
            </div>
          </div>
        </div>

        <!-- SECURITY -->
        <div class="bg-[#201f1f] border border-[#2d2d2d] rounded-xl p-6 space-y-6">
          <h3 class="text-sm font-bold text-on-surface uppercase tracking-widest flex items-center gap-2 select-none">
            <ShieldAlert class="w-5 h-5 text-brand-tertiary" /> Security &amp; Privacy
          </h3>
          <div class="space-y-4 divide-y divide-[#2d2d2d]/60">
            <div class="flex justify-between items-center pt-2 first:pt-0">
              <div>
                <h4 class="text-xs font-bold text-on-surface">Share my ride location</h4>
                <p class="text-xxs text-on-surface-variant mt-0.5 font-light max-w-sm leading-relaxed">
                  Allow fellow passengers to see your live pickup location during an active ride.
                </p>
              </div>
              <button type="button" @click="privacyToggles.shareLocation = !privacyToggles.shareLocation"
                class="w-10 h-6.5 rounded-full p-1 transition-colors cursor-pointer border-none shrink-0"
                :class="privacyToggles.shareLocation ? 'bg-brand-primary' : 'bg-[#353534]'">
                <div class="w-4.5 h-4.5 rounded-full bg-black transition-transform"
                  :class="privacyToggles.shareLocation ? 'translate-x-3.5' : 'translate-x-0'" />
              </button>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>
</template>
