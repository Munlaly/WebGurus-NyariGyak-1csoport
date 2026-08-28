<script setup lang="ts">
import { computed, ref } from 'vue';

// 1. Interface for what your backend will eventually send
interface Ingredient {
  id: number;
  name: string;
  daysUntilExpiry: number;
}

const props = defineProps<{
  // We check this setting based on the INT-31 system preferences you built
  inAppAlertsEnabled: boolean;
  expiringIngredients?: Ingredient[];
}>();

// 2. Mock data to test the UI until the backend is ready
const ingredients = ref<Ingredient[]>(
  props.expiringIngredients || [
    { id: 1, name: 'Milk', daysUntilExpiry: -1 },
    { id: 2, name: 'Chicken Breast', daysUntilExpiry: 1 },
    { id: 3, name: 'Spinach', daysUntilExpiry: 5 },
  ],
);

// 3. The logic you requested
const categorizedAlerts = computed(() => {
  if (!props.inAppAlertsEnabled) return [];

  return (
    ingredients.value
      .map((item) => {
        let status = '';
        let colorClasses = '';
        let icon = '';

        if (item.daysUntilExpiry <= 0) {
          status = 'EXPIRED';
          // Red for expired
          colorClasses =
            'bg-red-100 text-red-800 border-red-500 dark:bg-red-900/30 dark:text-red-400';
          icon = 'error';
        } else if (item.daysUntilExpiry <= 2) {
          status = 'CRITICAL';
          // Orange for 1-2 days
          colorClasses =
            'bg-orange-100 text-orange-800 border-orange-500 dark:bg-orange-900/30 dark:text-orange-400';
          icon = 'warning';
        } else if (item.daysUntilExpiry < 7) {
          status = 'URGENT';
          // Yellow for < 1 week
          colorClasses =
            'bg-yellow-100 text-yellow-800 border-yellow-500 dark:bg-yellow-900/30 dark:text-yellow-400';
          icon = 'schedule';
        }

        return { ...item, status, colorClasses, icon };
      })
      // Filter out anything that doesn't need an alert (7+ days)
      .filter((item) => item.status !== '')
      // Sort so EXPIRED shows at the top, then CRITICAL, then URGENT
      .sort((a, b) => a.daysUntilExpiry - b.daysUntilExpiry)
  );
});

// Optional: Let users dismiss the notification so it doesn't block the screen
const dismissAlert = (id: number) => {
  ingredients.value = ingredients.value.filter((item) => item.id !== id);
};
</script>

<template>
  <!-- Fixed container top center. top-20 ensures it clears your 64px header -->
  <div
    class="pointer-events-none fixed top-20 left-1/2 z-50 flex w-full max-w-md -translate-x-1/2 flex-col gap-3 px-4"
  >
    <transition-group name="alert-fade">
      <div
        v-for="alert in categorizedAlerts"
        :key="alert.id"
        class="pointer-events-auto flex items-center justify-between rounded-lg border-l-4 p-4 shadow-lg backdrop-blur-md"
        :class="alert.colorClasses"
      >
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined">{{ alert.icon }}</span>
          <div>
            <span class="font-bold">[{{ alert.status }}]</span>
            {{ alert.name }}
            <span v-if="alert.daysUntilExpiry > 0" class="text-sm opacity-80">
              (in {{ alert.daysUntilExpiry }} day{{
                alert.daysUntilExpiry > 1 ? 's' : ''
              }})
            </span>
          </div>
        </div>
        <button class="hover:opacity-70" @click="dismissAlert(alert.id)">
          <span class="material-symbols-outlined text-lg">close</span>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.alert-fade-enter-active,
.alert-fade-leave-active {
  transition: all 0.3s ease;
}
.alert-fade-enter-from,
.alert-fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
