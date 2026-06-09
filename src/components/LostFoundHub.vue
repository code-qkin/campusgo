<script setup lang="ts">
import { ref, computed } from 'vue';
import { LostItem, ActiveReport } from '../types';
import { mockLostItems, mockActiveReports } from '../data';
import { 
  MapPin, 
  Clock, 
  Plus, 
  Tag, 
  X, 
  CheckCircle, 
  CornerDownRight,
  ShieldAlert
} from 'lucide-vue-next';

import { showAlert as alert } from '../alert';

const items = ref<LostItem[]>(mockLostItems);
const activeReports = ref<ActiveReport[]>(mockActiveReports);
const filterCategory = ref('All');
const searchQuery = ref('');

// Create / Report Modal State
const reportModalOpen = ref(false);
const newItemName = ref('');
const newItemCategory = ref<'Electronics' | 'Keys' | 'Bags' | 'Student IDs' | 'Others'>('Electronics');
const newItemLocation = ref('');
const newItemDescription = ref('');

// Claim Modal State
const claimItem = ref<LostItem | null>(null);
const claimDescription = ref('');
const claimedItemsState = ref<Record<string, 'idle' | 'reviewing' | 'claimed'>>({});

const handleCreateReport = () => {
  if (!newItemName.value || !newItemLocation.value) {
    alert("Please enter the missing item name and estimated location last seen.");
    return;
  }

  const createdItem: LostItem = {
    id: `item-${Date.now()}`,
    name: newItemName.value,
    category: newItemCategory.value,
    location: newItemLocation.value,
    timeAgo: "Just now",
    image: "https://lh3.googleusercontent.com/aida-public/AB6AXuA8nJ_Byroj-TMmbxfpTMwMxaFJ-Z3fXeXv7AHkKfo9qIwKG9b2lNVDWI-d5f6KZrTLHxB13Kp64yDJW4WolIPhzeBZkTf6JK2-LGdebNQyH5ERiG1wztosWulR7xj5X_AKkIBSCjy8L0kWcpdpbEpq3if10HPVBCoxt1oGOCbV-7bg3iMOcCsrNA85QNLA6gtIzwHk_XrQT5-YvISdcKvBfDkmUIaxrlCXTSbW62mB1pEUQ2hvE7Y3HQjPgaS-4wQ9Y9wgiih4d5w",
    isClaimed: false,
    description: newItemDescription.value || "No secondary description provided."
  };

  items.value.unshift(createdItem);

  // Also add to My Active Reports
  const newReport: ActiveReport = {
    id: `report-${Date.now()}`,
    name: newItemName.value,
    type: "lost",
    status: "Reviewing",
    description: newItemDescription.value,
    date: "Reported Just now"
  };
  activeReports.value.unshift(newReport);

  // Reset controls
  const prevName = newItemName.value;
  newItemName.value = '';
  newItemLocation.value = '';
  newItemDescription.value = '';
  reportModalOpen.value = false;

  alert(`Success! "${prevName}" has been officially listed in the global lost database index. Verification matches will alert you instantly.`);
};

const handleClaimSubmit = () => {
  if (!claimDescription.value.trim() || claimDescription.value.length < 15) {
    alert("Please provide a thorough description (at least 15 characters) so we can authenticate ownership prior to dispatch release.");
    return;
  }

  if (claimItem.value) {
    claimedItemsState.value[claimItem.value.id] = 'reviewing';
    alert(`Claim submitted for "${claimItem.value.name}". Our terminal dispatchers will cross-reference your identifying description statement. Status updated to: Under Review.`);
    claimItem.value = null;
    claimDescription.value = '';
  }
};

