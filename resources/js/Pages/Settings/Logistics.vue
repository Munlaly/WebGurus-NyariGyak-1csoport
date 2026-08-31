<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';
import type { LogisticsProps } from '../../Types/settingInterfaces';

const props = defineProps<LogisticsProps>();

const activeTab = 'logistics';

const prepTimeOptions = [
  {
    label: 'Quick (Under 15 minutes)',
    value: 15,
    description: 'Fast, minimal-effort meals for busy schedules.',
  },
  {
    label: 'Standard (Under 30 minutes)',
    value: 30,
    description: 'Balanced recipes that fit easily into a daily routine.',
  },
  {
    label: 'Moderate (Under 45 minutes)',
    value: 45,
    description: 'Slightly more elaborate meals with multiple components.',
  },
  {
    label: 'No Limit (60+ minutes)',
    value: 60,
    description:
      'I enjoy cooking and do not mind spending extra time in the kitchen.',
  },
];

const form = useForm({
  household_size: props.settings.household_size,
  prep_time_preference: props.settings.prep_time_preference,
});

const submitLogistics = () => {
  form.put(route('settings.logistics'), { preserveScroll: true });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="flex flex-1 flex-col divide-y divide-gray-200 px-4 md:px-0 dark:divide-gray-800"
      @submit.prevent="submitLogistics"
    >
      <!-- Household Size Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
            Household Size
          </h2>
          <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
            How many people will you be cooking for? We use this metric to scale
            recipe portions and accurately calculate your weekly grocery list.
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField name="household_size" :error="form.errors.household_size">
            <UInput
              v-model="form.household_size"
              type="number"
              min="1"
              max="20"
              size="lg"
              placeholder="e.g., 2"
              class="w-full max-w-xs"
            />
          </UFormField>
        </div>
      </div>

      <!-- Prep Time Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
            Preparation Time
          </h2>
          <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
            Set a strict limit on how much time you are willing to spend
            preparing a single meal.
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField
            name="prep_time_preference"
            :error="form.errors.prep_time_preference"
          >
            <URadioGroup
              v-model="form.prep_time_preference"
              :items="prepTimeOptions"
              variant="card"
            />
          </UFormField>
        </div>
      </div>

      <div class="mt-auto flex justify-end py-6">
        <UButton
          type="submit"
          color="primary"
          :loading="form.processing"
          class="px-6 py-2 text-sm md:px-8 md:py-3 md:text-base lg:text-lg"
        >
          Save Logistics
        </UButton>
      </div>
    </UForm>
  </SettingsLayout>
</template>
