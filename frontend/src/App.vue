<script setup lang="ts">
import { ref } from 'vue';
import { ActiveTab, User, CampusAlert } from './types';
import { initialUser, mockAlerts } from './data';

import { showAlert as alert } from './alert';
import { customConfirm } from './modal';

// Component imports
import CustomAlert from './components/CustomAlert.vue';
import CustomModal from './components/CustomModal.vue';
import WelcomeScreen from './components/WelcomeScreen.vue';
import AuthScreen from './components/AuthScreen.vue';
import Sidebar from './components/Sidebar.vue';
import Header from './components/Header.vue';
import HomeDashboard from './components/HomeDashboard.vue';
import TransitRoutes from './components/TransitRoutes.vue';
import CarpoolHub from './components/CarpoolHub.vue';
import SafeWalkHub from './components/SafeWalkHub.vue';
import LostFoundHub from './components/LostFoundHub.vue';
import UnderMaintenance from './components/UnderMaintenance.vue';
import RewardsHub from './components/RewardsHub.vue';
import HistoryHub from './components/HistoryHub.vue';
import SettingsHub from './components/SettingsHub.vue';

import { Siren, X, Home, Map, Users, Shield, Package, Trophy, Clock, Settings } from 'lucide-vue-next';

const hasToken = !!localStorage.getItem('campusgo_token');
const savedTab = localStorage.getItem('campusgo_tab');
const activeTab = ref<ActiveTab>(
  hasToken
    ? (savedTab ? savedTab as ActiveTab : ActiveTab.Home)
    : ActiveTab.Welcome
);
const storedUser = localStorage.getItem('campusgo_user')
const user = ref<User>(storedUser ? {
  ...initialUser,
  fullName: JSON.parse(storedUser).full_name || initialUser.fullName,
  email: JSON.parse(storedUser).email || initialUser.email,
  points: JSON.parse(storedUser).points || 0,
} : initialUser);
const alerts = ref<CampusAlert[]>(mockAlerts);
const mobileMenuOpen = ref(false);

const mobileMenuItems = [
  { tab: ActiveTab.Home, label: 'Home Dashboard', icon: Home },
  { tab: ActiveTab.Routes, label: 'Transit Routes', icon: Map },
  { tab: ActiveTab.Carpool, label: 'Carpool Hub', icon: Users },
  { tab: ActiveTab.SafeWalk, label: 'SafeWalk Escorts', icon: Shield },
  { tab: ActiveTab.LostFound, label: 'Lost & Found', icon: Package },
  { tab: ActiveTab.Rewards, label: 'Eco-Rewards', icon: Trophy },
  { tab: ActiveTab.History, label: 'Activity Logs', icon: Clock },
  { tab: ActiveTab.Settings, label: 'Settings', icon: Settings }
];

// Emergency SOS state
const sosActive = ref(false);
const sosCountdown = ref(5);
const sosDispatched = ref(false);
let timerId: ReturnType<typeof setInterval> | null = null;

const startSOSInterval = () => {
  if (timerId) clearInterval(timerId);
  timerId = setInterval(() => {
    if (sosCountdown.value > 0) {
      sosCountdown.value--;
    } else {
      sosDispatched.value = true;
      if (timerId) {
        clearInterval(timerId);
        timerId = null;
      }
    }
  }, 1000);
};

const triggerSOS = () => {
  sosActive.value = true;
  sosCountdown.value = 5;
  sosDispatched.value = false;
  startSOSInterval();
};

const cancelSOS = () => {
  sosActive.value = false;
  sosCountdown.value = 5;
  sosDispatched.value = false;
  if (timerId) {
    clearInterval(timerId);
    timerId = null;
  }
};

const handleLogout = async () => {
  const confirmLogout = await customConfirm('Logout Confirmation', 'Are you sure you want to log out?');
  if (confirmLogout) {
    localStorage.removeItem('campusgo_token');
    localStorage.removeItem('campusgo_user');
    localStorage.removeItem('campusgo_tab');
    activeTab.value = ActiveTab.Welcome;
  }
};

const handleAuthSuccess = (fullName: string, email: string) => {
  user.value = {
    ...user.value,
    fullName,
    email
  };
  activeTab.value = ActiveTab.Home;
};

const updateUserPoints = (newPoints: number) => {
  user.value.points = newPoints;
};

const updateUser = (updated: Partial<User>) => {
  user.value = {
    ...user.value,
    ...updated
  };
};

const setActiveTab = (tab: ActiveTab) => {
  activeTab.value = tab;
  localStorage.setItem('campusgo_tab', tab);
  mobileMenuOpen.value = false;
};
</script>

