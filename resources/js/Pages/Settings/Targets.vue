<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';
import type { TargetsProps } from '../../Types/settingInterfaces';

const props = defineProps<TargetsProps>();

const activeTab = 'targets';

const goalItems = [
  {
    label: 'Lose Weight',
    value: 'lose_weight',
    description: 'Caloric deficit to shed fat.',
  },
  {
    label: 'Maintain Current Form',
    value: 'maintain',
    description: 'Caloric balance to sustain your current weight.',
  },
  {
    label: 'Gain Muscle',
    value: 'gain_muscle',
    description: 'Caloric surplus to support muscle growth.',
  },
];

const days = [
  { key: 'monday', label: 'Monday' },
  { key: 'tuesday', label: 'Tuesday' },
  { key: 'wednesday', label: 'Wednesday' },
  { key: 'thursday', label: 'Thursday' },
  { key: 'friday', label: 'Friday' },
  { key: 'saturday', label: 'Saturday' },
  { key: 'sunday', label: 'Sunday' },
] as const;

const activityOptions = [
  { label: 'Rest (No training)', value: 'rest' },
  { label: 'Moderate (Cardio, light weights)', value: 'moderate' },
  { label: 'Heavy (Intense workout)', value: 'heavy' },
];

const form = useForm({
  fitness_goal: props.profile.fitness_goal,
  schedule: { ...props.schedule },
});

const submitTargets = () => {
  form.put(route('settings.targets'), { preserveScroll: true });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="flex flex-1 flex-col divide-y divide-gray-200 px-4 md:px-0 dark:divide-gray-800"
      @submit.prevent="submitTargets"
    >
      <!-- Main Objective Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
            Primary Objective
          </h2>
          <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
            Your weekly caloric target will be dynamically calibrated based on
            this selection.
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField :error="form.errors.fitness_goal">
            <URadioGroup
              v-model="form.fitness_goal"
              :items="goalItems"
              variant="table"
            />
          </UFormField>
        </div>
      </div>

      <!-- Weekly Training Schedule Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
            Training Schedule
          </h2>
          <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
            Adjust the days you actively train. We use this to calculate daily
            caloric fluctuations.
          </p>
        </div>
        <div class="md:col-span-2">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div
              v-for="day in days"
              :key="day.key"
              :class="{ 'sm:col-span-2': day.key === 'sunday' }"
            >
              <UFormField :label="day.label" :name="`schedule.${day.key}`">
                <USelect
                  v-model="form.schedule[day.key]"
                  :items="activityOptions"
                  size="lg"
                  class="w-full"
                  :ui="{ content: 'z-[100]' }"
                />
              </UFormField>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-auto flex justify-end py-6">
        <UButton
          type="submit"
          color="primary"
          :loading="form.processing"
          class="px-6 py-2 text-sm md:px-8 md:py-3 md:text-base lg:text-lg"
        >
          Save Targets
        </UButton>
      </div>
    </UForm>
  </SettingsLayout>
</template>
