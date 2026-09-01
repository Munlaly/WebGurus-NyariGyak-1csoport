import { ref } from 'vue';

export function useDismissedAlerts() {
  const dismissedIds = ref<number[]>([]);

  function loadDismissedIds() {
    if (typeof window !== 'undefined') {
      const saved = localStorage.getItem('dismissed_alerts_history');
      if (saved) {
        try {
          dismissedIds.value = JSON.parse(saved);
        } catch (e) {
          console.error('Failed to parse dismissed alerts', e);
          dismissedIds.value = [];
        }
      }
    }
  }

  function dismissAlert(id: number) {
    if (!dismissedIds.value.includes(id)) {
      dismissedIds.value.push(id);
      if (typeof window !== 'undefined') {
        localStorage.setItem(
          'dismissed_alerts_history',
          JSON.stringify(dismissedIds.value),
        );
      }
    }
  }

  loadDismissedIds();

  return {
    dismissedIds,
    dismissAlert,
    loadDismissedIds,
  };
}
