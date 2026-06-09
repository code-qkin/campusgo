<script setup lang="ts">
import { ref, computed } from 'vue';
import { CarpoolRide } from '../types';
import { mockCarpoolRides } from '../data';
import { 
  MapPin, 
  Flag, 
  Clock, 
  Star, 
  X, 
  MessageSquare, 
  Music, 
  Briefcase, 
  Slash, 
  ShieldAlert,
  Send
} from 'lucide-vue-next';

import { showAlert as alert } from '../alert';

const rides = ref<CarpoolRide[]>(mockCarpoolRides);
const selectedRide = ref<CarpoolRide | null>(mockCarpoolRides[0]);
const filterTag = ref<string | null>('all');
const requestStatus = ref<Record<string, 'idle' | 'requested' | 'approved'>>({});
const chatOpen = ref(false);
const chatMessage = ref('');
const chatLogs = ref<Array<{ sender: 'user' | 'driver'; text: string }>>([
  { sender: 'driver', text: "Hey! Let me know if you have any questions about luggage or pickup." }
]);

const handleFilterClick = (tag: string) => {
  filterTag.value = filterTag.value === tag ? 'all' : tag;
};

const handleCardClick = (ride: CarpoolRide) => {
  selectedRide.value = ride;
  chatOpen.value = false; // Reset messaging panel
};

const handleRequestSeat = (rideId: string) => {
  requestStatus.value[rideId] = 'requested';
  setTimeout(() => {
    requestStatus.value[rideId] = 'approved';
    alert("Success! Carpool ride requested has been approved by the driver companion. Seat details synced with your history log.");
  }, 1800);
};

const handleSendChat = () => {
  if (!chatMessage.value.trim()) return;
  const userMsg = chatMessage.value;
  chatLogs.value.push({ sender: 'user', text: userMsg });
  chatMessage.value = '';

  setTimeout(() => {
    chatLogs.value.push({ 
      sender: 'driver', 
      text: `Awesome. Sounds great! I'll see you at the pickup location around scheduled departure.` 
    });
  }, 1200);
};

// Filter rides based on category tags
const filteredRides = computed(() => {
  const currentFilter = filterTag.value;
  if (!currentFilter || currentFilter === 'all') return rides.value;
  if (currentFilter === 'groceries') {
    return rides.value.filter(r => r.to.toLowerCase().includes('foods') || r.to.toLowerCase().includes('target'));
  }
  if (currentFilter === 'airport') {
    return rides.value.filter(r => r.to.toLowerCase().includes('terminal') || r.to.toLowerCase().includes('airport'));
  }
  if (currentFilter === 'night') {
    return rides.value.filter(r => r.departureTime.toLowerCase().includes('6:') || r.departureTime.toLowerCase().includes('9:'));
  }
  return rides.value;
});
</script>

