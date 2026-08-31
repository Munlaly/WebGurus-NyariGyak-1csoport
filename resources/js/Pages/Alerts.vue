<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import { lo } from '@nuxt/ui/runtime/locale/index.js';

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

const isConfirmModalOpen = ref(false);
const pendingDismissId = ref<number | null>(null);
const dontAskAgain = ref(false);
const SKIP_CONFIRM_KEY = 'skip_alert_confirm';

onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY);
  if (saved) {
    try {
      dismissedIds.value = JSON.parse(saved);
    } catch (e) {
      console.error('Failed to parse dismissed alerts:', e);
    }
  }
  if (localStorage.getItem(SKIP_CONFIRM_KEY) === 'true') {
    dontAskAgain.value = true;
  }
});

const formatDate = (dateString: string) => {
  if (!dateString) return 'Unknown Date';
  return dateString.split('T')[0];
};

const handleDismissClick = (id: number) => {
  if (dontAskAgain.value) {
    dismissAlert(id);
  } else {
    pendingDismissId.value = id;
    isConfirmModalOpen.value = true;
  }
};

const confirmDismiss = () => {
  if (dontAskAgain.value) {
    localStorage.setItem(SKIP_CONFIRM_KEY, 'true');
  } else {
    localStorage.removeItem(SKIP_CONFIRM_KEY);
  }

  if (pendingDismissId.value !== null) {
    dismissAlert(pendingDismissId.value);
  }

  isConfirmModalOpen.value = false;
  pendingDismissId.value = null;
};

const cancelDismiss = () => {
  isConfirmModalOpen.value = false;
  pendingDismissId.value = null;
};

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

const resetDismissedAlerts = () => {
  dismissedIds.value = [];
  localStorage.removeItem(STORAGE_KEY);
  localStorage.removeItem(SKIP_CONFIRM_KEY);
  dontAskAgain.value = false;
};
</script>

<template>
  <AuthenticatedLayout>
    <div class="relative mx-auto flex w-full max-w-4xl flex-col gap-8">
      <header class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Inventory Alerts
          </h1>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Review your expiring ingredients below.
          </p>
        </div>

        <!-- Batch Action: Reset Hidden Alerts Button -->
        <button
          v-if="dismissedIds.length > 0 || dontAskAgain"
          class="flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 active:scale-95 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
          @click="resetDismissedAlerts"
        >
          <span class="material-symbols-outlined text-base">refresh</span>
          <span>Reset Hidden Alerts</span>
        </button>
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
            <li v-for="item in alertsData.expired" :key="item.id">
              <Link
                :href="'/inventory?highlight=' + item.id"
                class="flex w-full items-center justify-between rounded-lg bg-red-100/50 px-4 py-2 transition-colors hover:bg-red-200/70 dark:bg-red-900/20 dark:hover:bg-red-900/40"
              >
                <span class="text-red-900 dark:text-red-300">
                  {{ item.ingredient?.name || 'Unknown Item' }}
                  <!-- Formatted date here! -->
                  <span class="opacity-75"
                    >(Expired on {{ formatDate(item.expiration_date) }})</span
                  >
                </span>

                <!-- X Button pointing to our new handle logic -->
                <button
                  class="flex h-8 w-8 items-center justify-center rounded-full text-red-700 transition-colors hover:bg-red-300 dark:text-red-400 dark:hover:bg-red-800"
                  title="Dismiss notification"
                  @click.prevent="handleDismissClick(item.id)"
                >
                  <span class="material-symbols-outlined text-xl">close</span>
                </button>
              </Link>
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

        <!-- Empty State -->
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

    <!-- Confirmation Modal Overlay -->
    <div
      v-if="isConfirmModalOpen"
      class="fixed inset-0 z-100 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
    >
      <div
        class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900"
      >
        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">
          Dismiss Alert?
        </h3>
        <p class="mb-5 text-sm text-gray-600 dark:text-gray-400">
          Are you sure you want to dismiss this notification? This will hide it
          from this list, but the item will remain in your inventory.
        </p>

        <!-- Checkbox -->
        <label
          class="mb-6 flex cursor-pointer items-center gap-3 text-sm text-gray-700 dark:text-gray-300"
        >
          <input
            v-model="dontAskAgain"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-600 dark:border-gray-700 dark:bg-gray-800 dark:focus:ring-offset-gray-900"
          />
          <span>Never ask this again</span>
        </label>

        <!-- Buttons -->
        <div class="flex justify-end gap-3">
          <button
            class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
            @click="cancelDismiss"
          >
            Cancel
          </button>
          <button
            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-700"
            @click="confirmDismiss"
          >
            Dismiss
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
