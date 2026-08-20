<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';

const props = defineProps<{
  userSettings: {
    mainGoal: string;
    calorieTarget: number;
  };
}>();

const activeTab = 'targets';
const mainGoalItems = [
  {
    label: 'Weight Loss',
    value: 'weightloss',
    description: 'Helps lose weight',
  },
  {
    label: 'Weight gain',
    value: 'weightgain',
    description: 'Helps gain weight (bulk)',
  },
  { label: 'Muscle gain', value: 'muscle', description: 'Helps build muscles' },
  { label: 'Healthy', value: 'general', description: 'General balanced diet' },
];

const form = useForm({
  mainGoal: props.userSettings.mainGoal,
  calorieTarget: props.userSettings.calorieTarget,
});

const submitGoal = () => {
  form
    .transform((data) => ({
      ...data,
      mainGoal: [data.mainGoal],
    }))
    .put(route('settings.targets'), { preserveScroll: true });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="flex flex-1 flex-col divide-y divide-gray-200 px-4 md:px-0 dark:divide-gray-800"
      @submit.prevent="submitGoal"
    >
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2
            class="text-base font-bold text-gray-900 md:text-lg lg:text-xl dark:text-white"
          >
            Main goal
          </h2>
          <p class="mt-1 text-sm text-gray-500 md:text-base dark:text-gray-400">
            Select your primary dietary objective so we can tailor your meal
            plans.
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField :error="form.errors.mainGoal">
            <URadioGroup
              v-model="form.mainGoal"
              :items="mainGoalItems"
              variant="table"
            />
          </UFormField>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2
            class="text-base font-bold text-gray-900 md:text-lg lg:text-xl dark:text-white"
          >
            Daily Calorie Target
          </h2>
          <p class="mt-1 text-sm text-gray-500 md:text-base dark:text-gray-400">
            Set your daily caloric intake limit.
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField :error="form.errors.calorieTarget">
            <UInputNumber
              v-model="form.calorieTarget"
              class="w-full max-w-md"
            />
          </UFormField>
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
          Save Targets
        </UButton>
      </div>
    </UForm>
  </SettingsLayout>
</template>
