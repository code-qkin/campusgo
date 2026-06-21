<script setup lang="ts">
import { ref } from 'vue';
import { Buddy, TrustedContact } from '../types';
import { mockBuddies, mockTrustedContacts } from '../data';
import { 
  ShieldCheck, 
  Siren, 
  UserPlus, 
  Star, 
  Compass
} from 'lucide-vue-next';

import { showAlert as alert } from '../alert';
import { customPrompt } from '../modal';

const buddies = ref<Buddy[]>(mockBuddies);
const contacts = ref<TrustedContact[]>(mockTrustedContacts);
const escortState = ref<Record<string, 'idle' | 'requesting' | 'accepted'>>({});
const selectedBuddy = ref<Buddy | null>(mockBuddies[0]);

const slideProgress = ref(0);
const sosActivated = ref(false);

const handleSlideChange = (e: Event) => {
  const val = parseInt((e.target as HTMLInputElement).value);
  slideProgress.value = val;
  if (val === 100) {
    sosActivated.value = true;
    alert("EMERGENCY SIGNAL BROADCASTED: University safety dispatchers and your 2 Trusted Contacts have been notified with your current GPS coordinates. Hold tight, a security officer is en route.");
  }
};

const resetSOS = () => {
  slideProgress.value = 0;
  sosActivated.value = false;
};

const handleRequestEscort = (buddyId: string) => {
  escortState.value[buddyId] = 'requesting';
  setTimeout(() => {
    escortState.value[buddyId] = 'accepted';
    alert(`Companion companion secured!
Request Accepted by Walker.
Estimated Arrival: 4 minutes.`);
  }, 1800);
};

const handleAddContact = async () => {
  const name = await customPrompt('Add Emergency Contact', 'Enter contact name:');
  if (!name) return;
  const relation = await customPrompt('Add Emergency Contact', 'Enter relationship (e.g. Spouse, Parent, Roommate):') || "Friend";

  const newContact: TrustedContact = {
    id: `contact-${Date.now()}`,
    name,
    relationship: relation,
    avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuCfVWdxa2fT9NyInAQJX4yPYvTFa5_N_7U2Rbm0oQYyS8epqd9CrNxRtrrtU4T9hlDo-vSJhiL8H5t50aewyG4I-gQbYuCvDO-zFPWTTymLef6qFeKjNYhPBCA9jjLhB57-5rliYxbmy-t0gUVrV9y1BynqE4titEJsgd4iwEAF-R1hbcRA2uFNnPWqaiJSjLjDgNeIrcvb5Y1dF1f0T_i50HwAQfpMx4xam8wcnyLLBajD31PIRm-3XFv5eyhbJK6j2iYsy5nnAKI"
  };
  contacts.value.push(newContact);
  alert(`${name} added successfully as a Trusted Emergency Contact.`);
};
</script>

