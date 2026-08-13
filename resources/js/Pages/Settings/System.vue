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
  form.post('/settings/system', {
    preserveScroll: true,
  });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="mx-auto max-w-2xl space-y-6"
      @submit.prevent="submitSystemSettings"
    >
      <!-- 1. Theme Selection -->
      <UCard>
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h2
              class="font-headline-md text-[24px] font-bold text-gray-900 dark:text-white"
            >
              Appearance
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Customize the planner's visual theme.
            </p>
          </div>
          <UIcon name="i-heroicons-swatch" class="text-2xl text-gray-400" />
        </div>

        <!--Segmented Control for Theme -->
        <div class="flex gap-1 rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
          <button
            type="button"
            :class="themeButtonClasses.light"
            @click="form.theme = 'light'"
          >
            <UIcon name="i-heroicons-sun" />
            Light Mode
          </button>
          <button
            type="button"
            :class="themeButtonClasses.dark"
            @click="form.theme = 'dark'"
          >
            <UIcon name="i-heroicons-moon" />
            Dark Mode
          </button>
        </div>
      </UCard>

      <!-- 2. Notifications -->
      <UCard>
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h2
              class="font-headline-md text-[24px] font-bold text-gray-900 dark:text-white"
            >
              Notifications
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Manage how you receive alerts and digests.
            </p>
          </div>
          <UIcon name="i-heroicons-bell" class="text-2xl text-gray-400" />
        </div>

        <div
          class="flex flex-col gap-4 divide-y divide-gray-200 dark:divide-gray-800"
        >
          <div class="flex items-center justify-between py-3">
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

          <div class="flex items-center justify-between py-3">
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

          <div class="flex items-center justify-between py-3">
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
      </UCard>

      <!-- Action Area for Settings -->
      <div class="flex justify-end pt-2">
        <UButton type="submit" color="primary" :loading="form.processing">
          Save System Settings
        </UButton>
      </div>
    </UForm>
  </SettingsLayout>
</template>
