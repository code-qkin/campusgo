import { ref } from 'vue';

export const activeAlerts = ref<{ id: number; message: string }[]>([]);

let alertId = 0;

export const showAlert = (msg: string) => {
  const id = alertId++;
  activeAlerts.value.push({ id, message: msg });
  setTimeout(() => {
    activeAlerts.value = activeAlerts.value.filter(a => a.id !== id);
  }, 4000);
};