<template>
  <div class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">
    
    <!-- LEFT/CENTER AREA: Available Rides Listing -->
    <section class="flex-1 max-md:p-4 p-10 overflow-y-auto">
      <div class="flex justify-between items-end mb-8 select-none">
        <div>
          <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Available Rides</h2>
          <p class="text-sm text-on-surface-variant font-light">Find a comfortable, secure carpool companion to your destination.</p>
        </div>
        
        <!-- Quick Filter chips list -->
        <div class="flex gap-3">
          <button 
            @click="handleFilterClick('groceries')"
            class="px-4 py-2 rounded-full border text-xs font-bold transition-all cursor-pointer"
            :class="filterTag === 'groceries' 
              ? 'bg-brand-primary/10 border-brand-primary text-brand-primary shadow-lg' 
              : 'bg-transparent border-[#2d2d2d] text-on-surface-variant hover:border-on-surface hover:text-[#fff]'"
          >
            🛒 Groceries
          </button>
          <button 
            @click="handleFilterClick('airport')"
            class="px-4 py-2 rounded-full border text-xs font-bold transition-all cursor-pointer"
            :class="filterTag === 'airport' 
              ? 'bg-brand-primary/10 border-brand-primary text-brand-primary shadow-lg' 
              : 'bg-transparent border-[#2d2d2d] text-on-surface-variant hover:border-on-surface hover:text-[#fff]'"
          >
            ✈️ Airport
          </button>
          <button 
            @click="handleFilterClick('night')"
            class="px-4 py-2 rounded-full border text-xs font-bold transition-all cursor-pointer"
            :class="filterTag === 'night' 
              ? 'bg-brand-primary/10 border-brand-primary text-brand-primary shadow-lg' 
              : 'bg-transparent border-[#2d2d2d] text-on-surface-variant hover:border-on-surface hover:text-[#fff]'"
          >
            🌙 Late Night
          </button>
        </div>
      </div>

      <!-- Dynamic Rides Cards grid split -->
      <div v-if="filteredRides.length === 0" class="bg-[#201f1f] border border-dashed border-[#2d2d2d] rounded-xl p-10 text-center select-none">
        <ShieldAlert class="w-10 h-10 text-brand-primary/70 mx-auto mb-3" />
        <p class="text-sm text-on-surface font-semibold">No rides currently match this filter tag.</p>
        <button @click="filterTag = 'all'" class="mt-3 text-xs text-brand-primary underline hover:text-[#fff] font-bold bg-transparent border-none cursor-pointer">Clear Filters</button>
      </div>

      <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-6 pb-12">
        <div 
          v-for="ride in filteredRides"
          :key="ride.id"
          @click="handleCardClick(ride)"
          class="relative overflow-hidden group border rounded-xl p-6 hover:bg-[#201f1f] transition-all duration-200 cursor-pointer"
          :class="selectedRide?.id === ride.id 
            ? 'bg-[#201f1f] border-brand-primary shadow-[0_0_30px_rgba(255,180,170,0.05)]' 
            : 'bg-[#1e1e1e]/60 border-[#2d2d2d]'"
        >
          <div class="absolute top-0 right-0 w-32 h-32 bg-brand-primary/5 rounded-bl-full -mr-8 -mt-8 pointer-events-none transition-transform group-hover:scale-105 duration-350"></div>
          
          <div class="flex justify-between items-start mb-6 relative z-10">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-full overflow-hidden border border-[#2d2d2d] shrink-0">
                <img 
                  :alt="ride.driver.name" 
                  class="w-full h-full object-cover" 
                  :src="ride.driver.avatar" 
                />
              </div>
              <div>
                <h3 class="text-base font-bold text-on-surface">{{ ride.driver.name }}</h3>
                <div class="flex items-center text-brand-tertiary text-xs gap-1 font-bold mt-0.5">
                  <Star class="w-3.5 h-3.5 fill-brand-tertiary text-brand-tertiary animate-pulse" />
                  <span>{{ ride.driver.rating }} ({{ ride.driver.ridesCount }} rides)</span>
                </div>
              </div>
            </div>

            <div class="text-right">
              <div class="text-xl font-bold text-brand-primary">
                {{ ride.price === 0 ? "Free" : `$${ride.price}` }}
              </div>
              <p class="text-xxs text-on-surface-variant font-medium opacity-75 mt-0.5">per seat</p>
            </div>
          </div>

          <!-- Route Timeline description points -->
          <div class="space-y-4 mb-6 relative z-10">
            <div class="flex items-start gap-3">
              <MapPin class="w-5 h-5 text-on-surface-variant shrink-0 mt-0.5" />
              <div>
                <div class="text-xxs font-semibold text-on-surface-variant uppercase tracking-wider">From</div>
                <p class="text-sm font-semibold text-on-surface">{{ ride.from }}</p>
              </div>
            </div>
            <div class="flex items-start gap-4">
              <Flag class="w-5 h-5 text-brand-primary shrink-0 mt-0.5" />
              <div>
                <div class="text-xxs font-semibold text-on-surface-variant uppercase tracking-wider">To</div>
                <p class="text-sm font-semibold text-on-surface">{{ ride.to }}</p>
              </div>
            </div>
          </div>

          <!-- Footer metadata alignment -->
          <div class="flex items-center justify-between border-t border-[#353534]/50 pt-4 relative z-10">
            <div class="flex items-center gap-1.5 text-on-surface-variant text-xs font-semibold">
              <Clock class="w-4 h-4 text-on-surface-variant" />
              <span>{{ ride.departureTime }}</span>
            </div>

            <!-- Seats availability graphic indicator circles -->
            <div class="flex gap-1">
              <div 
                v-for="(_, i) in Array.from({ length: ride.seatsTotal })" 
                :key="i"
                class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold"
                :class="i < (ride.seatsTotal - ride.seatsAvailable) 
                  ? 'bg-brand-primary text-[#131313] font-black' 
                  : 'border border-[#2d2d2d] border-dashed text-on-surface-variant/70'"
              >
                {{ i < (ride.seatsTotal - ride.seatsAvailable) ? "P" : i + 1 }}
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- RIGHT SIDE DRAWER: Selected Carpool Detail panel -->
    <aside v-if="selectedRide" class="max-md:absolute max-md:inset-0 max-md:w-full w-[450px] border-l border-[#2d2d2d] bg-[#1c1b1b] flex flex-col h-full shrink-0 relative z-30 shadow-2xl animate-in slide-in-from-right duration-200">
      <!-- Header -->
      <div class="p-6 border-b border-[#2d2d2d] flex justify-between items-center shrink-0">
        <h2 class="text-lg font-bold text-on-surface tracking-wide">Ride Details</h2>
        <button 
          @click="selectedRide = null"
          class="w-8 h-8 rounded-full hover:bg-[#2a2a2a]/45 flex items-center justify-center text-on-surface-variant hover:text-white transition-colors cursor-pointer border-none"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-6 space-y-7">
        
        <!-- Driver Profile Summary Block Card -->
        <div class="flex justify-between items-center bg-[#131313] p-4 rounded-xl border border-[#222]">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full overflow-hidden border border-[#2d2d2d] shrink-0">
              <img 
                :alt="selectedRide.driver.name" 
                class="w-full h-full object-cover" 
                :src="selectedRide.driver.avatar" 
              />
            </div>
            <div>
              <h3 class="text-base font-bold text-on-surface">{{ selectedRide.driver.name }}</h3>
              <p class="text-xs text-on-surface-variant font-light">{{ selectedRide.driver.carModel }} ({{ selectedRide.driver.carColor }})</p>
              <div class="flex items-center text-brand-tertiary text-xs font-bold gap-1 mt-1">
                <Star class="w-3.5 h-3.5 fill-brand-tertiary text-brand-tertiary" />
                <span>{{ selectedRide.driver.rating }} Rating • {{ selectedRide.driver.ridesCount }} rides</span>
              </div>
            </div>
          </div>

          <button 
            @click="chatOpen = !chatOpen"
            class="w-10 h-10 rounded-full border border-[#2d2d2d] flex items-center justify-center transition-all cursor-pointer hover:bg-[#201f1f]"
            :class="chatOpen ? 'bg-brand-primary-container border-brand-primary text-white' : 'text-on-surface hover:text-[#fff] bg-transparent'"
            title="Message Companion"
          >
            <MessageSquare class="w-5 h-5" />
          </button>
        </div>

        <!-- MESSAGE CHAT BOX SLIDE-IN (Interactive Micro-feature!) -->
        <div v-if="chatOpen" class="bg-[#131313] border border-[#2d2d2d] rounded-xl overflow-hidden flex flex-col h-48 animate-in slide-in-from-top-3 duration-200">
          <div class="bg-[#201f1f] px-4 py-2 border-b border-[#2d2d2d] flex justify-between items-center select-none">
            <span class="text-xxs font-bold text-on-surface-variant uppercase tracking-wider">Direct Message with {{ selectedRide.driver.name }}</span>
            <button @click="chatOpen = false" class="text-xxs hover:underline text-brand-primary font-bold bg-transparent border-none cursor-pointer">Collapse</button>
          </div>
          
          <!-- Messages stream log -->
          <div class="flex-1 overflow-y-auto p-3.5 space-y-2.5">
            <div v-for="(log, i) in chatLogs" :key="i" class="flex" :class="log.sender === 'user' ? 'justify-end' : 'justify-start'">
              <div 
                class="max-w-[75%] px-3 py-1.5 rounded-lg text-xs font-medium leading-relaxed"
                :class="log.sender === 'user' 
                  ? 'bg-brand-primary-container text-white rounded-br-none' 
                  : 'bg-[#201f1f] border border-[#2d2d2d] text-on-surface rounded-bl-none'"
              >
                {{ log.text }}
              </div>
            </div>
          </div>

          <!-- Input form -->
          <form @submit.prevent="handleSendChat" class="border-t border-[#2d2d2d]/60 flex items-center p-1.5 gap-1.5 bg-[#201f1f]">
            <input 
              type="text" 
              v-model="chatMessage"
              class="flex-1 bg-[#131313] border border-[#222] rounded px-3 py-1 text-xs text-on-surface focus:outline-none" 
              placeholder="Ask about bags, pickup spot..."
            />
            <button type="submit" class="w-8 h-8 rounded bg-brand-primary-container text-white flex items-center justify-center cursor-pointer border-none">
              <Send class="w-4 h-4 text-white" />
            </button>
          </form>
        </div>

        <div v-else class="rounded-xl overflow-hidden border border-[#2d2d2d] h-44 relative bg-[#201f1f]/60 select-none">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(111,251,190,0.02)_0%,transparent_70%)] pointer-events-none"></div>
          <div class="absolute top-1/2 left-1/4 w-3 h-3 bg-on-surface rounded-full shadow-[0_0_10px_rgba(225,225,225,0.4)] transform -translate-x-1/2 -translate-y-1/2 z-10"></div>
          <div class="absolute top-1/3 left-3/4 w-4 h-4 bg-[#ff5f52] rounded-full shadow-[0_0_15px_rgba(255,180,170,0.6)] transform -translate-x-1/2 -translate-y-1/2 z-10 flex items-center justify-center">
            <div class="w-1.5 h-1.5 bg-[#131313] rounded-full"></div>
          </div>
          <!-- Simulated connection line overlay -->
          <svg class="absolute inset-0 w-full h-full stroke-brand-primary/40 stroke-2 fill-none z-0">
            <path d="M100,105 C150,150 200,50 310,66" strokeDasharray="6,6" strokeWidth="3" />
          </svg>
          <div class="absolute bottom-2 right-2 bg-black/75 px-2 py-1 rounded text-[9px] text-on-surface-variant border border-[#2d2d2d]">Map Data © CampusGo</div>
        </div>

        <!-- Trip timeline locations -->
        <div class="space-y-6 relative border-l border-[#2d2d2d] pl-5 ml-2">
          <div class="relative">
            <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full border-2 border-on-surface bg-[#1c1b1b] flex items-center justify-center">
              <div class="w-1.5 h-1.5 rounded-full bg-on-surface-variant"></div>
            </div>
            <div>
              <div class="text-xxs font-semibold text-on-surface-variant uppercase tracking-wider">Pickup • 5:30 PM</div>
              <h4 class="text-sm font-bold text-on-surface mt-0.5">{{ selectedRide.from }}</h4>
              <p class="text-xs text-on-surface-variant font-light mt-1">Main entrance loading zone. Look for the blue Civic.</p>
            </div>
          </div>

          <div class="relative">
            <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full border-2 border-brand-primary bg-[#1c1b1b] flex items-center justify-center">
              <div class="w-1.5 h-1.5 rounded-full bg-brand-primary"></div>
            </div>
            <div>
              <div class="text-xxs font-semibold text-on-surface-variant uppercase tracking-wider">Dropoff • ~5:50 PM</div>
              <h4 class="text-sm font-bold text-on-surface mt-0.5">{{ selectedRide.to }}</h4>
              <p class="text-xs text-on-surface-variant font-light mt-1">Downtown location, near the north parking entrance.</p>
            </div>
          </div>
        </div>

        <!-- Ride preferences block tags -->
        <div class="border-t border-[#353534]/50 pt-6">
          <h4 class="text-xs font-bold text-on-surface-variant mb-4 uppercase tracking-wider">Ride Preferences</h4>
          <div class="flex flex-wrap gap-2">
            <div class="px-3 py-1.5 rounded-lg bg-[#131313] border border-[#2a2a2a] flex items-center gap-2 text-on-surface text-xs font-semibold">
              <Music class="w-4 h-4 text-brand-secondary fill-brand-secondary/15" />
              <span>Music OK</span>
            </div>
            <div class="px-3 py-1.5 rounded-lg bg-[#131313] border border-[#2a2a2a] flex items-center gap-2 text-on-surface text-xs font-semibold">
              <Briefcase class="w-4 h-4 text-brand-primary" />
              <span>Light bags only</span>
            </div>
            <div class="px-3 py-1.5 rounded-lg bg-[#131313] border border-[#2a2a2a] flex items-center gap-2 text-on-surface text-xs font-semibold">
              <Slash class="w-4 h-4 text-brand-tertiary" />
              <span>No pets</span>
            </div>
          </div>
        </div>

        <!-- Payment Summary calculation breakdown details widget -->
        <div class="bg-[#131313] p-4 rounded-xl border border-[#222]/80 space-y-2 select-none">
          <div class="flex justify-between items-center">
            <span class="text-xs text-on-surface-variant font-medium">Seat Price</span>
            <span class="text-xs text-on-surface font-semibold">
              {{ selectedRide.price === 0 ? "Free" : `$${selectedRide.price.toFixed(2)}` }}
            </span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-xs text-on-surface-variant font-medium">Service Fee</span>
            <span class="text-xs text-on-surface font-semibold">
              {{ selectedRide.price === 0 ? "$0.00" : "$1.50" }}
            </span>
          </div>
          <div class="flex justify-between items-center pt-3 border-t border-[#2d2d2d] mt-2">
            <span class="text-sm font-bold text-on-surface">Total</span>
            <span class="text-base font-extrabold text-brand-primary">
              {{ selectedRide.price === 0 ? "Free" : `$${(selectedRide.price + 1.5).toFixed(2)}` }}
            </span>
          </div>
        </div>

      </div>

      <!-- Footer action seat submission button -->
      <div class="p-6 border-t border-[#2d2d2d] bg-[#201f1f] shrink-0">
        <button 
          @click="handleRequestSeat(selectedRide.id)"
          :disabled="requestStatus[selectedRide.id] === 'requested' || requestStatus[selectedRide.id] === 'approved'"
          class="w-full py-3.5 rounded-xl text-sm font-bold transition-all shadow-[0_0_20px_rgba(255,180,170,0.15)] cursor-pointer select-none border-none"
          :class="requestStatus[selectedRide.id] === 'approved'
            ? 'bg-brand-tertiary text-black hover:bg-brand-tertiary font-black shadow-brand-tertiary'
            : requestStatus[selectedRide.id] === 'requested'
              ? 'bg-[#353534] text-on-surface-variant cursor-not-allowed'
              : 'bg-[#ff5f52] hover:bg-[#ff786d] text-white'"
        >
          {{ requestStatus[selectedRide.id] === 'approved' 
            ? "Seat Confirmed!" 
            : requestStatus[selectedRide.id] === 'requested'
              ? "Sending Request..." 
              : "Request Seat"
          }}
        </button>
        <p class="text-center text-on-surface-variant/70 text-xxs mt-3 select-none">
          {{ requestStatus[selectedRide.id] === 'approved' 
            ? "Seat matches synced. Coordinates locked." 
            : "You won't be charged until the driver accepts your seat request."
          }}
        </p>
      </div>
    </aside>

  </div>
</template>
