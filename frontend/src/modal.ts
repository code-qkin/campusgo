import { ref } from 'vue';

export type ModalState = {
  isOpen: boolean;
  type: 'confirm' | 'prompt';
  title: string;
  message: string;
  promptValue: string;
  resolve: ((value: any) => void) | null;
};

export const modalState = ref<ModalState>({
  isOpen: false,
  type: 'confirm',
  title: '',
  message: '',
  promptValue: '',
  resolve: null
});

export const customConfirm = (title: string, message: string): Promise<boolean> => {
  return new Promise((resolve) => {
    modalState.value = {
      isOpen: true,
      type: 'confirm',
      title,
      message,
      promptValue: '',
      resolve
    };
  });
};

export const customPrompt = (title: string, message: string): Promise<string | null> => {
  return new Promise((resolve) => {
    modalState.value = {
      isOpen: true,
      type: 'prompt',
      title,
      message,
      promptValue: '',
      resolve
    };
  });
};
