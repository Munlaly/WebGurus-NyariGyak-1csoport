<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
  userSettings: {
    theme: 'light' | 'dark';
    pushNotifications: boolean;
    inAppAlerts: boolean;
    emailDigests: boolean;
  };
}>();

const activeTab = 'system';

const form = useForm({
  theme: props.userSettings?.theme || 'light',
  pushNotifications: props.userSettings?.pushNotifications ?? true,
  inAppAlerts: props.userSettings?.inAppAlerts ?? true,
  emailDigests: props.userSettings?.emailDigests ?? false,
});

const themeButtonClasses = computed(() => {
  const base =
    'flex flex-1 items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-bold transition-all';
  const active =
    'text-primary dark:text-primary-400 bg-white shadow-sm dark:bg-gray-700';
  const inactive = 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700';
  return {
    light: [base, form.theme === 'light' ? active : inactive],
    dark: [base, form.theme === 'dark' ? active : inactive],
  };
});

const submitSystemSettings = () => {
  form.put(route('settings.system'), { preserveScroll: true });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="flex flex-1 flex-col divide-y divide-gray-200 px-4 md:px-0 dark:divide-gray-800"
      @submit.prevent="submitSystemSettings"
    >
      <!-- Theme Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
            Appearance
          </h2>
          <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
            Customize the planner's visual theme.
          </p>
        </div>
        <div class="md:col-span-2">
          <div
            class="flex max-w-md gap-1 rounded-lg bg-gray-100 p-1 dark:bg-gray-800"
          >
            <button
              type="button"
              :class="themeButtonClasses.light"
              @click="form.theme = 'light'"
            >
              <UIcon name="i-heroicons-sun" /> Light Mode
            </button>
            <button
              type="button"
              :class="themeButtonClasses.dark"
              @click="form.theme = 'dark'"
            >
              <UIcon name="i-heroicons-moon" /> Dark Mode
            </button>
          </div>
        </div>
      </div>

      <!-- Notifications Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
            Notifications
          </h2>
          <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
            Manage how you receive alerts and digests.
          </p>
        </div>
        <div class="flex max-w-md flex-col gap-6 md:col-span-2">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-semibold text-gray-900 dark:text-white">
                Browser Push Notifications
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Real-time alerts for expiring ingredients.
              </p>
            </div>
            <USwitch v-model="form.pushNotifications" />
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="font-semibold text-gray-900 dark:text-white">
                In-App Alerts
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Updates shown within the dashboard.
              </p>
            </div>
            <USwitch v-model="form.inAppAlerts" />
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="font-semibold text-gray-900 dark:text-white">
                Weekly Email Digests
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Summary of your zero-waste impact.
              </p>
            </div>
            <USwitch v-model="form.emailDigests" />
          </div>
        </div>
      </div>

      <!-- Pinned to bottom with mt-auto -->
      <div class="mt-auto flex justify-end py-6">
        <UButton
          type="submit"
          color="primary"
          :loading="form.processing"
          class="px-6 py-2 text-sm md:px-8 md:py-3 md:text-base lg:text-lg"
        >
          Save System Settings
        </UButton>
      </div>
    </UForm>
  </SettingsLayout>
</template>