<template>
  <CustomAlert />
  <CustomModal />
  <!-- Welcome Screen -->
  <WelcomeScreen v-if="activeTab === ActiveTab.Welcome" @get-started="setActiveTab(ActiveTab.Login)" />

  <!-- Login/Sign Up Auth screen -->
  <AuthScreen v-else-if="activeTab === ActiveTab.Login || activeTab === ActiveTab.SignUp"
    :initial-mode="activeTab === ActiveTab.Login ? 'login' : 'signup'" @auth-success="handleAuthSuccess" />

  <!-- Shell Layout for Authenticated Dashboard Tabs -->
  <div v-else class="min-h-screen bg-[#131313] text-on-surface flex font-sans overflow-hidden">

    <!-- Sidebar Section -->
    <Sidebar :active-tab="activeTab" @change-tab="setActiveTab" @trigger-sos="triggerSOS" @logout="handleLogout" />

    <!-- Main Panel Side Block shift -->
    <div class="flex-1 flex flex-col min-w-0">

      <!-- Navigation Header bar -->
      <Header :active-tab="activeTab" :user="user" @logout="handleLogout" @search-change="() => { }"
        @toggle-menu="mobileMenuOpen = !mobileMenuOpen" />

      <!-- Tab specific components content wrapper -->
      <main class="flex-1 min-h-0 max-md:pb-16">
        <HomeDashboard v-if="activeTab === ActiveTab.Home" :user="user" :alerts="alerts" @change-tab="setActiveTab" />
        <!-- <TransitRoutes v-else-if="activeTab === ActiveTab.Routes" /> -->
        <CarpoolHub v-else-if="activeTab === ActiveTab.Carpool" />
        <!-- <SafeWalkHub v-else-if="activeTab === ActiveTab.SafeWalk" /> -->
        <LostFoundHub v-else-if="activeTab === ActiveTab.LostFound" />
        <UnderMaintenance v-else-if="activeTab === ActiveTab.Routes" title="Live Bus Tracking"
          message="Real-time bus tracking is coming soon." />
        <UnderMaintenance v-else-if="activeTab === ActiveTab.SafeWalk" title="SafeWalk"
          message="Safety escort feature is being built." />
        <UnderMaintenance v-else-if="activeTab === ActiveTab.Rewards" title="Rewards"
          message="Points and rewards system is coming soon." />
        <UnderMaintenance v-else-if="activeTab === ActiveTab.Settings" title="Settings"
          message="Settings page is being rebuilt." />
        <!-- <RewardsHub v-else-if="activeTab === ActiveTab.Rewards" :user="user" @update-user-points="updateUserPoints" /> -->
        <HistoryHub v-else-if="activeTab === ActiveTab.History" />
        <!-- <SettingsHub v-else-if="activeTab === ActiveTab.Settings" :user="user" @update-user="updateUser" /> -->
      </main>

    </div>

    <!-- Mobile Bottom Nav (max-md only) -->
    <div
      class="hidden max-md:flex fixed bottom-0 left-0 right-0 h-16 bg-[#1c1b1b] border-t border-[#2d2d2d] justify-around items-center z-50 shadow-[0_-4px_24px_rgba(0,0,0,0.5)]">
      <button @click="setActiveTab(ActiveTab.Home)" class="flex flex-col items-center p-2"
        :class="activeTab === ActiveTab.Home ? 'text-brand-primary' : 'text-on-surface-variant'">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
      </button>
      <button @click="setActiveTab(ActiveTab.Routes)" class="flex flex-col items-center p-2"
        :class="activeTab === ActiveTab.Routes ? 'text-brand-primary' : 'text-on-surface-variant'">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M8 6v6" />
          <path d="M15 6v6" />
          <path d="M2 12h19.6" />
          <path
            d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3" />
          <circle cx="7" cy="18" r="2" />
          <circle cx="17" cy="18" r="2" />
        </svg>
      </button>
      <!-- SOS trigger -->
      <button @click="triggerSOS"
        class="flex flex-col items-center p-3 -mt-6 bg-[#93000a] rounded-full border-4 border-[#131313] text-[#ffb4ab] shadow-lg cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 12.2V6.5a5 5 0 0 1 10 0v5.8" />
          <path d="M5.7 15a4.3 4.3 0 0 0 .8 8.6h11a4.3 4.3 0 0 0 .8-8.6c0-3.9-3.5-7.4-6.3-7.4S5.7 11.1 5.7 15Z" />
          <circle cx="12" cy="9" r="1" />
        </svg>
      </button>
      <button @click="setActiveTab(ActiveTab.SafeWalk)" class="flex flex-col items-center p-2"
        :class="activeTab === ActiveTab.SafeWalk ? 'text-brand-primary' : 'text-on-surface-variant'">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
          <path d="m9 12 2 2 4-4" />
        </svg>
      </button>
      <button @click="setActiveTab(ActiveTab.Settings)" class="flex flex-col items-center p-2"
        :class="activeTab === ActiveTab.Settings ? 'text-brand-primary' : 'text-on-surface-variant'">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path
            d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
          <circle cx="12" cy="12" r="3" />
        </svg>
      </button>
    </div>

    <!-- Mobile Hamburger Menu Overlay -->
    <div v-if="mobileMenuOpen"
      class="hidden max-md:block fixed inset-0 z-[60] bg-[#131313] animate-in slide-in-from-left duration-200">
      <div class="flex flex-col h-full relative">
        <div class="h-16 flex items-center justify-between px-4 border-b border-[#2d2d2d] shrink-0">
          <h1
            class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-brand-primary to-brand-secondary tracking-tighter">
            CampusGo
          </h1>
          <button @click="mobileMenuOpen = false"
            class="p-2 text-on-surface-variant hover:text-white border-none bg-transparent">
            <X class="w-6 h-6" />
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
          <button v-for="item in mobileMenuItems" :key="item.tab" @click="setActiveTab(item.tab)"
            class="w-full flex items-center gap-4 px-4 py-3 rounded-lg text-sm font-bold transition-all border-none bg-transparent cursor-pointer"
            :class="activeTab === item.tab ? 'bg-brand-primary-container text-white' : 'text-on-surface hover:bg-[#201f1f]'">
            <component :is="item.icon" class="w-5 h-5" />
            <span>{{ item.label }}</span>
          </button>
        </div>
      </div>
    </div>
    <!-- EMERGENCY COUNTDOWN MODAL / DIRECT OVERLAY BLOCK -->
    <div v-if="sosActive" class="fixed inset-0 z-[100] flex items-center justify-center max-md:p-4 p-8 select-none">
      <div class="absolute inset-0 bg-black/95 backdrop-blur-md"></div>

      <div
        class="relative bg-[#201f1f] border border-[#ff5f52]/40 rounded-2xl max-md:p-6 p-10 max-w-[500px] w-full text-center shadow-[0_0_60px_rgba(255,95,82,0.3)] animate-in scale-in-95 duration-200">

        <div
          class="w-20 h-20 rounded-full bg-[#93000a] text-white flex items-center justify-center mx-auto mb-6 shadow-xl border border-[#ff5f52]/30">
          <Siren class="w-10 h-10 text-[#ffb4ab] animate-pulse" />
        </div>

        <div v-if="!sosDispatched">
          <h2 class="text-2xl font-black text-white uppercase tracking-tight">Emergency SOS Initializing</h2>
          <p class="text-sm text-on-surface-variant font-light mt-3 leading-relaxed max-w-sm mx-auto">
            Broadcasting status coordinates directly to <span class="text-[#ffb4ab] font-bold">University Security &
              Dispatchers</span> in...
          </p>

          <!-- Big countdown timer graphic -->
          <div class="text-7xl font-extrabold text-[#ffb4aa] tracking-tighter my-8 select-none">
            0{{ sosCountdown }}
          </div>

          <button @click="cancelSOS"
            class="w-full bg-[#353534] hover:bg-brand-surface-high border border-[#2d2d2d] py-3.5 rounded-xl text-sm font-bold text-white transition-all cursor-pointer active:scale-95 border-none">
            Cancel SOS Trigger
          </button>
        </div>

        <div v-else class="animate-in fade-in duration-300">
          <h2 class="text-2xl font-black text-[#ffb4aa] uppercase tracking-tight">SOS Dispatch Broadcasted!</h2>

          <div class="bg-[#93000a]/15 border border-[#ffb4ab]/25 p-4.5 rounded-xl my-6 text-left space-y-2.5">
            <div class="flex justify-between items-center text-xxs font-bold text-[#ffb4ab] uppercase tracking-wide">
              <span>Tracker Status</span>
              <span class="flex items-center gap-1.5 animate-pulse"><span
                  class="w-2 h-2 rounded-full bg-brand-primary"></span>Active Live Stream</span>
            </div>
            <div>
              <h4 class="text-xs font-bold text-on-surface">Security Team En Route</h4>
              <p class="text-xxs text-on-surface-variant leading-relaxed mt-1 font-light">
                Current coordinate lock matches your phone GPS beacon. Operational dispatch squad estimated response
                time is under <span class="text-[#ffb4ab] font-semibold">2 minutes</span>.
              </p>
            </div>
          </div>

          <div class="flex gap-4">
            <button
              @click="alert('Alerting campus security dispatch center directly. Opening dynamic VoIP connection.')"
              class="flex-grow bg-[#ff5f52] hover:bg-[#ff786d] text-white text-xs font-bold py-3 rounded-lg cursor-pointer border-none">
              Direct Office Call
            </button>
            <button @click="cancelSOS"
              class="flex-grow bg-[#353534] border border-[#2d2d2d] text-on-surface-variant hover:text-white text-xs font-semibold py-3 rounded-lg cursor-pointer border-none">
              Deactivate Alert
            </button>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>
