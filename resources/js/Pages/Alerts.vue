<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
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
  critical: InventoryItem[];
  urgent: InventoryItem[];
}

const page = usePage();
const dismissedIds = ref<number[]>([]);
const STORAGE_KEY = 'dismissed_alerts_history';

onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY);
  if (saved) {
    try {
      dismissedIds.value = JSON.parse(saved);
    } catch (e) {
      console.error('Failed to parse dismissed alerts:', e);
    }
  }
});

const dismissAlert = (id: number) => {
  if (!dismissedIds.value.includes(id)) {
    dismissedIds.value.push(id);
    localStorage.setItem(STORAGE_KEY, JSON.stringify(dismissedIds.value));
  }
};

const alertsData = computed<AlertsData>(() => {
  const data = page.props.expiringAlerts as AlertsData;
  const rawData = data || {
    expired: [],
    critical: [],
    urgent: [],
  };

  return {
    expired: rawData.expired.filter(
      (item) => !dismissedIds.value.includes(item.id),
    ),
    critical: rawData.critical,
    urgent: rawData.urgent,
  };
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
          <ul class="flex flex-col gap-3">
            <li
              v-for="item in alertsData.expired"
              :key="item.id"
              class="flex items-center justify-between rounded-lg bg-red-100/50 px-4 py-2 dark:bg-red-900/20"
            >
              <span class="text-red-900 dark:text-red-300">
                {{ item.ingredient?.name || 'Unknown Item' }}
                <span class="opacity-75"
                  >(Expired on {{ item.expiration_date }})</span
                >
              </span>

              <!-- The X Button -->
              <button
                class="flex h-8 w-8 items-center justify-center rounded-full text-red-700 transition-colors hover:bg-red-200 dark:text-red-400 dark:hover:bg-red-800"
                title="Dismiss notification"
                @click="dismissAlert(item.id)"
              >
                <span class="material-symbols-outlined text-xl">close</span>
              </button>
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
          <ul class="flex flex-col gap-2">
            <li v-for="item in alertsData.critical" :key="item.id">
              <Link
                :href="'/inventory?highlight=' + item.id"
                class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-orange-900 transition-colors hover:bg-orange-100 dark:text-orange-300 dark:hover:bg-orange-900/30"
              >
                <span>{{ item.ingredient?.name || 'Unknown Item' }}</span>
                <span class="material-symbols-outlined opacity-50"
                  >arrow_forward_ios</span
                >
              </Link>
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
          <ul class="flex flex-col gap-2">
            <li v-for="item in alertsData.urgent" :key="item.id">
              <Link
                :href="'/inventory?highlight=' + item.id"
                class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-yellow-900 transition-colors hover:bg-yellow-100 dark:text-yellow-300 dark:hover:bg-yellow-900/30"
              >
                <span>{{ item.ingredient?.name || 'Unknown Item' }}</span>
                <span class="material-symbols-outlined opacity-50"
                  >arrow_forward_ios</span
                >
              </Link>
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