// Filter lost items based on active tags
const filteredItems = computed(() => {
  let result = items.value;
  if (filterCategory.value !== 'All') {
    result = result.filter(item => item.category === filterCategory.value);
  }
  if (searchQuery.value) {
    result = result.filter(item => 
      item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.location.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }
  return result;
});

const categories = ["All", "Electronics", "Keys", "Bags", "Student IDs", "Others"];
</script>

<template>
  <div class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] max-md:overflow-y-auto overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">
    
    <!-- Floating Action Button: Report Missing Item -->
    <button 
      @click="reportModalOpen = true"
      class="fixed bottom-10 right-[480px] max-md:right-6 max-md:bottom-24 w-14 h-14 bg-brand-primary-container hover:brightness-110 text-white rounded-full flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-300 cursor-pointer shadow-[0_8px_30px_rgba(255,95,82,0.4)] z-40 outline-none border-none group"
      title="Report Missing Item"
    >
      <Plus class="w-6 h-6 group-hover:rotate-90 transition-transform duration-300" />
    </button>

    <!-- LEFT PANEL: Search and List active lost properties -->
    <section class="flex-1 flex flex-col max-md:p-4 p-10 max-md:overflow-visible overflow-y-auto">
      
      <!-- Module actions header -->
      <div class="flex justify-between items-end mb-8 select-none">
        <div>
          <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Lost & Found Ecosystem</h2>
          <p class="text-sm text-on-surface-variant font-light">Verify, claim, and organize lost items securely inside academic property circles.</p>
        </div>
      </div>

      <!-- Category horizontal selection ribbons -->
      <div class="flex gap-2.5 mb-8 overflow-x-auto pb-1 select-none whitespace-nowrap scrollbar-none">
        <button
          v-for="cat in categories"
          :key="cat"
          @click="filterCategory = cat"
          class="px-4.5 py-2 rounded-lg border text-xs font-bold transition-all cursor-pointer"
          :class="filterCategory === cat 
            ? 'bg-brand-primary/10 border-brand-primary text-brand-primary shadow-sm'
            : 'bg-[#201f1f] border-[#222] text-on-surface-variant hover:border-[#353534] hover:text-[#fff]'"
        >
          {{ cat }}
        </button>
      </div>

      <!-- Global items display list card grid -->
      <div v-if="filteredItems.length === 0" class="bg-[#201f1f] border border-dashed border-[#2d2d2d] rounded-xl p-12 text-center select-none">
        <Tag class="w-10 h-10 text-on-surface-variant/50 mx-auto mb-3" />
        <p class="text-sm text-on-surface font-semibold">No missing items reported in this category.</p>
        <p class="text-xxs text-on-surface-variant mt-1.5 max-w-xs mx-auto font-light">Be the first to create a report index by clicking "Report Missing Item" above.</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pb-12">
        <div 
          v-for="item in filteredItems" 
          :key="item.id"
          class="bg-[#1e1e1e]/60 border border-[#2d2d2d] rounded-xl overflow-hidden flex flex-col hover:border-[#353534] transition-all group"
        >
          <!-- Photo container header -->
          <div class="h-44 bg-[#131313] relative overflow-hidden flex items-center justify-center">
            <img 
              :alt="item.name" 
              class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500" 
              :src="item.image" 
            />
            <span class="absolute top-3 left-3 bg-[#131313]/90 backdrop-blur border border-[#2d2d2d] text-xxs font-bold px-2 py-1 rounded text-brand-secondary select-none">
              {{ item.category }}
            </span>
          </div>

          <!-- Body elements block -->
          <div class="p-5 flex-1 flex flex-col">
            <h3 class="text-base font-bold text-on-surface">{{ item.name }}</h3>
            <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed font-light flex-1">
              {{ item.description }}
            </p>

            <div class="space-y-2 mt-5 pt-4 border-t border-[#353534]/50 text-xxs font-semibold text-on-surface-variant uppercase tracking-wider">
              <div class="flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5" />
                <span>Last Seen:</span>
                <span class="text-on-surface font-bold ml-auto">{{ item.location }}</span>
              </div>
              <div class="flex items-center gap-1.5">
                <Clock class="w-3.5 h-3.5" />
                <span>Reported:</span>
                <span class="text-on-surface font-bold ml-auto">{{ item.timeAgo }}</span>
              </div>
            </div>

            <!-- Claim Button action trigger -->
            <div class="mt-5">
              <div v-if="claimedItemsState[item.id] === 'reviewing'" class="w-full py-2.5 rounded-lg border border-brand-secondary/30 bg-brand-secondary/15 text-brand-secondary font-bold text-xs text-center select-none flex items-center justify-center gap-1.5 uppercase tracking-wide">
                <Clock class="w-4 h-4 animate-spin" />
                <span>Claim Under Review</span>
              </div>
              <button 
                v-else
                @click="claimItem = item"
                class="w-full bg-[#ff5f52] hover:bg-[#ff786d] text-white font-bold text-xs py-2.5 rounded-lg transition-all cursor-pointer active:scale-97 select-none border-none outline-none"
              >
                Claim Item
              </button>
            </div>

          </div>
        </div>
      </div>

    </section>

    <!-- RIGHT DRAWER: Form to report new lost items -->
    <aside class="max-md:w-full w-[450px] border-l border-[#2d2d2d] bg-[#1c1b1b] p-6 flex flex-col max-md:h-auto h-full shrink-0 select-none">
      <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-4">My Reports Logs</h3>
      
      <div class="space-y-4 flex-1 max-md:overflow-visible overflow-y-auto">
        <div v-for="report in activeReports" :key="report.id" class="p-4 rounded-xl bg-[#131313] border border-[#2d2d2d] space-y-3">
          <div class="flex justify-between items-start">
            <span 
              class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider"
              :class="report.type === 'lost' ? 'bg-brand-primary-container text-white' : 'bg-brand-secondary/15 text-brand-secondary border border-brand-secondary/30'"
            >
              {{ report.type }}
            </span>
            
            <div class="flex items-center gap-1 text-[10px] text-on-surface font-bold">
              <Clock v-if="report.status === 'Reviewing'" class="w-3.5 h-3.5 text-brand-primary" />
              <CheckCircle v-else class="w-3.5 h-3.5 text-brand-tertiary" />
              <span :class="report.status === 'Reviewing' ? 'text-brand-primary' : 'text-brand-tertiary'">
                {{ report.status }}
              </span>
            </div>
          </div>

          <div>
            <h4 class="text-xs font-bold text-on-surface">{{ report.name }}</h4>
            <p class="text-xxs text-on-surface-variant font-light leading-relaxed mt-1">{{ report.description }}</p>
            <div class="flex items-center gap-1.5 text-xxs text-on-surface-variant/60 font-semibold mt-3">
              <CornerDownRight class="w-3.5 h-3.5 text-on-surface-variant/45" />
              <span>{{ report.date }}</span>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- MODAL 1: Submit Report Missing item overlay -->
    <div v-if="reportModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-8">
      <div class="absolute inset-0 bg-black/85 backdrop-blur-xs" @click="reportModalOpen = false"></div>
      <div class="relative bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-8 max-w-[480px] w-full shadow-2xl animate-in zoom-in-95 duration-200">
        
        <header class="flex justify-between items-center mb-6">
          <h2 class="text-lg font-bold text-on-surface">Report Lost Property</h2>
          <button 
            @click="reportModalOpen = false"
            class="w-8 h-8 rounded-full hover:bg-[#2a2a2a]/45 flex items-center justify-center text-on-surface-variant hover:text-white transition-colors cursor-pointer border-none bg-transparent"
          >
            <X class="w-5 h-5" />
          </button>
        </header>

        <form @submit.prevent="handleCreateReport" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Item Name</label>
            <input 
              type="text" 
              v-model="newItemName"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
              placeholder="e.g., Red Leather Wallet"
              required
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Category</label>
            <select 
              v-model="newItemCategory"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
            >
              <option value="Electronics">Electronics</option>
              <option value="Keys">Keys</option>
              <option value="Bags">Bags</option>
              <option value="Student IDs">Student IDs</option>
              <option value="Others">Others</option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Estimated Location Last Seen</label>
            <input 
              type="text" 
              v-model="newItemLocation"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
              placeholder="e.g., Science Library Bench Block A"
              required
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Identifying Description</label>
            <textarea 
              v-model="newItemDescription"
              rows="3"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
              placeholder="Describe unique identifiers, stickers, keys inside, colors..."
            />
          </div>

          <button 
            type="submit"
            class="w-full py-3 bg-brand-primary-container hover:brightness-110 text-white font-bold text-xs rounded-lg transition-all cursor-pointer shadow-lg mt-4 uppercase tracking-wider border-none"
          >
            File Database Report
          </button>
        </form>

      </div>
    </div>

    <!-- MODAL 2: Authenticate ownership Claim modal -->
    <div v-if="claimItem !== null" class="fixed inset-0 z-50 flex items-center justify-center p-8">
      <div class="absolute inset-0 bg-black/85 backdrop-blur-xs" @click="claimItem = null"></div>
      <div class="relative bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl p-8 max-w-[480px] w-full shadow-2xl animate-in zoom-in-95 duration-200">
        
        <header class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-bold text-on-surface">Submit Ownership Claim</h2>
          <button 
            @click="claimItem = null"
            class="w-8 h-8 rounded-full hover:bg-[#2a2a2a]/45 flex items-center justify-center text-on-surface-variant hover:text-white transition-colors cursor-pointer border-none bg-transparent"
          >
            <X class="w-5 h-5" />
          </button>
        </header>

        <div class="bg-[#ffb4ab]/8 px-4 py-3 rounded-lg border border-brand-error/25 text-xxs font-medium text-brand-error flex items-start gap-2 mb-6">
          <ShieldAlert class="w-4 h-4 shrink-0 mt-0.5" />
          <p class="leading-relaxed">
            To prevent fraud, claims require a descriptive explanation of content identifiers (e.g. keychains, tags, wallpapers) prior to property inspection matches.
          </p>
        </div>

        <form @submit.prevent="handleClaimSubmit" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Item Selected</label>
            <input 
              type="text" 
              :value="claimItem.name"
              class="w-full bg-[#131313] border border-[#353534] rounded px-3 py-2 text-xs text-on-surface-variant/65 cursor-not-allowed uppercase font-extrabold"
              disabled 
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Describe unique item contents / marks</label>
            <textarea 
              v-model="claimDescription"
              rows="4"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
              placeholder="e.g., Left pocket contains matching yellow charger, backing decal says 'West Loop'..."
              required
            />
            <span class="text-xxs text-on-surface-variant/70 font-semibold block text-right mt-1">Minimum 15 characters required</span>
          </div>

          <button 
            type="submit"
            class="w-full py-3 bg-brand-primary-container hover:brightness-110 text-white font-bold text-xs rounded-lg transition-all cursor-pointer shadow-lg uppercase tracking-wider border-none"
          >
            File Owner Verification
          </button>
        </form>

      </div>
    </div>

  </div>
</template>