<template>
  <div class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">
    
    <!-- LEFT/SIDE PANEL: Escorts Hub controllers -->
    <aside class="max-md:w-full max-md:h-[50vh] max-md:order-2 w-[450px] bg-[#0e0e0e]/95 backdrop-blur-xl border-r border-[#2d2d2d] z-20 flex flex-col shadow-2xl overflow-y-auto">
      
      <!-- Section header -->
      <header class="p-6 border-b border-[#2d2d2d] select-none">
        <h2 class="text-xl font-bold text-on-surface tracking-tight">SafeWalk Escorts</h2>
        <p class="text-xs text-on-surface-variant leading-relaxed mt-1 font-light">
          Request an accredited student buddy or staff operator to accompany you home or walk between campus buildings.
        </p>
      </header>

      <!-- DRAG-SLIDER TRIGGER EMERGENCY ACTION -->
      <div class="p-6 border-b border-[#2d2d2d] bg-[#ffb4ab]/5">
        <div class="flex justify-between items-center mb-3">
          <span class="text-xs font-bold text-[#ffb4ab] flex items-center gap-2 uppercase tracking-wider select-none">
            <Siren class="w-4 h-4 text-brand-error animate-pulse" />
            Silent Dispatch Alarm
          </span>
          <button v-if="sosActivated" @click="resetSOS" class="text-xxs text-brand-primary border border-brand-primary px-2 py-0.5 rounded uppercase font-bold bg-transparent cursor-pointer">Reset SOS</button>
        </div>

        <div class="relative w-full h-14 bg-brand-error-container/20 border border-brand-error/25 rounded-2xl flex items-center justify-center overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-brand-error-container/30 to-transparent pointer-events-none z-0"></div>
          
          <!-- Background flashing alert label -->
          <span class="text-xs font-bold text-[#ffb4ab] relative z-10 select-none pointer-events-none opacity-85 uppercase tracking-widest text-center">
            {{ sosActivated ? "⚠️ HIGH SOS ACTIVE" : "Slide to Notify Security Dispatch" }}
          </span>

          <!-- Simulated Slider control interface input -->
          <input 
            type="range" 
            min="0" 
            max="100" 
            v-model="slideProgress"
            :disabled="sosActivated"
            @input="handleSlideChange"
            class="absolute inset-y-0 left-0 w-full h-full opacity-80 cursor-grab active:cursor-grabbing accent-brand-primary-container z-20 range-slider"
            style="background: transparent; -webkit-appearance: none"
          />
        </div>
      </div>

      <!-- BUDDIES LIST FEED -->
      <div class="p-6 space-y-6">
        <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest">Available Buddies</h3>

        <div class="space-y-4">
          <div 
            v-for="buddy in buddies" 
            :key="buddy.id"
            @click="selectedBuddy = buddy"
            class="p-4 border rounded-xl hover:bg-[#1e1e1e] transition-all cursor-pointer"
            :class="selectedBuddy?.id === buddy.id 
              ? 'bg-[#201f1f] border-brand-primary shadow-sm' 
              : 'bg-[#1e1e1e]/40 border-[#2d2d2d]'"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-full overflow-hidden border border-[#2d2d2d] shrink-0 relative">
                  <img :alt="buddy.name" class="w-full h-full object-cover" :src="buddy.avatar" />
                  <span v-if="buddy.isVerified" class="absolute bottom-0 right-0 w-4 h-4 bg-brand-tertiary border-2 border-[#131313] rounded-full flex items-center justify-center" title="Gold SafeWalk Verified">
                    <ShieldCheck class="w-2.5 h-2.5 text-[#131313] fill-[#131313]" />
                  </span>
                </div>
                <div>
                  <div class="flex items-center gap-1.5">
                    <h4 class="text-sm font-bold text-on-surface">{{ buddy.name }}</h4>
                    <span class="text-xxs px-1.5 py-0.5 bg-[#353534] text-on-surface-variant rounded font-semibold">{{ buddy.distance }}</span>
                  </div>
                  <p class="text-xs text-on-surface-variant font-medium opacity-80 mt-0.5">{{ buddy.major }}</p>
                </div>
              </div>

              <div class="flex items-center text-brand-secondary text-xs font-bold gap-0.5">
                <Star class="w-3.5 h-3.5 fill-brand-secondary text-brand-secondary" />
                <span>{{ buddy.rating }}</span>
              </div>
            </div>

            <!-- Escort action submission buttons -->
            <div class="mt-4 flex justify-end gap-2">
              <button 
                @click.stop="alert(`Opening secure audio link call connection with ${buddy.name}...`)"
                class="px-3.5 py-1.5 rounded-lg border border-[#2d2d2d] text-xs font-bold hover:bg-[#201f1f] cursor-pointer bg-transparent text-on-surface"
              >
                Call
              </button>
              <button
                @click.stop="handleRequestEscort(buddy.id)"
                :disabled="escortState[buddy.id] === 'requesting' || escortState[buddy.id] === 'accepted'"
                class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer border-none"
                :class="escortState[buddy.id] === 'accepted'
                  ? 'bg-brand-tertiary text-black'
                  : escortState[buddy.id] === 'requesting'
                    ? 'bg-[#353534] text-on-surface-variant cursor-not-allowed'
                    : 'bg-[#ff5f52] text-white hover:brightness-110'"
              >
                {{ escortState[buddy.id] === 'accepted' 
                  ? "Headed to you!" 
                  : escortState[buddy.id] === 'requesting'
                    ? "Sending Companion Request..." 
                    : "Request Walk"
                }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TRUSTED EMERGENCY CONTACTS AVATARS SECTION -->
      <div class="p-6 border-t border-[#2d2d2d] space-y-4">
        <div class="flex justify-between items-center">
          <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest">Trusted Contacts</h3>
          <button 
            @click="handleAddContact"
            class="text-xxs text-brand-primary hover:underline font-bold flex items-center gap-1 cursor-pointer bg-transparent border-none p-1"
          >
            <UserPlus class="w-3 h-3" />
            Add Contact
          </button>
        </div>

        <div class="flex flex-col gap-3">
          <div v-for="contact in contacts" :key="contact.id" class="flex justify-between items-center bg-[#131313] p-3 rounded-lg border border-[#2d2d2d]">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full overflow-hidden border border-[#2d2d2d] shrink-0">
                <img :alt="contact.name" class="w-full h-full object-cover" :src="contact.avatar" />
              </div>
              <div>
                <h4 class="text-xs font-bold text-on-surface">{{ contact.name }}</h4>
                <p class="text-xxs text-on-surface-variant mt-0.5">{{ contact.relationship }}</p>
              </div>
            </div>

            <div class="flex items-center gap-2 select-none">
              <span class="w-1.5 h-1.5 bg-brand-tertiary rounded-full animate-pulse"></span>
              <span class="text-xxs text-on-surface-variant font-bold uppercase tracking-wider">SMS Linked</span>
            </div>
          </div>
        </div>
      </div>

    </aside>

    <!-- RIGHT CANVAS: Interactive concentric Buddy Radar overlay display block -->
    <div class="flex-1 bg-[#0a0a0a] relative overflow-hidden select-none max-md:order-1 max-md:h-[50vh] max-md:shrink-0">
      
      <!-- Grid backgrounds layout centering radar rings -->
      <div class="absolute inset-x-0 inset-y-0 flex items-center justify-center">
        
        <!-- Concentric radar circle guides -->
        <div class="w-[500px] h-[500px] border border-dashed border-[#2d2d2d] rounded-full relative flex items-center justify-center">
          
          <div class="w-[350px] h-[350px] border border-brand-primary/10 rounded-full flex items-center justify-center relative">
            
            <div class="w-[200px] h-[200px] border border-brand-tertiary/10 rounded-full flex items-center justify-center relative">
              
              <!-- Center compass node of current user -->
              <div class="relative z-10">
                <span class="absolute -inset-6 bg-brand-primary/15 rounded-full blur-xl animate-pulse"></span>
                <div class="w-11 h-11 rounded-full bg-brand-primary border-4 border-black flex items-center justify-center relative z-11">
                  <Compass class="w-5 h-5 text-[#131313] animate-spin-slow" />
                </div>
              </div>

            </div>

          </div>

          <!-- Buddy avatar markers located strategically on map coordinates ring elements -->
          <button 
            v-for="b in buddies" 
            :key="b.id"
            @click="selectedBuddy = b"
            class="absolute p-0.5 rounded-full bg-black border border-[#2d2d2d] z-10 hover:border-brand-primary hover:scale-105 transition-all cursor-pointer"
            :style="{
              left: `${b.position.x}%`,
              top: `${b.position.y}%`
            }"
          >
            <div class="relative">
              <div class="w-10 h-10 rounded-full overflow-hidden border border-[#2d2d2d]">
                <img :alt="b.name" class="w-full h-full object-cover" :src="b.avatar" />
              </div>
              <!-- Status pulsing ring indicator -->
              <span 
                class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-black flex items-center justify-center cursor-pointer"
                :class="selectedBuddy?.id === b.id 
                  ? 'bg-brand-primary' 
                  : b.status === 'available'
                    ? 'bg-brand-tertiary'
                    : 'bg-brand-error'"
              ></span>
            </div>
          </button>

        </div>

      </div>

      <!-- Bottom map information legend context -->
      <div class="absolute bottom-8 right-8 bg-[#201f1f]/85 border border-[#2d2d2d] p-3 rounded-lg backdrop-blur text-xxs flex gap-4 select-none">
        <div class="flex items-center gap-1.5 font-semibold text-on-surface">
          <span class="w-2 h-2 rounded-full bg-brand-tertiary"></span>
          <span>Available Partner</span>
        </div>
        <div class="flex items-center gap-1.5 font-semibold text-on-surface">
          <span class="w-2 h-2 rounded-full bg-brand-error"></span>
          <span>Active Walking Duty</span>
        </div>
        <div class="flex items-center gap-1.5 font-semibold text-on-surface">
          <span class="w-2.5 h-2.5 bg-brand-primary rounded-full"></span>
          <span>Currently Selected</span>
        </div>
      </div>

      <!-- Selected Buddy info panel floating inside canvas layout -->
      <div v-if="selectedBuddy" class="absolute top-8 right-8 w-64 bg-[#1c1b1b] border border-[#2d2d2d] p-4 rounded-xl shadow-2xl animate-in fade-in duration-300">
        <div class="flex gap-3 items-center">
          <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-[#2d2d2d]">
            <img :alt="selectedBuddy.name" class="w-full h-full object-cover" :src="selectedBuddy.avatar" />
          </div>
          <div class="min-w-0 flex-1">
            <h4 class="text-xs font-bold text-on-surface truncate">{{ selectedBuddy.name }}</h4>
            <p class="text-xxs text-on-surface-variant font-light truncate mt-0.5">{{ selectedBuddy.major }}</p>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-[#2d2d2d] text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
          <div>
            <span class="block font-medium opacity-60">Status</span>
            <span class="block font-bold mt-0.5" :class="selectedBuddy.status === 'available' ? 'text-brand-tertiary' : 'text-brand-error'">
              {{ selectedBuddy.status === 'available' ? 'Available' : 'In Duty Walk' }}
            </span>
          </div>
          <div>
            <span class="block font-medium opacity-60">Accredited Partner</span>
            <span class="block text-brand-secondary font-bold mt-0.5 flex items-center gap-1">
              <ShieldCheck class="w-3.5 h-3.5 text-brand-secondary fill-brand-secondary/15" />
              Gold Class
            </span>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>
