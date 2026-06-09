<script setup lang="ts">
import { modalState } from '../modal';

const handleConfirm = () => {
  if (modalState.value.resolve) {
    modalState.value.resolve(modalState.value.type === 'prompt' ? modalState.value.promptValue : true);
  }
  modalState.value.isOpen = false;
};

const handleCancel = () => {
  if (modalState.value.resolve) {
    modalState.value.resolve(modalState.value.type === 'prompt' ? null : false);
  }
  modalState.value.isOpen = false;
};
</script>

<template>
  <Transition name="fade">
    <div v-if="modalState.isOpen" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-[#1e1e1e] border border-[#2d2d2d] rounded-2xl p-6 shadow-2xl w-full max-w-md flex flex-col gap-4 animate-in zoom-in-95 duration-200">
        <h3 class="text-xl font-bold text-on-surface">{{ modalState.title }}</h3>
        <p v-if="modalState.message" class="text-sm text-on-surface-variant leading-relaxed whitespace-pre-wrap">{{ modalState.message }}</p>
        
        <input 
          v-if="modalState.type === 'prompt'" 
          v-model="modalState.promptValue" 
          type="text" 
          class="bg-[#131313] border border-[#333] text-on-surface rounded-xl p-3 focus:outline-none focus:border-brand-primary w-full mt-2" 
          @keyup.enter="handleConfirm"
          autofocus
        />

        <div class="flex justify-end gap-3 mt-4">
          <button @click="handleCancel" class="px-5 py-2.5 rounded-lg text-sm font-bold text-on-surface-variant hover:bg-[#2a2a2a] transition-colors cursor-pointer">
            Cancel
          </button>
          <button @click="handleConfirm" class="px-5 py-2.5 rounded-lg text-sm font-bold bg-brand-primary text-black hover:bg-brand-primary/90 transition-colors cursor-pointer shadow-lg">
            {{ modalState.type === 'prompt' ? 'Submit' : 'Confirm' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
