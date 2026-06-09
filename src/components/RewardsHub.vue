<script setup lang="ts">
import { ref } from 'vue';
import { RewardItem, User } from '../types';
import { mockRewards } from '../data';
import { 
  Trophy, 
  Coffee, 
  Tickets, 
  Shirt, 
  Utensils, 
  CheckCircle, 
  Gift
} from 'lucide-vue-next';

import { showAlert as alert } from '../alert';
import { customConfirm } from '../modal';

const props = defineProps<{
  user: User
}>();

const emit = defineEmits(['update-user-points']);

const rewards = ref<RewardItem[]>(mockRewards);
const activeVouchers = ref<Array<{ id: string; reward: RewardItem; claimDate: string; code: string }>>([
  {
    id: "v-1",
    reward: mockRewards[0],
    claimDate: "Oct 22, 2024",
    code: "CG-COFFEE-8821"
  }
]);

const handleClaimReward = async (reward: RewardItem) => {
  if (props.user.points < reward.ptsRequired) {
    alert(`Insufficient Points Balance:
This voucher requires ${reward.ptsRequired} points. You have ${props.user.points} points.
Earn points by logging carpools, walking home, or opting for hybrid paths!`);
    return;
  }

  const confirmClaim = await customConfirm('Confirm Redemption', `Confirm point redeem:
Redeem "${reward.name}" for ${reward.ptsRequired} points?`);
  if (!confirmClaim) return;

  // Deduct points
  emit('update-user-points', props.user.points - reward.ptsRequired);

  // Create claimed voucher log
  const codeSegment = Math.floor(1000 + Math.random() * 9000);
  const newVoucher = {
    id: `v-${Date.now()}`,
    reward,
    claimDate: "Today",
    code: `CG-${reward.name.split(" ")[0].toUpperCase()}-${codeSegment}`
  };

  activeVouchers.value.unshift(newVoucher);
  alert(`Success! voucher "${reward.name}" claimed. Show the voucher barcode/code "${newVoucher.code}" to any campus cafeteria checkout counter to redeem.`);
};
</script>

<template>
  <div class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] max-md:overflow-y-auto overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">
    
    <!-- MAIN CATALOG AREA -->
    <section class="flex-1 max-md:p-4 p-10 max-md:overflow-visible overflow-y-auto">
      <div class="flex justify-between items-end mb-8 select-none">
        <div>
          <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Eco-Rewards</h2>
          <p class="text-sm text-on-surface-variant font-light">Swap points earned during sustainable transit activities for premium student goodies.</p>
        </div>

        <div class="bg-[#2a2a2a]/45 border border-[#2d2d2d] px-5 py-3 rounded-xl flex items-center gap-3">
          <Trophy class="w-5 h-5 text-brand-primary animate-bounce duration-1000" />
          <div>
            <div class="text-xxs uppercase tracking-wider text-on-surface-variant/75 font-semibold">My Balance</div>
            <div class="text-base font-extrabold text-[#fff] tracking-wide">{{ user.points }} pts</div>
          </div>
        </div>
      </div>

      <!-- Catalog collection cards grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-12">
        <div 
          v-for="reward in rewards" 
          :key="reward.id"
          class="bg-[#1e1e1e]/60 border rounded-xl p-6 transition-all relative flex flex-col justify-between"
          :class="user.points >= reward.ptsRequired 
            ? 'border-[#2d2d2d] hover:border-[#353534]' 
            : 'border-[#2d2d2d]/45 opacity-80'"
        >
          <div>
            <div class="flex justify-between items-start mb-4 select-none">
              <div class="w-12 h-12 rounded bg-[#131313] border border-[#2d2d2d] flex items-center justify-center">
                <Coffee v-if="reward.icon === 'Coffee'" class="w-6 h-6 text-brand-primary" />
                <Tickets v-else-if="reward.icon === 'Tickets'" class="w-6 h-6 text-brand-secondary" />
                <Shirt v-else-if="reward.icon === 'Shirt'" class="w-6 h-6 text-brand-tertiary" />
                <Utensils v-else-if="reward.icon === 'Utensils'" class="w-6 h-6 text-[#ffb4ab]" />
                <Gift v-else class="w-6 h-6 text-brand-primary" />
              </div>

              <div class="text-right">
                <div class="text-sm font-bold text-[#ffb4aa]">{{ reward.ptsRequired }} pts</div>
                <span class="text-xxs px-2 py-0.5 bg-[#353534] rounded text-on-surface-variant font-semibold mt-1 inline-block uppercase tracking-wider">{{ reward.category }}</span>
              </div>
            </div>

            <h3 class="text-lg font-bold text-[#fff]">{{ reward.name }}</h3>
            <p class="text-xs text-on-surface-variant font-light mt-2 leading-relaxed">
              {{ reward.description }}
            </p>
          </div>

          <div class="mt-6">
            <button 
              @click="handleClaimReward(reward)"
              class="w-full py-2.5 rounded-lg font-bold text-xs transition-transform active:scale-[0.98] cursor-pointer outline-none border-none"
              :class="user.points >= reward.ptsRequired 
                ? 'bg-[#ff5f52] hover:bg-[#ff786d] text-white' 
                : 'bg-[#2a2a2a] text-on-surface-variant cursor-not-allowed'"
            >
              {{ user.points >= reward.ptsRequired ? "Redeem Now" : `Earn ${reward.ptsRequired - user.points} more points` }}
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- RIGHT PANEL: User Points & Cart Wallet -->
    <aside class="max-md:w-full w-80 bg-[#1c1b1b] border-l border-[#2d2d2d] flex flex-col max-md:h-auto h-full shrink-0 select-none">
      <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-4 p-6 pb-0">Claimed Vouchers</h3>
      
      <div class="flex-1 max-md:overflow-visible overflow-y-auto p-6 space-y-4">
        <div v-for="voucher in activeVouchers" :key="voucher.id" class="p-4 rounded-xl bg-[#131313] border border-[#2d2d2d] space-y-3 relative overflow-hidden">
          <div class="absolute top-0 right-0 w-16 h-16 bg-brand-primary/5 rounded-bl-full pointer-events-none"></div>
          
          <div class="flex justify-between items-start">
            <span class="text-xxs text-on-surface-variant font-semibold">{{ voucher.claimDate }}</span>
            <span class="flex items-center gap-1 text-[10px] text-brand-tertiary font-bold">
              <CheckCircle class="w-3.5 h-3.5" />
              Active
            </span>
          </div>

          <div>
            <h4 class="text-xs font-bold text-on-surface">{{ voucher.reward.name }}</h4>
            <p class="text-[10px] font-mono font-bold text-brand-secondary bg-[#1e1e1e] border border-[#2d2d2d] px-2.5 py-1.5 rounded mt-2.5 text-center tracking-widest select-all">
              {{ voucher.code }}
            </p>
            <span class="text-[9px] text-on-surface-variant/65 text-center block mt-2 font-light">Present at campus cafeteria checkout.</span>
          </div>
        </div>
      </div>
    </aside>

  </div>
</template>
