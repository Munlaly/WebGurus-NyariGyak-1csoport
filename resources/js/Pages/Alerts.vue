<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';

interface Ingredient {
  id: number;
  name: string;
}

interface InventoryItem {
  id: number;
  expiration_date: string;
  ingredient?: Ingredient;
}

interface AlertsData {
  expired: InventoryItem[];
  ciritcal: InventoryItem[];
  urgent: InventoryItem[];
}

const page = usePage();
const alertsData = computed<AlertsData>(() => {
  const data = page.props.expiringAlerts as AlertsData;
  return (
    data || {
      expired: [],
      critical: [],
      urgent: [],
    }
  );
});
</script>

<template>
  <AuthenticatedLayout>
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-8">
      <header>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Inventory Alerts
        </h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
          Review your expiring ingredients below.
        </p>
      </header>

      <div class="flex flex-col gap-6">
        <!-- Expired Section -->
        <div
          v-if="alertsData.expired.length"
          class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900/50 dark:bg-red-900/10"
        >
          <h2
            class="mb-4 flex items-center gap-2 text-lg font-bold text-red-800 dark:text-red-400"
          >
            <span class="material-symbols-outlined">error</span> Expired
          </h2>
          <ul
            class="list-inside list-disc space-y-2 text-red-900 dark:text-red-300"
          >
            <li v-for="item in alertsData.expired" :key="item.id">
              {{ item.ingredient?.name || 'Unknown Item' }}
              <span class="opacity-75"
                >(Expired on {{ item.expiration_date }})</span
              >
            </li>
          </ul>
        </div>

        <!-- Critical Section -->
        <div
          v-if="alertsData.critical.length"
          class="rounded-xl border border-orange-200 bg-orange-50 p-6 dark:border-orange-900/50 dark:bg-orange-900/10"
        >
          <h2
            class="mb-4 flex items-center gap-2 text-lg font-bold text-orange-800 dark:text-orange-400"
          >
            <span class="material-symbols-outlined">warning</span> Critical (1-2
            days)
          </h2>
          <ul
            class="list-inside list-disc space-y-2 text-orange-900 dark:text-orange-300"
          >
            <li v-for="item in alertsData.critical" :key="item.id">
              {{ item.ingredient?.name || 'Unknown Item' }}
            </li>
          </ul>
        </div>

        <!-- Urgent Section -->
        <div
          v-if="alertsData.urgent.length"
          class="rounded-xl border border-yellow-200 bg-yellow-50 p-6 dark:border-yellow-900/50 dark:bg-yellow-900/10"
        >
          <h2
            class="mb-4 flex items-center gap-2 text-lg font-bold text-yellow-800 dark:text-yellow-400"
          >
            <span class="material-symbols-outlined">schedule</span> Urgent (&lt;
            7 days)
          </h2>
          <ul
            class="list-inside list-disc space-y-2 text-yellow-900 dark:text-yellow-300"
          >
            <li v-for="item in alertsData.urgent" :key="item.id">
              {{ item.ingredient?.name || 'Unknown Item' }}
            </li>
          </ul>
        </div>

        <div
          v-if="
            !alertsData.expired.length &&
            !alertsData.critical.length &&
            !alertsData.urgent.length
          "
          class="flex h-32 items-center justify-center rounded-xl border border-dashed border-gray-300 text-gray-500 dark:border-gray-700 dark:text-gray-400"
        >
          No active alerts right now! Your inventory is looking great.
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
